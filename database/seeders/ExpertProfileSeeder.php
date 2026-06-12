<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpertProfileSeeder extends Seeder
{
    public function run(): void
    {
        // ambil ID user expert yang sudah dibuat
        $siti  = DB::table('users')->where('email', 'siti@konsultasi.test')->value('id');
        $budi  = DB::table('users')->where('email', 'budi@konsultasi.test')->value('id');
        $andi  = DB::table('users')->where('email', 'andi@konsultasi.test')->value('id');

        // ambil ID kategori
        $catHukum  = DB::table('categories')->where('name', 'Hukum & Legalitas')->value('id');
        $catDesain = DB::table('categories')->where('name', 'Desain & Kreatif')->value('id');
        $catIT     = DB::table('categories')->where('name', 'Teknologi & IT')->value('id');

        $experts = [
            [
                'user_id'             => $siti,
                'category_id'         => $catHukum,
                'title'               => 'Konsultan Hukum & Advokat',
                'bio'                 => 'Berpengalaman 10 tahun di bidang hukum perdata dan bisnis. Menangani lebih dari 200 kasus.',
                'location'            => 'Jakarta',
                'experience_years'    => 10,
                'hourly_rate'         => 150000,
                'is_online'           => true,
                'verification_status' => 'approved', // sudah diverifikasi — muncul di katalog
                'total_sessions'      => 12,         // level Pro (10–49)
                'average_rating'      => 4.80,
                'commission_level'    => 'pro',
            ],
            [
                'user_id'             => $budi,
                'category_id'         => $catDesain,
                'title'               => 'UI/UX Designer & Brand Consultant',
                'bio'                 => 'Spesialis desain antarmuka dan identitas merek untuk startup dan UMKM.',
                'location'            => 'Bandung',
                'experience_years'    => 5,
                'hourly_rate'         => 100000,
                'is_online'           => true,
                'verification_status' => 'approved', // sudah diverifikasi
                'total_sessions'      => 3,          // level Newbie (<10)
                'average_rating'      => 4.50,
                'commission_level'    => 'newbie',
            ],
            [
                'user_id'             => $andi,
                'category_id'         => $catIT,
                'title'               => 'Full Stack Developer & IT Consultant',
                'bio'                 => 'Developer dengan keahlian Laravel, React, dan arsitektur sistem cloud.',
                'location'            => 'Surabaya',
                'experience_years'    => 7,
                'hourly_rate'         => 200000,
                'is_online'           => false,
                // sengaja pending — untuk testing fitur verifikasi admin
                'verification_status' => 'pending',
                'total_sessions'      => 0,
                'average_rating'      => 0.00,
                'commission_level'    => 'newbie',
            ],
        ];

        foreach ($experts as $expert) {
            $expertId = DB::table('expert_profiles')->insertGetId(
                array_merge($expert, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // tambahkan skill ke masing-masing expert
            $this->attachSkills($expertId, $expert['category_id']);
        }

        $this->command->info('✓ ExpertProfileSeeder — 3 expert (2 approved, 1 pending)');
    }

    private function attachSkills(int $expertId, int $categoryId): void
    {
        // mapping kategori → skill yang relevan
        $catHukum  = DB::table('categories')->where('name', 'Hukum & Legalitas')->value('id');
        $catDesain = DB::table('categories')->where('name', 'Desain & Kreatif')->value('id');

        $skillMap = [
            $catHukum  => ['Hukum Perdata', 'Hukum Bisnis', 'Kontrak & Perjanjian'],
            $catDesain => ['UI/UX Design', 'Graphic Design', 'Branding'],
        ];

        $skillNames = $skillMap[$categoryId]
            ?? ['Web Development', 'Database', 'Mobile Development'];

        foreach ($skillNames as $skillName) {
            $skillId = DB::table('skills')->where('name', $skillName)->value('id');
            if ($skillId) {
                DB::table('expert_skills')->insert([
                    'expert_profile_id' => $expertId,
                    'skill_id'          => $skillId,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
}