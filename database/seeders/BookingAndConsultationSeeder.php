<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingAndConsultationSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua data expert profile yang ada
        $experts = DB::table('expert_profiles')->get();
        // Ambil semua client id (role = client)
        $clientIds = DB::table('users')->where('role', 'client')->pluck('id')->toArray();

        foreach ($experts as $expert) {
            // 1. Buat 3 Slot Ketersediaan (Availabilities) untuk masing-masing Expert
            $days = ['Senin', 'Rabu', 'Jumat'];
            foreach ($days as $index => $day) {
                $start = "10:00:00";
                $end = "11:00:00";
                
                // Set slot pertama dan kedua sebagai 'booked' karena sudah dilewati, slot ketiga 'available'
                $slotStatus = ($index < 2) ? 'booked' : 'available';

                $availabilityId = DB::table('availabilities')->insertGetId([
                    'expert_profile_id' => $expert->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'is_active' => true,
                    'status' => $slotStatus,
                    'locked_at' => null,
                    'locked_by' => null,
                ]);

                // 2. Jika status slot 'booked', buatkan data transaksi Booking-nya sekalian
                if ($slotStatus === 'booked') {
                    $randomClientId = $clientIds[array_rand($clientIds)];
                    
                    $bookingId = DB::table('bookings')->insertGetId([
                        'client_id' => $randomClientId,
                        'expert_profile_id' => $expert->id,
                        'availability_id' => $availabilityId,
                        'booking_date' => Carbon::now()->subDays(rand(1, 5))->format('Y-m-d'),
                        'start_time' => $start,
                        'end_time' => $end,
                        'status' => 'pending_settlement', // status selesai sesi / menunggu konfirmasi pencairan dana
                        'client_notes' => 'Halo, saya ingin berkonsultasi mengenai studi kasus proyek saya.',
                        'total_price' => $expert->hourly_rate,
                        'created_at' => Carbon::now()->subDays(6),
                    ]);

                    // 3. Otomatis buatkan data di tabel Consultations (Sesi Chat) yang terikat dengan booking tersebut
                    DB::table('consultasions')->insert([
                        'booking_id' => $bookingId,
                        'type' => 'chat',
                        'summary' => 'Konsultasi berjalan lancar. Expert memberikan solusi arsitektur yang komprehensif.',
                        'status' => 'ended',
                        'started_at' => Carbon::now()->subDays(2)->setHour(10)->setMinute(0),
                        'ended_at' => Carbon::now()->subDays(2)->setHour(11)->setMinute(0),
                    ]);
                }
            }
        }
    }
}