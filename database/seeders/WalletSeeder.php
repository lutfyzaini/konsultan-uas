<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            // saldo awal berbeda per role
            $balance = match($user->role) {
                'client' => 500000,  // client punya saldo untuk testing bayar
                'expert' => 0,       // expert mulai dari 0, nambah saat sesi settled
                'admin'  => 0,
                default  => 0,
            };

            DB::table('wallets')->insert([
                'user_id'          => $user->id,
                'balance'          => $balance,
                'total_earned'     => 0,
                'total_withdrawn'  => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        $this->command->info('✓ WalletSeeder — dompet dibuat untuk ' . $users->count() . ' user');
        $this->command->line('  Client saldo awal: Rp 500.000 (cukup untuk 2–3 sesi)');
    }
}