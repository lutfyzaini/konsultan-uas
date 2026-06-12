<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

// ================================================================
// COMMAND 2: Auto-approve settlement 24 jam tanpa komplain
// Jadwal: setiap jam
// ================================================================
class AutoApproveSettlements extends Command
{
    protected $signature   = 'payments:auto-approve';
    protected $description = 'Cairkan dana ke expert untuk sesi yang sudah 24 jam tanpa komplain';

    public function handle(PaymentService $paymentService): void
    {
        $count = $paymentService->autoApproveSettlements();

        if ($count > 0) {
            $this->info("✓ {$count} sesi berhasil di-settle otomatis.");
        } else {
            $this->line('Tidak ada sesi yang perlu di-auto-approve saat ini.');
        }
    }
}