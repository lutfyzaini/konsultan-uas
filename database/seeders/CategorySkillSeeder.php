<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySkillSeeder extends Seeder
{
    public function run(): void
    {
        // ── KATEGORI KEAHLIAN ──
        $categories = [
            'Hukum & Legalitas',
            'Desain & Kreatif',
            'Teknologi & IT',
            'Keuangan & Akuntansi',
            'Kesehatan & Medis',
            'Pendidikan & Pelatihan',
        ];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── SKILL SPESIFIK ──
        $skills = [
            // Hukum
            'Hukum Perdata', 'Hukum Pidana', 'Hukum Bisnis', 'Kontrak & Perjanjian',
            // Desain
            'UI/UX Design', 'Graphic Design', 'Branding', 'Ilustrasi Digital',
            // IT
            'Web Development', 'Mobile Development', 'Database', 'Cybersecurity',
            // Keuangan
            'Akuntansi', 'Pajak', 'Investasi', 'Perencanaan Keuangan',
            // Kesehatan
            'Konsultasi Gizi', 'Kesehatan Mental', 'Olahraga & Kebugaran',
            // Pendidikan
            'Matematika', 'Bahasa Inggris', 'Pemrograman', 'Public Speaking',
        ];

        foreach ($skills as $name) {
            DB::table('skills')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ CategorySkillSeeder — ' . count($categories) . ' kategori, ' . count($skills) . ' skill');
    }
}