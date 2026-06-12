<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── ADMIN ──
            [
                'username' => 'admin',
                'email'    => 'admin@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'status'   => 'active',
            ],

            // ── EXPERT (3 orang, berbeda kategori) ──
            [
                'username' => 'dr_siti',
                'email'    => 'siti@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'expert',
                'status'   => 'active',
            ],
            [
                'username' => 'budi_desain',
                'email'    => 'budi@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'expert',
                'status'   => 'active',
            ],
            [
                'username' => 'andi_it',
                'email'    => 'andi@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'expert',
                'status'   => 'active',
            ],

            // ── CLIENT (3 orang) ──
            [
                'username' => 'client_rina',
                'email'    => 'rina@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'client',
                'status'   => 'active',
            ],
            [
                'username' => 'client_doni',
                'email'    => 'doni@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'client',
                'status'   => 'active',
            ],
            [
                'username' => 'client_maya',
                'email'    => 'maya@konsultasi.test',
                'password' => Hash::make('password'),
                'role'     => 'client',
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            $created = DB::table('users')->insertGetId(
                array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // buat user_profile untuk setiap user
            DB::table('user_profiles')->insert([
                'user_id'    => $created,
                'name'       => ucwords(str_replace('_', ' ', $user['username'])),
                'phone'      => '08' . rand(100000000, 999999999),
                'gender'     => ['male', 'female'][rand(0, 1)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ UserSeeder selesai — ' . count($users) . ' user dibuat');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            collect($users)->map(fn($u) => [$u['role'], $u['email'], 'password'])->toArray()
        );
    }
}