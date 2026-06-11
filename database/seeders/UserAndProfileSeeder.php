<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserAndProfileSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $password = Hash::make('password123');

        // 1. SEED ADMIN
        $adminId = DB::table('users')->insertGetId([
            'username' => 'admin_utama',
            'email' => 'admin@platform.com',
            'password' => $password,
            'role' => 'admin',
            'commission_level' => 'newbie',
        ]);
        DB::table('user_profiles')->insert([
            'user_id' => $adminId,
            'name' => 'Administrator Sistem',
            'phone' => '081234567890',
            'gender' => 'male',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Admin',
        ]);

        // 2. SEED CLIENTS
        for ($i = 1; $i <= 5; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $firstName = ($gender == 'male') ? $faker->firstNameMale : $faker->firstNameFemale;
            $fullName = "$firstName " . $faker->lastName;
            
            $clientId = DB::table('users')->insertGetId([
                'username' => strtolower($firstName) . $faker->numberBetween(10, 99),
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'role' => 'client',
                'commission_level' => 'newbie',
            ]);

            DB::table('user_profiles')->insert([
                'user_id' => $clientId,
                'name' => $fullName,
                'phone' => '08' . $faker->numerify('##########'),
                'gender' => $gender,
                'avatar_url' => "https://ui-avatars.com/api/?name=" . urlencode($fullName),
            ]);
        }

        // 3. SEED EXPERTS
        $titles = [
            1 => 'Konsultan Hukum Perusahaan & Agraria',
            2 => 'Principal Interior Designer & Space Planner',
            3 => 'Senior Fullstack Web Developer',
            4 => 'Financial Planner & Tax Consultant'
        ];

        $bios = [
            1 => 'Berpengalaman 8 tahun dalam menangani sengketa bisnis, pembuatan draf kontrak legalitas, dan konsultasi hukum perdata.',
            2 => 'Fokus pada efisiensi ruang komersial dan residensial bergaya minimalis modern dengan sentuhan arsitektur lokal.',
            3 => 'Spesialis backend arsitektur tangguh berbasis Laravel dan ekosistem PHP modern. Siap membantu debugging code Anda.',
            4 => 'Membantu UMKM dan korporasi menyusun laporan keuangan, audit internal, serta optimasi kepatuhan pajak tahunan.'
        ];

        for ($catId = 1; $catId <= 4; $catId++) {
            $gender = $faker->randomElement(['male', 'female']);
            $firstName = ($gender == 'male') ? $faker->firstNameMale : $faker->firstNameFemale;
            $fullName = $firstName . ' ' . $faker->lastName;

            $expertUserId = DB::table('users')->insertGetId([
                'username' => 'expert_' . strtolower($firstName),
                'email' => 'expert' . $catId . '@platform.com',
                'password' => $password,
                'role' => 'expert',
                'commission_level' => $faker->randomElement(['newbie', 'pro', 'master']),
            ]);

            DB::table('user_profiles')->insert([
                'user_id' => $expertUserId,
                'name' => $fullName,
                'phone' => '08' . $faker->numerify('##########'),
                'gender' => $gender,
                'avatar_url' => "https://ui-avatars.com/api/?name=" . urlencode($fullName),
            ]);

            $expertProfileId = DB::table('expert_profiles')->insertGetId([
                'user_id' => $expertUserId,
                'category_id' => $catId,
                'title' => $titles[$catId],
                'bio' => $bios[$catId],
                'location' => $faker->randomElement(['Jakarta', 'Bandung', 'Yogyakarta', 'Surakarta', 'Boyolali']),
                'experience_years' => $faker->numberBetween(3, 12),
                'hourly_rate' => $faker->randomElement([75000, 100000, 150000, 250000]),
                'is_online' => $faker->boolean(70),
                'is_verified' => 'approved',
                'total_sessions' => $faker->numberBetween(5, 40),
                'average_rating' => $faker->randomFloat(2, 4, 5),
            ]);

            $skillOffsetStart = (($catId - 1) * 4) + 1;
            for ($j = 0; $j < 2; $j++) {
                DB::table('expert_skills')->insert([
                    'expert_profile_id' => $expertProfileId,
                    'skill_id' => $skillOffsetStart + $j
                ]);
            }
        }
    }
}