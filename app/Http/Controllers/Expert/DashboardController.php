<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Wallet;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $expert = $user->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        // Ambil data wallet expert
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
        );

        // Ambil booking terkait expert ini
        $bookings = Booking::where('expert_profile_id', $expert->id)
            ->with(['client.profile'])
            ->latest()
            ->get();

        // Pisahkan kategori booking
        $activeSessions = $bookings->whereIn('status', ['confirmed', 'ongoing']);
        $completedSessions = $bookings->where('status', 'completed');
        $cancelledSessions = $bookings->where('status', 'cancelled');

        return view('expert.dashboard', compact(
            'expert',
            'wallet',
            'bookings',
            'activeSessions',
            'completedSessions',
            'cancelledSessions'
        ));
    }

    public function toggleOnline(Request $request)
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return response()->json(['success' => false, 'message' => 'Profil Expert tidak ditemukan.'], 404);
        }

        if ($expert->verification_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Akun kamu belum disetujui oleh admin. Tidak dapat mengubah status online.'
            ], 403);
        }

        $expert->update([
            'is_online' => !$expert->is_online
        ]);

        return response()->json([
            'success' => true,
            'is_online' => (bool) $expert->is_online,
            'message' => $expert->is_online ? 'Kamu sekarang Online' : 'Kamu sekarang Offline'
        ]);
    }
}
