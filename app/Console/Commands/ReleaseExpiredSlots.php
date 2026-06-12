<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

// ================================================================
// COMMAND 1: Rilis slot yang expired (lock > 15 menit tanpa bayar)
// Jadwal: setiap menit
// ================================================================
class ReleaseExpiredSlots extends Command
{
    protected $signature   = 'slots:release-expired';
    protected $description = 'Rilis slot yang terkunci lebih dari 15 menit tanpa pembayaran';

    public function handle(BookingService $bookingService): void
    {
        $count = $bookingService->releaseExpiredSlots();

        if ($count > 0) {
            $this->info("✓ {$count} slot expired berhasil dirilis.");
        } else {
            $this->line('Tidak ada slot expired saat ini.');
        }
    }
}