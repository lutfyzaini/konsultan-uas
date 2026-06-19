<?php

namespace App\Console\Commands;

use App\Services\InstantConsultationService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class CheckInstantConsultations extends Command
{
    protected $signature   = 'instant:check-attendance';
    protected $description = 'Cek kehadiran sesi instant yang sudah melewati batas 10 menit';

    public function handle(
        InstantConsultationService $instantService,
        PaymentService $paymentService,
    ): void {
        // 1. Cek via InstantConsultationService (pakai Consultation model)
        $instantResult = $instantService->checkAndResolveExpiredSessions();

        // 2. Cek via PaymentService (pakai Booking model — fallback)
        $noShowResult = $paymentService->processNoShows();

        $totalProcessed = $instantResult['settled'] + $instantResult['refunded']
                        + $noShowResult['client_no_show'] + $noShowResult['expert_no_show'];

        if ($totalProcessed > 0) {
            $this->info("✓ Diproses: {$instantResult['settled']} settled, {$instantResult['refunded']} refunded, " .
                "{$noShowResult['client_no_show']} client no-show, {$noShowResult['expert_no_show']} expert no-show");
        } else {
            $this->line('Tidak ada sesi instant yang perlu diproses.');
        }

        if ($instantResult['errors'] > 0) {
            $this->warn("⚠ {$instantResult['errors']} sesi gagal diproses. Cek log.");
        }
    }
}