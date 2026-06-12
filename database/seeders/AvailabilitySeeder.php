<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // hanya buat slot untuk expert yang sudah approved
        $approvedExperts = DB::table('expert_profiles')
            ->where('verification_status', 'approved')
            ->get();

        // slot waktu yang umum dipakai konsultan
        $timeSlots = [
            ['08:00', '09:00'],
            ['09:00', '10:00'],
            ['10:00', '11:00'],
            ['13:00', '14:00'],
            ['14:00', '15:00'],
            ['15:00', '16:00'],
            ['19:00', '20:00'],
            ['20:00', '21:00'],
        ];

        $workDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $count    = 0;

        foreach ($approvedExperts as $expert) {
            // tiap expert punya jadwal di hari kerja (Senin–Jumat)
            foreach ($workDays as $day) {
                // ambil 4 slot random per hari agar tidak terlalu penuh
                $slots = collect($timeSlots)->random(4);

                foreach ($slots as [$start, $end]) {
                    DB::table('availabilities')->insert([
                        'expert_profile_id' => $expert->id,
                        'day_of_week'       => $day,
                        'start_time'        => $start,
                        'end_time'          => $end,
                        'is_active'         => true,
                        'status'            => 'available',
                        'locked_at'         => null,
                        'locked_by'         => null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("✓ AvailabilitySeeder — {$count} slot jadwal dibuat");
        $this->command->line('  Senin–Jumat, 4 slot/hari per expert yang approved');
    }
}