<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // ================================================================
    // DASHBOARD CLIENT
    //
    // Data yang dikirim ke view:
    //   $cancelledByExpertAbsent  — booking instant yang batal karena expert absen
    //                               (untuk menampilkan komponen alert refund)
    //   $recentBookings           — 5 booking terbaru untuk tabel ringkasan
    // ================================================================

    public function index()
    {
        $userId = Auth::id();

        // ── 1. Ambil booking instant yang dibatalkan karena expert tidak hadir ──
        //
        // Kriteria:
        //   a) Milik client yang sedang login
        //   b) Status booking = 'cancelled' (diset oleh InstantConsultationService)
        //   c) Memiliki konsultasi bertipe 'instant'
        //   d) absence_resolved_at ada (artinya sudah diproses oleh cron)
        //   e) Dibatalkan dalam 24 jam terakhir (agar alert tidak abadi)
        //
        // Catatan: whereHas memastikan hanya booking yang benar-benar
        // karena expert absen (bukan alasan pembatalan lain).
        $cancelledByExpertAbsent = Booking::where('client_id', $userId)
            ->where('status', 'cancelled')
            ->where('cancel_reason', 'expert_no_show')
            ->where('updated_at', '>=', now()->subHours(24))
            // Eager load relasi yang dibutuhkan komponen alert
            ->with([
                'expertProfile.user.profile',
                'expertProfile.category',
                'consultation',
            ])
            ->latest('updated_at')
            ->get();

        // ── 2. Ambil 5 booking terbaru (semua tipe) untuk tabel ringkasan ──
        $recentBookings = Booking::where('client_id', $userId)
            ->with(['expertProfile.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('client.dashboard', compact(
            'cancelledByExpertAbsent',
            'recentBookings',
        ));
    }
}
