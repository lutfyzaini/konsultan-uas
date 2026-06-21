<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\ExpertProfile;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingService
{
    // ----------------------------------------------------------------
    // KONSTANTA — ubah di sini kalau mau ganti aturan bisnis
    // ----------------------------------------------------------------
    const LOCK_MINUTES        = 15;  // slot dikunci 15 menit untuk bayar
    const MIN_LEAD_TIME_HOURS = 2;   // minimal pesan 2 jam sebelum sesi

    // ----------------------------------------------------------------
    // LOCK SLOT
    // Dipanggil saat client memilih slot dan klik "Booking"
    // Mengembalikan Booking baru, atau throw Exception jika gagal
    // ----------------------------------------------------------------
    public function lockSlot(int $availabilityId, int $clientId): Booking
    {
        return DB::transaction(function () use ($availabilityId, $clientId) {

            // 1. Lock baris di database (pessimistic locking)
            //    Ini mencegah double-booking jika ada 2 request bersamaan
            $slot = Availability::lockForUpdate()->findOrFail($availabilityId);

            // 2. Cek slot masih tersedia
            if ($slot->status !== 'available') {
                throw new \Exception('Slot ini sudah tidak tersedia. Silakan pilih waktu lain.');
            }

            // 3. Cek slot masih aktif
            if (! $slot->is_active) {
                throw new \Exception('Slot ini sudah dinonaktifkan oleh Expert.');
            }

            // 4. Hitung tanggal booking terdekat berdasarkan hari
            $bookingDate = $this->getNextBookingDate($slot->day_of_week);

            // 5. Cek lead time — minimal 2 jam sebelum sesi
            $sessionDateTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $slot->start_time);
            if ($sessionDateTime->diffInHours(now(), false) > -self::MIN_LEAD_TIME_HOURS) {
                throw new \Exception('Pemesanan minimal ' . self::MIN_LEAD_TIME_HOURS . ' jam sebelum sesi dimulai.');
            }

            // 6. Update status slot → locked
            $slot->update([
                'status'    => 'locked',
                'locked_at' => now(),
                'locked_by' => $clientId,
            ]);

            // 7. Buat record booking baru
            $expert = ExpertProfile::findOrFail($slot->expert_profile_id);

            $lockMinutes = (int) \App\Models\PlatformSetting::getValue('auto_cancel_minutes', 15);

            $booking = Booking::create([
                'client_id'           => $clientId,
                'expert_profile_id'   => $slot->expert_profile_id,
                'availability_id'     => $slot->id,
                'booking_date'        => $bookingDate,
                'start_time'          => $slot->start_time,
                'end_time'            => $slot->end_time,
                'status'              => 'pending_payment',
                'total_price'         => $expert->hourly_rate,
                'payment_deadline'    => now()->addMinutes($lockMinutes),
            ]);

            return $booking;
        });
    }

    // ----------------------------------------------------------------
    // BATALKAN BOOKING (karena expired atau client cancel)
    // ----------------------------------------------------------------
    public function cancelBooking(Booking $booking, string $reason = 'expired'): void
    {
        DB::transaction(function () use ($booking, $reason) {

            // kembalikan slot ke available
            $slot = Availability::find($booking->availability_id);
            if ($slot) {
                $slot->update([
                    'status'    => 'available',
                    'locked_at' => null,
                    'locked_by' => null,
                ]);
            }

            // check if status was confirmed (paid) -> refund
            if ($booking->status === 'confirmed') {
                $payment = $booking->payment;
                if ($payment && $payment->status === 'paid') {
                    $clientWallet = \App\Models\Wallet::where('user_id', $booking->client_id)->lockForUpdate()->first();
                    
                    // Cek sisa waktu menuju sesi
                    $sessionStart = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
                    $hoursToSession = now()->diffInHours($sessionStart, false);

                    if ($hoursToSession >= 0 && $hoursToSession < 2) {
                        // Refund 80% ke client, 20% kompensasi ke expert
                        $refundAmount = $payment->amount * 0.8;
                        $compensationAmount = $payment->amount * 0.2;

                        if ($clientWallet) {
                            $balanceBeforeClient = $clientWallet->balance;
                            $clientWallet->increment('balance', $refundAmount);
                            $clientWallet->decrement('total_withdrawn', $refundAmount);

                            \App\Models\WalletTransaction::create([
                                'wallet_id'      => $clientWallet->id,
                                'booking_id'     => $booking->id,
                                'type'           => 'credit',
                                'amount'         => $refundAmount,
                                'balance_before' => $balanceBeforeClient,
                                'balance_after'  => $clientWallet->fresh()->balance,
                                'description'    => 'Refund 80% (batal < 2 jam) booking #' . $booking->id,
                            ]);
                        }

                        $expertUser = $booking->expertProfile->user;
                        $expertWallet = \App\Models\Wallet::where('user_id', $expertUser->id)->lockForUpdate()->first();
                        if ($expertWallet) {
                            $balanceBeforeExpert = $expertWallet->balance;
                            $expertWallet->increment('balance', $compensationAmount);
                            $expertWallet->increment('total_earned', $compensationAmount);

                            \App\Models\WalletTransaction::create([
                                'wallet_id'      => $expertWallet->id,
                                'booking_id'     => $booking->id,
                                'type'           => 'credit',
                                'amount'         => $compensationAmount,
                                'balance_before' => $balanceBeforeExpert,
                                'balance_after'  => $expertWallet->fresh()->balance,
                                'description'    => 'Kompensasi 20% pembatalan client < 2 jam, booking #' . $booking->id,
                            ]);
                        }
                    } else {
                        // Refund 100% ke client
                        if ($clientWallet) {
                            $balanceBeforeClient = $clientWallet->balance;
                            $clientWallet->increment('balance', $payment->amount);
                            $clientWallet->decrement('total_withdrawn', $payment->amount);

                            \App\Models\WalletTransaction::create([
                                'wallet_id'      => $clientWallet->id,
                                'booking_id'     => $booking->id,
                                'type'           => 'credit',
                                'amount'         => $payment->amount,
                                'balance_before' => $balanceBeforeClient,
                                'balance_after'  => $clientWallet->fresh()->balance,
                                'description'    => 'Refund 100% pembatalan booking #' . $booking->id,
                            ]);
                        }
                    }

                    $payment->update(['status' => 'refunded']);
                }
            }

            // update status booking
            $booking->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason,
            ]);
        });
    }

    // ----------------------------------------------------------------
    // MULAI SESI KONSULTASI
    // Dipanggil saat waktu sesi tiba dan expert/client masuk ruang chat
    // ----------------------------------------------------------------
    public function startSession(Booking $booking): void
    {
        if ($booking->status !== 'confirmed') {
            throw new \Exception('Sesi tidak dapat dimulai — status booking bukan confirmed.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status'             => 'ongoing',
                'session_started_at' => now(),
            ]);

            // buat record konsultasi
            $booking->consultation()->create([
                'type'       => 'chat',
                'status'     => 'active',
                'started_at' => now(),
            ]);
        });
    }

    // ----------------------------------------------------------------
    // AKHIRI SESI (otomatis di menit ke-60 atau client klik selesai)
    // ----------------------------------------------------------------
    public function endSession(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {

            $now = now();

            // update booking
            $booking->update([
                'status'           => 'pending_settlement',
                'session_ended_at' => $now,
            ]);

            // update konsultasi
            if ($booking->consultation) {
                $booking->consultation->update([
                    'status'   => 'ended',
                    'ended_at' => $now,
                ]);
            }

            // update slot → selesai dipakai
            Availability::where('id', $booking->availability_id)
                ->update(['status' => 'booked']);
        });
    }

    public function checkAndAutoEndSession(Booking $booking): void
    {
        if ($booking->status === 'ongoing' && $booking->session_started_at) {
            $durationHours = (int) \App\Models\PlatformSetting::getValue('session_duration_hours', 1);
            $limitTime = Carbon::parse($booking->session_started_at)->addHours($durationHours);
            if (now()->isAfter($limitTime)) {
                $this->endSession($booking);
            }
        }
    }

    // ----------------------------------------------------------------
    // RILIS SLOT EXPIRED (dipanggil oleh cron job setiap menit)
    // ----------------------------------------------------------------
    public function releaseExpiredSlots(): int
    {
        $lockMinutes = (int) \App\Models\PlatformSetting::getValue('auto_cancel_minutes', 15);
        $expiredTime = now()->subMinutes($lockMinutes);
        $count       = 0;

        // ambil semua slot yang sudah locked > 15 menit
        $expiredSlots = Availability::where('status', 'locked')
            ->where('locked_at', '<=', $expiredTime)
            ->get();

        foreach ($expiredSlots as $slot) {
            DB::transaction(function () use ($slot, &$count) {

                // rilis slot
                $slot->update([
                    'status'    => 'available',
                    'locked_at' => null,
                    'locked_by' => null,
                ]);

                // batalkan booking yang terkait
                Booking::where('availability_id', $slot->id)
                    ->where('status', 'pending_payment')
                    ->update(['status' => 'cancelled']);

                $count++;
            });
        }

        return $count; // jumlah slot yang dirilis
    }

    // ----------------------------------------------------------------
    // HELPER: hitung tanggal booking terdekat berdasarkan nama hari
    // Contoh: 'Senin' → Carbon date Senin minggu ini atau depan
    // ----------------------------------------------------------------
    private function getNextBookingDate(string $dayName): Carbon
    {
        $dayMap = [
            'Senin'  => Carbon::MONDAY,
            'Selasa' => Carbon::TUESDAY,
            'Rabu'   => Carbon::WEDNESDAY,
            'Kamis'  => Carbon::THURSDAY,
            'Jumat'  => Carbon::FRIDAY,
            'Sabtu'  => Carbon::SATURDAY,
            'Minggu' => Carbon::SUNDAY,
        ];

        $targetDay = $dayMap[$dayName] ?? Carbon::MONDAY;
        $today     = now();

        // kalau hari ini adalah hari yang dicari, cek apakah masih ada slot
        // yang bisa dipesan (minimal 2 jam ke depan) — kalau tidak, ambil minggu depan
        $date = $today->copy()->next($targetDay);

        // kalau ternyata targetDay = hari ini
        if ($today->dayOfWeek === $targetDay) {
            $date = $today->copy();
        }

        return $date->startOfDay();
    }

    const ATTENDANCE_MINUTES = 10; // batas hadir di ruang chat

    // ----------------------------------------------------------------
    // BUAT BOOKING INSTANT (tanpa slot, langsung untuk expert online)
    // Dipanggil saat client klik "Konsultasi Sekarang" di profil expert
    // ----------------------------------------------------------------
    public function createInstantBooking(int $expertProfileId, int $clientId): Booking
    {
        return DB::transaction(function () use ($expertProfileId, $clientId) {

            $expert = ExpertProfile::lockForUpdate()->findOrFail($expertProfileId);

            if (! $expert->is_online) {
                throw new \Exception('Expert ini sedang tidak online.');
            }

            if ($expert->verification_status !== 'approved') {
                throw new \Exception('Expert ini belum terverifikasi.');
            }

            // cek client tidak punya instant booking lain yang masih aktif
            $activeInstant = Booking::where('client_id', $clientId)
                ->where('booking_type', 'instant')
                ->whereIn('status', ['pending_payment', 'confirmed', 'ongoing'])
                ->exists();

            if ($activeInstant) {
                throw new \Exception('Kamu masih punya sesi instant yang aktif. Selesaikan dulu sebelum membuat sesi baru.');
            }

            $lockMinutes = (int) \App\Models\PlatformSetting::getValue('auto_cancel_minutes', 15);

            $booking = Booking::create([
                'client_id'         => $clientId,
                'expert_profile_id' => $expert->id,
                'availability_id'   => null, // instant tidak pakai slot terjadwal
                'booking_date'      => now()->toDateString(),
                'start_time'        => now()->toTimeString(),
                'end_time'          => now()->addHour()->toTimeString(),
                'status'            => 'pending_payment',
                'booking_type'      => 'instant',
                'total_price'       => $expert->hourly_rate,
                'payment_deadline'  => now()->addMinutes($lockMinutes),
            ]);

            return $booking;
        });
    }

    // ----------------------------------------------------------------
    // MULAI SESI INSTANT (setelah bayar) — beda dari startSession biasa
    // karena langsung set attendance_deadline 10 menit
    // ----------------------------------------------------------------
    public function startInstantSession(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->update([
                'status'               => 'ongoing',
                'session_started_at'   => now(),
                'attendance_deadline'  => now()->addMinutes(self::ATTENDANCE_MINUTES),
            ]);

            $booking->consultation()->create([
                'type'       => 'chat',
                'status'     => 'active',
                'started_at' => now(),
            ]);
        });
    }

    // ----------------------------------------------------------------
    // TANDAI KEHADIRAN: dipanggil saat client/expert masuk ruang chat
    // ----------------------------------------------------------------
    public function markAttendance(Booking $booking, string $role): void
    {
        if ($role === 'client') {
            $booking->update(['client_joined' => true]);
        } elseif ($role === 'expert') {
            $booking->update(['expert_joined' => true]);
        }

        // kalau keduanya sudah hadir, sesi resmi dimulai (lewati pengecekan no-show)
        if ($booking->fresh()->client_joined && $booking->fresh()->expert_joined) {
            $booking->update(['attendance_deadline' => null]);
        }
    }
}