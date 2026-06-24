<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Mail\SessionReminderMail;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\Mail;

#[Signature('bookings:send-reminders')]
#[Description('Mencari booking yang berstatus confirmed dan jadwalnya akan dimulai dalam 30 menit ke depan, lalu mengirimkan email pengingat.')]
class SendSessionReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $dateString = $now->toDateString();
        $timeStringEnd = $now->copy()->addMinutes(30)->toTimeString();

        // Cari booking hari ini yang berstatus confirmed, tipe scheduled, mulai dalam 30 menit, dan belum dikirimi pengingat
        $bookings = Booking::where('status', 'confirmed')
            ->where('booking_type', 'scheduled')
            ->where('booking_date', $dateString)
            ->whereTime('start_time', '<=', $timeStringEnd)
            ->where('reminder_sent', false)
            ->with(['client.profile', 'expertProfile.user.profile'])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('Tidak ada sesi booking terkonfirmasi yang memerlukan pengingat dalam 30 menit ke depan.');
            return 0;
        }

        $this->info("Ditemukan {$bookings->count()} sesi booking. Mengirimkan email pengingat...");

        foreach ($bookings as $booking) {
            try {
                // Kirim email ke Client
                if ($booking->client && $booking->client->email) {
                    Mail::to($booking->client->email)->send(new SessionReminderMail($booking, 'client'));
                }

                // Kirim email ke Expert
                $expertUser = $booking->expertProfile->user ?? null;
                if ($expertUser && $expertUser->email) {
                    Mail::to($expertUser->email)->send(new SessionReminderMail($booking, 'expert'));
                }

                // Tandai sebagai terkirim
                $booking->update(['reminder_sent' => true]);
                $this->info("Pengingat berhasil dikirim untuk Booking ID: {$booking->id}");
            } catch (\Exception $e) {
                $this->error("Gagal mengirimkan pengingat untuk Booking ID: {$booking->id}. Error: " . $e->getMessage());
            }
        }

        $this->info('Proses pengiriman pengingat selesai.');
        return 0;
    }
}
