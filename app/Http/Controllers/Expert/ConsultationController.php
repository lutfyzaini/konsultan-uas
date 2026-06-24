<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    // ──────────────────────────────────────────────
    // RUANG CHAT CONSULTATION (SISI EXPERT)
    // GET /expert/consultation/{id}/room
    // ──────────────────────────────────────────────
    public function room(int $id)
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        $booking = Booking::with(['client.profile', 'consultation'])
            ->where('expert_profile_id', $expert->id)
            ->findOrFail($id);

        // Blokir jika waktu sesi terjadwal belum tiba
        if ($booking->booking_type === 'scheduled') {
            $sessionStart = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
            if (now()->isBefore($sessionStart)) {
                return redirect()->route('expert.dashboard')
                    ->with('error', 'Ruang chat belum dibuka. Sesi baru bisa diakses pada ' . $sessionStart->format('d M Y, H:i') . ' WIB.');
            }
        }

        // Jika booking masih terkonfirmasi (belum ongoing), mulai sesi
        if ($booking->status === 'confirmed') {
            try {
                $this->bookingService->startSession($booking);
                $booking->refresh();
            } catch (\Exception $e) {
                return redirect()->route('expert.dashboard')->with('error', $e->getMessage());
            }
        }

        // Tandai expert sudah hadir
        $this->bookingService->markAttendance($booking, 'expert');
        $booking->refresh();

        // Jika dibatalkan, kembalikan ke dasbor dengan info
        if ($booking->status === 'cancelled') {
            return redirect()->route('expert.dashboard')
                ->with('error', 'Sesi konsultasi ini telah dibatalkan.');
        }

        $secondsRemaining = $booking->attendance_deadline
            ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
            : null;

        $messages = $booking->consultation
            ? DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->orderBy('created_at', 'asc')
                ->get()
            : collect();

        return view('expert.consultation.room', compact('booking', 'secondsRemaining', 'messages'));
    }

    // ──────────────────────────────────────────────
    // KIRIM PESAN CHAT
    // POST /expert/consultation/{id}/message
    // ──────────────────────────────────────────────
    public function sendMessage(Request $request, int $id)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $expert = auth()->user()->expertProfile;
        $booking = Booking::with('consultation')
            ->where('expert_profile_id', $expert->id)
            ->where('status', 'ongoing')
            ->findOrFail($id);

        if (!$booking->consultation) {
            return response()->json(['error' => 'Sesi aktif tidak ditemukan.'], 404);
        }

        $msgId = DB::table('chat_messages')->insertGetId([
            'consultation_id' => $booking->consultation->id,
            'sender_id'       => auth()->id(),
            'message'         => $request->message,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'id'         => $msgId,
            'sender_id'  => auth()->id(),
            'message'    => $request->message,
            'created_at' => now()->format('H:i'),
            'is_own'     => true,
        ]);
    }

    // ──────────────────────────────────────────────
    // AJAX POLLING
    // GET /expert/consultation/{id}/status
    // ──────────────────────────────────────────────
    public function checkStatus(Request $request, int $id)
    {
        $expert = auth()->user()->expertProfile;
        $booking = Booking::with('consultation')
            ->where('expert_profile_id', $expert->id)
            ->findOrFail($id);

        $this->bookingService->checkAndAutoEndSession($booking);
        $booking->refresh();

        $lastId = (int) $request->query('last_id', 0);
        $newMessages = [];

        if ($booking->consultation) {
            $newMessages = DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->where('id', '>', $lastId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id'        => $m->id,
                    'sender_id' => $m->sender_id,
                    'message'   => $m->message,
                    'time'      => \Carbon\Carbon::parse($m->created_at)->format('H:i'),
                    'is_own'    => $m->sender_id === auth()->id(),
                ])
                ->values();
        }

        return response()->json([
            'status'             => $booking->status,
            'client_joined'      => (bool) $booking->client_joined,
            'seconds_remaining'  => $booking->attendance_deadline
                ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
                : null,
            'redirect_to_result' => $booking->status === 'cancelled',
            'new_messages'       => $newMessages,
        ]);
    }

    // ──────────────────────────────────────────────
    // AKHIRI SESI MANUALLY BY EXPERT
    // POST /expert/consultation/{id}/end
    // ──────────────────────────────────────────────
    public function endSession(int $id)
    {
        $expert = auth()->user()->expertProfile;
        $booking = Booking::where('expert_profile_id', $expert->id)
            ->where('status', 'ongoing')
            ->findOrFail($id);

        try {
            $this->bookingService->endSession($booking);
            return redirect()->route('expert.dashboard')->with('success', 'Sesi konsultasi telah Anda akhiri. Menunggu settlement/konfirmasi dana.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
