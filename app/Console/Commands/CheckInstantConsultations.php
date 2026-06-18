<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckInstantConsultations extends Command
{
    // Pastikan signature ini yang dipanggil di routes/console.php
    protected $signature = 'instant:check-attendance';
    protected $description = 'Memeriksa kehadiran chat setelah sesi instant berjalan 10 menit';

    public function handle()
    {
        $now = Carbon::now();

        // Ambil sesi konsultasi instan yang aktif dan waktu batas hadirnya sudah lewat (attendance_deadline <= now)
        $expiredBookings = DB::table('bookings')
            ->join('consultations', 'bookings.id', '=', 'consultations.booking_id')
            ->where('bookings.booking_type', 'instant')
            ->where('bookings.status', 'ongoing')
            ->where('bookings.attendance_deadline', '<=', $now)
            ->select('bookings.*', 'consultations.id as consultation_id')
            ->get();

        foreach ($expiredBookings as $booking) {
            
            // Hitung aktivitas pesan chat di tabel chat_message milikmu
            $expertChatCount = DB::table('chat_message')
                ->where('consultation_id', $booking->consultation_id)
                ->where('sender_id', function($query) use ($booking) {
                    $query->select('user_id')->from('expert_profiles')->where('id', $booking->expert_profile_id);
                })->count();

            $clientChatCount = DB::table('chat_message')
                ->where('consultation_id', $booking->consultation_id)
                ->where('sender_id', $booking->client_id)
                ->count();

            DB::transaction(function () use ($booking, $expertChatCount, $clientChatCount) {
                
                // KASUS A: Client Ghaib (Expert sudah masuk & chat, Client tidak merespon sama sekali)
                if ($expertChatCount > 0 && $clientChatCount == 0) {
                    DB::table('bookings')->where('id', $booking->id)->update([
                        'status' => 'completed',
                        'client_notes' => 'CLIENT_NO_SHOW',
                        'updated_at' => Carbon::now()
                    ]);
                    DB::table('consultations')->where('booking_id', $booking->id)->update([
                        'status' => 'ended',
                        'updated_at' => Carbon::now()
                    ]);

                    // Salurkan dana ke wallet Expert karena dia sudah meluangkan waktu stand-by
                    $expertUser = DB::table('expert_profiles')->where('id', $booking->expert_profile_id)->first();
                    $expertWallet = DB::table('wallets')->where('user_id', $expertUser->user_id)->first();
                    
                    if ($expertWallet) {
                        DB::table('wallets')->where('id', $expertWallet->id)->increment('balance', $booking->total_price);
                        DB::table('wallet_transactions')->insert([
                            'wallet_id' => $expertWallet->id,
                            'booking_id' => $booking->id,
                            'type' => 'credit',
                            'amount' => $booking->total_price,
                            'balance_before' => $expertWallet->balance,
                            'balance_after' => $expertWallet->balance + $booking->total_price,
                            'description' => 'Pencairan dana: Client tidak hadir pada sesi instant',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
                
                // KASUS B: Expert Ghaib (Client sudah menunggu, Expert tidak menyapa/mengirim pesan sama sekali)
                else if ($expertChatCount == 0) {
                    DB::table('bookings')->where('id', $booking->id)->update([
                        'status' => 'cancelled',
                        'client_notes' => 'EXPERT_ABSENT_REFUND', // Penanda khusus untuk memicu alert di dashboard client
                        'updated_at' => Carbon::now()
                    ]);
                    DB::table('consultations')->where('booking_id', $booking->id)->update([
                        'status' => 'ended',
                        'updated_at' => Carbon::now()
                    ]);

                    // 1. REFUND 100% ke Wallet Client
                    $clientWallet = DB::table('wallets')->where('user_id', $booking->client_id)->first();
                    if ($clientWallet) {
                        DB::table('wallets')->where('id', $clientWallet->id)->increment('balance', $booking->total_price);
                        DB::table('wallet_transactions')->insert([
                            'wallet_id' => $clientWallet->id,
                            'booking_id' => $booking->id,
                            'type' => 'credit',
                            'amount' => $booking->total_price,
                            'balance_before' => $clientWallet->balance,
                            'balance_after' => $clientWallet->balance + $booking->total_price,
                            'description' => 'Refund otomatis 100%: Expert tidak hadir dalam 10 menit',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }

                    // 2. Naikkan angka pinalti Expert penalty_count (+1)
                    DB::table('expert_profiles')->where('id', $booking->expert_profile_id)->increment('penalty_count');
                }
            });
        }

        $this->info('Pengecekan aturan kehadiran konsultasi langsung selesai.');
    }
}