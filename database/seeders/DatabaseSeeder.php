<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. user dulu (semua aktor)
            CategorySkillSeeder::class, // 2. kategori & skill
            ExpertProfileSeeder::class, // 3. profil expert + skills
            WalletSeeder::class,        // 4. dompet semua user
            AvailabilitySeeder::class,  // 5. slot jadwal expert
        ]);
    }
}