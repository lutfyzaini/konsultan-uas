<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class CheckInstantConsultations extends Command
{
    protected $signature   = 'instant:check-attendance';
    protected $description = 'Cek kehadiran sesi instant yang sudah melewati batas 10 menit';

    public function handle(
        PaymentService $paymentService,
    ): void {
        // Cek via PaymentService (pakai Booking model)
        $noShowResult = $paymentService->processNoShows();

        $totalProcessed = $noShowResult['client_no_show'] + $noShowResult['expert_no_show'];

        if ($totalProcessed > 0) {
            $this->info("✓ Diproses: {$noShowResult['client_no_show']} client no-show, {$noShowResult['expert_no_show']} expert no-show");
        } else {
            $this->line('Tidak ada sesi instant yang perlu diproses.');
        }
    }
}