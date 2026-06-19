<?php

namespace App\Services;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\ExpertProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentService
{
    // ----------------------------------------------------------------
    // PROSES PEMBAYARAN
    // Dipanggil saat client klik "Bayar" di halaman payment
    // ----------------------------------------------------------------
    public function processPayment(Booking $booking): Payment
    {
        return DB::transaction(function () use ($booking) {

            // 1. Cek booking masih valid (belum expired)
            if ($booking->status !== 'pending_payment') {
                throw new \Exception('Booking ini sudah tidak bisa dibayar.');
            }

            if (now()->isAfter($booking->payment_deadline)) {
                throw new \Exception('Waktu pembayaran sudah habis. Silakan booking ulang.');
            }

            // 2. Ambil wallet client
            $clientWallet = Wallet::where('user_id', $booking->client_id)->lockForUpdate()->first();

            if (! $clientWallet) {
                throw new \Exception('Dompet digital tidak ditemukan.');
            }

            // 3. Cek saldo mencukupi
            if ($clientWallet->balance < $booking->total_price) {
                throw new \Exception(
                    'Saldo tidak mencukupi. Saldo kamu: Rp ' .
                    number_format($clientWallet->balance, 0, ',', '.') .
                    ', dibutuhkan: Rp ' .
                    number_format($booking->total_price, 0, ',', '.')
                );
            }

            // 4. Hitung komisi berdasarkan badge expert saat ini (sesuai agent.md)
            $expert         = ExpertProfile::find($booking->expert_profile_id);
            $commissionRate = $this->getCommissionRate($expert);
            $commission     = $booking->total_price * ($commissionRate / 100);
            $expertEarnings = $booking->total_price - $commission;

            // 5. Potong saldo client (debit)
            $balanceBefore = $clientWallet->balance;
            $clientWallet->decrement('balance', $booking->total_price);
            $clientWallet->increment('total_withdrawn', $booking->total_price);

            // 6. Catat log transaksi wallet client
            WalletTransaction::create([
                'wallet_id'      => $clientWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'debit',
                'amount'         => $booking->total_price,
                'balance_before' => $balanceBefore,
                'balance_after'  => $clientWallet->fresh()->balance,
                'description'    => 'Pembayaran booking #' . $booking->id,
            ]);

            // 7. Buat record payment (dana masuk escrow — belum ke expert)
            $payment = Payment::create([
                'booking_id'          => $booking->id,
                'invoice'             => $this->generateInvoice(),
                'amount'              => $booking->total_price,
                'platform_commission' => $commission,
                'expert_earnings'     => $expertEarnings,
                'commission_rate'     => $commissionRate,
                'method'              => 'wallet',
                'status'              => 'paid',
                'paid_at'             => now(),
            ]);

            // 8. Update status booking → confirmed
            $booking->update(['status' => 'confirmed']);

            // 9. Update status slot → booked (hanya untuk booking terjadwal, bukan instant)
            if ($booking->availability) {
                $booking->availability->update([
                    'status'    => 'booked',
                    'locked_at' => null,
                ]);
            }

            return $payment;
        });
    }

