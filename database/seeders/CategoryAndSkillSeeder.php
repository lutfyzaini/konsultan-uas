<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryAndSkillSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Categories
        $categories = ['Hukum', 'Desain Interior', 'Teknologi & Koding', 'Bisnis & Keuangan'];
        foreach ($categories as $cat) {
            DB::table('categories')->insert(['name' => $cat]);
        }

        // 2. Seed Skills
        $skills = [
            // Hukum
            'Hukum Perdata', 'Hukum Pidana', 'Kontrak Bisnis', 'Hak Kekayaan Intelektual',
            // Desain
            'Desain 3D Blender', 'Sketchup Layout', 'Interior Skandinavia', 'Minimalis Urban',
            // IT / Koding
            'Laravel & PHP OOP', 'Tailwind CSS', 'REST API Development', 'React.js',
            // Bisnis
            'Perencanaan Pajak', 'Strategi Pitch Deck', 'Digital Marketing', 'Manajemen Risiko'
        ];

        foreach ($skills as $skill) {
            DB::table('skills')->insert(['name' => $skill]);
        }
    }
}