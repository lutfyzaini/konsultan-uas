<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Consultation;
use App\Models\ExpertProfile;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstantConsultationService
{
    // ================================================================
    // KONSTANTA ATURAN BISNIS
    // ================================================================

    /** Batas waktu kehadiran dalam menit (aturan bisnis: 10 menit) */
    public const PRESENCE_WINDOW_MINUTES = 10;

    // ================================================================
    // METHOD UTAMA: dipanggil oleh Artisan Command setiap menit
    // Mengambil semua sesi instant yang aktif & sudah melewati deadline
    // ================================================================

    /**
     * Cek seluruh sesi instant yang sudah melewati batas waktu 10 menit.
     * Mengembalikan array ringkasan hasil proses untuk logging di command.
     *
     * @return array{ settled: int, refunded: int, errors: int }
     */
    public function checkAndResolveExpiredSessions(): array
    {
        $result = ['settled' => 0, 'refunded' => 0, 'errors' => 0];

        // ── Query: cari semua sesi instant yang:
        //    - Tipe instant (bukan scheduled)
        //    - Status masih 'active' (belum selesai/dibatalkan)
        //    - Sudah melewati deadline kehadiran
        //    - Belum pernah diproses sebelumnya (absence_resolved_at NULL)
        $expiredSessions = Consultation::where('consultation_type', 'instant')
            ->where('status', 'active')
            ->whereNotNull('presence_deadline')
            ->where('presence_deadline', '<=', now())
            ->whereNull('absence_resolved_at')
            ->with([
                // Eager load relasi yang dibutuhkan agar tidak N+1 query
                'booking.client',
                'booking.expertProfile.user',
                'booking.payment',
                'chatMessages', // akan digunakan untuk deteksi kehadiran
            ])
            ->get();

        Log::info("[InstantConsult] Ditemukan {$expiredSessions->count()} sesi yang perlu dicek.");

        foreach ($expiredSessions as $consultation) {
            try {
                $this->resolveAbsence($consultation, $result);
            } catch (\Throwable $e) {
                // Tangkap error per sesi agar sesi lain tetap diproses
                $result['errors']++;
                Log::error(
                    "[InstantConsult] Gagal memproses consultation #{$consultation->id}: " .
                    $e->getMessage(),
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    // ================================================================
    // CORE LOGIC: tentukan skenario kehadiran & jalankan aksi
    // ================================================================

    /**
     * Tentukan siapa yang tidak hadir, lalu jalankan aksi yang sesuai.
     */
    private function resolveAbsence(Consultation $consultation, array &$result): void
    {
        $chatMessages = $consultation->chatMessages;
        $booking      = $consultation->booking;

        // ── Ambil ID client dan ID user expert untuk perbandingan pengirim pesan ──
        $clientId = $booking->client_id;
        $expertUserId = $booking->expertProfile->user_id;

        // ── Deteksi kehadiran berdasarkan siapa yang mengirim pesan ──
        $expertSentMessage  = $chatMessages->where('sender_id', $expertUserId)->isNotEmpty();
        $clientSentMessage  = $chatMessages->where('sender_id', $clientId)->isNotEmpty();

        // ================================================================
        // SKENARIO A: CLIENT TIDAK HADIR
        // Kondisi: Expert sudah kirim pesan, tapi Client tidak membalas sama sekali
        // Aksi   : Selesaikan sesi, cairkan dana ke Expert, dana Client hangus
        // ================================================================
        if ($expertSentMessage && ! $clientSentMessage) {
            Log::info("[InstantConsult] Consultation #{$consultation->id} — Client tidak hadir. Cairkan ke Expert.");
            $this->handleClientAbsent($consultation, $booking);
            $result['settled']++;
            return;
        }

        // ================================================================
        // SKENARIO B: EXPERT TIDAK HADIR
        // Kondisi: Chat kosong (tidak ada sapaan Expert) setelah 10 menit,
        //          atau Client sudah kirim pesan tapi Expert tidak merespons
        // Aksi   : Batalkan sesi, refund 100% ke Client, tambah penalti Expert
        // ================================================================
        if (! $expertSentMessage) {
            Log::info("[InstantConsult] Consultation #{$consultation->id} — Expert tidak hadir. Refund ke Client.");
            $this->handleExpertAbsent($consultation, $booking);
            $result['refunded']++;
            return;
        }

        // ================================================================
        // SKENARIO C: KEDUANYA HADIR
        // Tidak ada tindakan khusus (sesi berjalan normal),
        // cukup tandai sudah diproses agar tidak masuk query lagi
        // ================================================================
        $consultation->update(['absence_resolved_at' => now()]);
        Log::info("[InstantConsult] Consultation #{$consultation->id} — Kedua pihak hadir. Tidak ada tindakan.");
    }

    // ================================================================
    // SKENARIO A: CLIENT TIDAK HADIR
    // Expert datang, client menghilang → dana client hangus, cair ke expert
    // ================================================================

    /**
     * Tutup sesi & cairkan dana ke Expert.
     * Seluruh operasi dibungkus dalam DB::transaction untuk mencegah data korup.
     */
    private function handleClientAbsent(Consultation $consultation, Booking $booking): void
    {
        DB::transaction(function () use ($consultation, $booking) {

            $payment = $booking->payment;

            if (! $payment || $payment->status !== 'paid') {
                throw new \Exception("Payment booking #{$booking->id} tidak ditemukan atau sudah diproses.");
            }

            // ── 1. Ambil wallet expert dengan lock (cegah race condition) ──
            $expertUser   = $booking->expertProfile->user;
            $expertWallet = Wallet::where('user_id', $expertUser->id)
                                  ->lockForUpdate()
                                  ->firstOrFail();

            $balanceBefore = (float) $expertWallet->balance;

            // ── 2. Tambah saldo expert sesuai expert_earnings (sudah dipotong komisi) ──
            $expertWallet->increment('balance', $payment->expert_earnings);
            $expertWallet->increment('total_earned', $payment->expert_earnings);

            // ── 3. Catat log transaksi masuk ke wallet expert ──
            WalletTransaction::create([
                'wallet_id'      => $expertWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->expert_earnings,
                'balance_before' => $balanceBefore,
                'balance_after'  => $expertWallet->fresh()->balance,
                'description'    => "Pendapatan sesi instant #{$booking->id} — client tidak hadir (dana hangus)",
            ]);

            // ── 4. Update status payment menjadi settled ──
            $payment->update([
                'status'     => 'paid',  // tetap paid, settled_at menandai pencairan
                'settled_at' => now(),
            ]);

            // ── 5. Update status booking menjadi completed ──
            $booking->update([
                'status'          => 'completed',
                'session_ended_at' => now(),
            ]);

            // ── 6. Update status konsultasi menjadi ended ──
            $consultation->update([
                'status'              => 'ended',
                'ended_at'            => now(),
                'summary'             => 'Sesi ditutup otomatis: client tidak hadir dalam 10 menit.',
                'absence_resolved_at' => now(), // tandai sudah diproses
            ]);

            // ── 7. Tambah total_sessions expert (kontribusi untuk naik level) ──
            $expert = $booking->expertProfile;
            $expert->increment('total_sessions');
        });
    }

    // ================================================================
    // SKENARIO B: EXPERT TIDAK HADIR
    // Client menunggu tapi expert tidak datang → refund 100%, tambah penalti
    // ================================================================

    /**
     * Batalkan sesi, refund penuh ke Client, dan tambah penalti pada Expert.
     * Seluruh operasi dibungkus dalam DB::transaction untuk atomisitas.
     */
    private function handleExpertAbsent(Consultation $consultation, Booking $booking): void
    {
        DB::transaction(function () use ($consultation, $booking) {

            $payment = $booking->payment;

            if (! $payment || $payment->status !== 'paid') {
                throw new \Exception("Payment booking #{$booking->id} tidak ditemukan atau sudah diproses.");
            }

            // ── 1. Ambil wallet client dengan lock (cegah race condition) ──
            $clientWallet = Wallet::where('user_id', $booking->client_id)
                                  ->lockForUpdate()
                                  ->firstOrFail();

            $balanceBefore = (float) $clientWallet->balance;

            // ── 2. Kembalikan SELURUH dana (amount, bukan expert_earnings)
            //       karena ini refund penuh — komisi pun dikembalikan ──
            $clientWallet->increment('balance', $payment->amount);
            $clientWallet->decrement('total_withdrawn', $payment->amount);

            // ── 3. Catat log transaksi refund ke wallet client ──
            WalletTransaction::create([
                'wallet_id'      => $clientWallet->id,
                'booking_id'     => $booking->id,
                'type'           => 'credit',
                'amount'         => $payment->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $clientWallet->fresh()->balance,
                'description'    => "Refund 100% sesi instant #{$booking->id} — expert tidak hadir dalam 10 menit",
            ]);

            // ── 4. Update status payment menjadi refunded ──
            $payment->update(['status' => 'refunded']);

            // ── 5. Update status booking menjadi cancelled ──
            $booking->update([
                'status'          => 'cancelled',
                'session_ended_at' => now(),
            ]);

            // ── 6. Update status konsultasi menjadi ended ──
            $consultation->update([
                'status'              => 'ended',
                'ended_at'            => now(),
                'summary'             => 'Sesi dibatalkan otomatis: expert tidak hadir dalam 10 menit.',
                'absence_resolved_at' => now(), // tandai sudah diproses
            ]);

            // ── 7. Tambah nilai penalti pada profil expert ──
            //       Kolom ini bisa digunakan untuk suspensi otomatis di masa depan
            $expertProfile = $booking->expertProfile;
            $expertProfile->increment('penalty_count');

            Log::warning(
                "[InstantConsult] Expert #{$expertProfile->id} (@{$expertProfile->user->name}) " .
                "kini memiliki {$expertProfile->fresh()->penalty_count} penalti."
            );
        });
    }
}