    // ----------------------------------------------------------------
    // CAIRKAN DANA KE EXPERT (Settlement)
    // Dipanggil saat: client klik "Selesai" ATAU auto-approve 24 jam
    // ----------------------------------------------------------------
    public function settlePayment(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {

            $payment = $booking->payment;

            if (! $payment || $payment->status !== 'paid') {
                throw new \Exception('Tidak ada pembayaran yang bisa dicairkan.');
            }

            // 1. Ambil wallet expert
            $expertUser   = $booking->expertProfile->user;
            $expertWallet = Wallet::where('user_id', $expertUser->id)->lockForUpdate()->first();

            // 2. Tambah saldo expert (credit)
            $balanceBefore = $expertWallet->balance;
            $expertWallet->increment('balance', $payment->expert_earnings);
            $expertWallet->increment('total_earned', $payment->expert_earnings);

            // 3. Catat log transaksi wallet expert
            WalletTransaction::create([
                'wallet_id'      => $expertWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->expert_earnings,
                'balance_before' => $balanceBefore,
                'balance_after'  => $expertWallet->fresh()->balance,
                'description'    => 'Pendapatan sesi #' . $booking->id .
                                    ' (komisi ' . $payment->commission_rate . '%)',
            ]);

            // 4. Update payment → settled
            $payment->update([
                'status'      => 'paid',
                'settled_at'  => now(),
            ]);

            // 5. Update booking → completed
            $booking->update(['status' => 'completed']);

            // 6. Tambah total_sessions expert (untuk leveling komisi)
            $expert = $booking->expertProfile;
            $expert->increment('total_sessions');

            // 7. Update commission_level jika sudah naik level
            $this->updateCommissionLevel($expert->fresh());
        });
    }

    // ----------------------------------------------------------------
    // REFUND KE CLIENT (saat dispute dimenangkan client)
    // ----------------------------------------------------------------
    public function refundToClient(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {

            $payment = $booking->payment;

            // 1. Kembalikan dana ke wallet client
            $clientWallet  = Wallet::where('user_id', $booking->client_id)->lockForUpdate()->first();
            $balanceBefore = $clientWallet->balance;

            $clientWallet->increment('balance', $payment->amount);
            $clientWallet->decrement('total_withdrawn', $payment->amount);

            // 2. Catat log refund
            WalletTransaction::create([
                'wallet_id'      => $clientWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $clientWallet->fresh()->balance,
                'description'    => 'Refund booking #' . $booking->id,
            ]);

            // 3. Update payment & booking
            $payment->update(['status' => 'refunded']);
            $booking->update(['status' => 'cancelled']);
        });
    }

    // ----------------------------------------------------------------
    // AUTO-APPROVE: jalankan settlement otomatis setelah 24 jam
    // Dipanggil oleh cron job setiap jam
    // ----------------------------------------------------------------
    public function autoApproveSettlements(): int
    {
        $count = 0;

        $pendingBookings = Booking::where('status', 'pending_settlement')
            ->where('session_ended_at', '<=', now()->subHours(24))
            ->whereDoesntHave('dispute') // tidak dalam sengketa
            ->get();

        foreach ($pendingBookings as $booking) {
            try {
                $this->settlePayment($booking);
                $count++;
            } catch (\Exception $e) {
                // log error tapi lanjut ke booking berikutnya
                Log::error('Auto-approve gagal untuk booking ' . $booking->id . ': ' . $e->getMessage());
            }
        }

        return $count;
    }

    // ----------------------------------------------------------------
    // HELPER: ambil persentase komisi berdasarkan badge expert (sesuai agent.md)
    // ----------------------------------------------------------------
    public function getCommissionRate(ExpertProfile $expert): int
    {
        if ($expert->badge === 'Top Rated') {
            return 8; // Top Rated: 8% platform fee (2% discount)
        }
        return 10; // Default platform fee: 10%
    }

    // ----------------------------------------------------------------
    // HELPER: update level komisi expert jika sudah memenuhi syarat
    // ----------------------------------------------------------------
    private function updateCommissionLevel(ExpertProfile $expert): void
    {
        $newLevel = match(true) {
            $expert->total_sessions >= 50 => 'master',
            $expert->total_sessions >= 10 => 'pro',
            default                       => 'newbie',
        };

        if ($expert->commission_level !== $newLevel) {
            $expert->update(['commission_level' => $newLevel]);
        }
    }

    // ----------------------------------------------------------------
    // HELPER: generate nomor invoice unik
    // Format: INV-20240101-XXXX
    // ----------------------------------------------------------------
    private function generateInvoice(): string
    {
        do {
            $invoice = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Payment::where('invoice', $invoice)->exists());

        return $invoice;
    }
    // ════════════════════════════════════════════════════════════
