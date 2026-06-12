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

            // 4. Hitung komisi berdasarkan level expert saat ini
            $expert         = ExpertProfile::find($booking->expert_profile_id);
            $commissionRate = $this->getCommissionRate($expert->commission_level);
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

            // 9. Update status slot → booked (permanen, tidak bisa diambil lagi)
            $booking->availability->update([
                'status'    => 'booked',
                'locked_at' => null, // bersihkan locked_at
            ]);

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
    // HELPER: ambil persentase komisi berdasarkan level
    // ----------------------------------------------------------------
    public function getCommissionRate(string $level): int
    {
        return match($level) {
            'master' => 10,
            'pro'    => 15,
            default  => 20, // newbie
        };
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
}