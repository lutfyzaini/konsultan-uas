<?php

use Illuminate\Support\Facades\Schedule;

// ================================================================
// JADWAL CRON JOB
// Jalankan: php artisan schedule:work  (untuk development)
//           php artisan schedule:run   (dipanggil oleh crontab server)
//
// Setup di server (cPanel/VPS), tambahkan ke crontab:
// * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
// ================================================================

// Setiap menit: rilis slot yang expired (lock > 15 menit tanpa bayar)
Schedule::command('slots:release-expired')->everyMinute();

// Setiap jam: cairkan dana sesi yang sudah 24 jam tanpa komplain
Schedule::command('payments:auto-approve')->hourly();