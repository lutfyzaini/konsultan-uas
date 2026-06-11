<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperationalAndWalletSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT WALLET UNTUK SEMUA USER YANG ADA
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            // Jika client berikan saldo simulasi awal 500rb, jika expert/admin mulai dari 0
            $initialBalance = ($user->role === 'client') ? 500000.00 : 0.00;
            
            DB::table('wallets')->insert([
                'user_id' => $user->id,
                'balance' => $initialBalance,
                'total_earned' => 0.00,
                'total_withdrawn' => 0.00
            ]);
        }

        // 2. GENERATE INVOICE, CHAT LOGS, DAN REVIEWS DARI BOOKING YANG SUDAH ADA
        $bookings = DB::table('bookings')->get();
        foreach ($bookings as $booking) {
            
            // A. Seed data Invoice Pembayaran (Payments)
            DB::table('payments')->insert([
                'booking_id' => $booking->id,
                'invoice' => 'INV-' . strtoupper(Str::random(8)),
                'amount' => $booking->total_price,
                'method' => 'wallet',
                'status' => 'paid',
                'paid_at' => $booking->created_at
            ]);

            // Ambil data sesi konsultasi terkait untuk diletakkan chat messages-nya
            $consultation = DB::table('consultasions')->where('booking_id', $booking->id)->first();
            
            if ($consultation) {
                // B. Seed Percakapan Teks Tiruan di Chat Messages (Simulasi Alur Tanya Jawab)
                DB::table('chat_messages')->insert([
                    [
                        'consultation_id' => $consultation->id,
                        'sender_id' => $booking->client_id,
                        'message' => 'Halo, selamat pagi. Saya ingin berkonsultasi mengenai kendala arsitektur di proyek web saya.',
                        'type' => 'text',
                        'is_read' => true,
                        'sent_at' => $consultation->started_at
                    ],
                    [
                        'consultation_id' => $consultation->id,
                        'sender_id' => DB::table('expert_profiles')->where('id', $booking->expert_profile_id)->value('user_id'),
                        'message' => 'Selamat pagi! Silakan ceritakan detail kendala atau bagikan potongan kode yang bermasalah.',
                        'type' => 'text',
                        'is_read' => true,
                        'sent_at' => \Carbon\Carbon::parse($consultation->started_at)->addMinutes(5)
                    ]
                ]);
            }

            // C. Seed Ulasan Klien (Reviews)
            DB::table('reviews')->insert([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'expert_profile_id' => $booking->expert_profile_id,
                'rating' => rand(4, 5), // Rating bintang 4 atau 5
                'comment' => 'Penjelasan sangat terstruktur, solutif, dan mudah dipahami oleh pemula!',
                'created_at' => $booking->booking_date
            ]);
        }
    }
}