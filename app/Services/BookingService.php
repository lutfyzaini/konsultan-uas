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

            $booking = Booking::create([
                'client_id'           => $clientId,
                'expert_profile_id'   => $slot->expert_profile_id,
                'availability_id'     => $slot->id,
                'booking_date'        => $bookingDate,
                'start_time'          => $slot->start_time,
                'end_time'            => $slot->end_time,
                'status'              => 'pending_payment',
                'total_price'         => $expert->hourly_rate,
                'payment_deadline'    => now()->addMinutes(self::LOCK_MINUTES),
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

            // update status booking
            $booking->update(['status' => 'cancelled']);
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

    // ----------------------------------------------------------------
    // RILIS SLOT EXPIRED (dipanggil oleh cron job setiap menit)
    // ----------------------------------------------------------------
    public function releaseExpiredSlots(): int
    {
        $expiredTime = now()->subMinutes(self::LOCK_MINUTES);
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
}