// TAMBAHKAN METHOD-METHOD INI KE app/Services/PaymentService.php
// (di dalam class PaymentService, sebelum closing brace terakhir)
// ════════════════════════════════════════════════════════════

    // ----------------------------------------------------------------
    // SETTLE: CLIENT TIDAK HADIR (no-show)
    // Dana hangus, expert tetap dibayar penuh (sesuai aturan no-show biasa)
    // ----------------------------------------------------------------
    public function settleClientNoShow(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {

            $payment = $booking->payment;

            if (! $payment || $payment->status !== 'paid') {
                throw new \Exception('Tidak ada pembayaran yang bisa diproses.');
            }

            // expert tetap dapat dana (sama seperti aturan no-show booking biasa)
            $expertUser   = $booking->expertProfile->user;
            $expertWallet = Wallet::where('user_id', $expertUser->id)->lockForUpdate()->first();

            $balanceBefore = $expertWallet->balance;
            $expertWallet->increment('balance', $payment->expert_earnings);
            $expertWallet->increment('total_earned', $payment->expert_earnings);

            WalletTransaction::create([
                'wallet_id'      => $expertWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->expert_earnings,
                'balance_before' => $balanceBefore,
                'balance_after'  => $expertWallet->fresh()->balance,
                'description'    => 'Kompensasi client tidak hadir — booking #' . $booking->id,
            ]);

            $payment->update(['status' => 'paid', 'settled_at' => now()]);

            $booking->update([
                'status'        => 'cancelled',
                'cancel_reason' => 'client_no_show',
            ]);

            // expert tetap dapat sesi terhitung & naik level seperti biasa
            $expert = $booking->expertProfile;
            $expert->increment('total_sessions');
            $this->updateCommissionLevel($expert->fresh());
        });
    }

    // ----------------------------------------------------------------
    // SETTLE: EXPERT TIDAK HADIR (no-show)
    // Dana refund penuh ke client, expert dapat penalti (penalty_count)
    // ----------------------------------------------------------------
    public function settleExpertNoShow(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {

            $payment = $booking->payment;

            if (! $payment || $payment->status !== 'paid') {
                throw new \Exception('Tidak ada pembayaran yang bisa diproses.');
            }

            // refund PENUH ke client (bukan cuma sebagian)
            $clientWallet  = Wallet::where('user_id', $booking->client_id)->lockForUpdate()->first();
            $balanceBefore = $clientWallet->balance;

            $clientWallet->increment('balance', $payment->amount);
            $clientWallet->decrement('total_withdrawn', $payment->amount);

            WalletTransaction::create([
                'wallet_id'      => $clientWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $clientWallet->fresh()->balance,
                'description'    => 'Refund — expert tidak hadir, booking #' . $booking->id,
            ]);

            $payment->update(['status' => 'refunded']);

            $booking->update([
                'status'        => 'cancelled',
                'cancel_reason' => 'expert_no_show',
            ]);

            // ── PENALTI EXPERT ──
            $expert = $booking->expertProfile;
            $expert->increment('penalty_count');

            // auto-suspend jika penalty_count mencapai 3x (sama seperti aturan cancel biasa)
            $expert = $expert->fresh();
            if ($expert->penalty_count >= 3) {
                $expert->user->update(['status' => 'suspended']);
            }
        });
    }

    // ----------------------------------------------------------------
    // CEK NO-SHOW: dipanggil oleh cron job setiap menit
    // Mengecek semua instant consultation yang attendance_deadline-nya lewat
    // ----------------------------------------------------------------
    public function processNoShows(): array
    {
        $result = ['client_no_show' => 0, 'expert_no_show' => 0];

        $expiredBookings = Booking::where('booking_type', 'instant')
            ->where('status', 'ongoing')
            ->where('attendance_deadline', '<=', now())
            ->get();

        foreach ($expiredBookings as $booking) {
            try {
                $clientIn  = $booking->client_joined;
                $expertIn  = $booking->expert_joined;

                if (! $clientIn && $expertIn) {
                    // hanya expert yang hadir → client no-show
                    $this->settleClientNoShow($booking);
                    $result['client_no_show']++;

                } elseif ($clientIn && ! $expertIn) {
                    // hanya client yang hadir → expert no-show
                    $this->settleExpertNoShow($booking);
                    $result['expert_no_show']++;

                } elseif (! $clientIn && ! $expertIn) {
                    // tidak ada yang hadir — treat sebagai expert no-show
                    // (expert yang punya tanggung jawab utama menjaga ketersediaan)
                    $this->settleExpertNoShow($booking);
                    $result['expert_no_show']++;
                }
                // kalau keduanya hadir, harusnya sudah masuk status lain
                // (di luar scope no-show check)

            } catch (\Exception $e) {
                \Log::error('Gagal proses no-show booking ' . $booking->id . ': ' . $e->getMessage());
            }
        }

        return $result;
    }
}