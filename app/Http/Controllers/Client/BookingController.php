<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PaymentService $paymentService,
    ) {}

    // ──────────────────────────────────────────────
    // DAFTAR BOOKING CLIENT
    // GET /client/booking
    // ──────────────────────────────────────────────
    public function index()
    {
        $bookings = Booking::where('client_id', auth()->id())
            ->with(['expertProfile.user.profile', 'expertProfile.category'])
            ->latest()
            ->paginate(10);

        return view('client.booking.index', compact('bookings'));
    }

    // ──────────────────────────────────────────────
    // BUAT BOOKING TERJADWAL (lock slot)
    // POST /client/booking
    // ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'availability_id' => 'required|integer|exists:availabilities,id',
        ]);

        try {
            $booking = $this->bookingService->lockSlot(
                $request->availability_id,
                auth()->id(),
            );

            return redirect()
                ->route('client.booking.payment', $booking->id)
                ->with('success', 'Slot berhasil dikunci! Selesaikan pembayaran dalam 15 menit.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // HALAMAN PEMBAYARAN
    // GET /client/booking/{id}/payment
    // ──────────────────────────────────────────────
    public function payment(int $id)
    {
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category', 'availability'])
            ->where('client_id', auth()->id())
            ->where('booking_type', 'scheduled')
            ->findOrFail($id);

        if ($booking->status !== 'pending_payment') {
            return redirect()->route('client.booking.show', $booking->id);
        }

        $secondsRemaining = max(0, (int) now()->diffInSeconds($booking->payment_deadline, false));

        return view('client.booking.payment', compact('booking', 'secondsRemaining'));
    }

    // ──────────────────────────────────────────────
    // PROSES BAYAR
    // POST /client/booking/{id}/pay
    // ──────────────────────────────────────────────
    public function pay(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->where('booking_type', 'scheduled')
            ->findOrFail($id);

        try {
            $this->paymentService->processPayment($booking);

            return redirect()
                ->route('client.booking.show', $booking->id)
                ->with('success', 'Pembayaran berhasil! Sesi dijadwalkan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // BATALKAN BOOKING
    // POST /client/booking/{id}/cancel
    // ──────────────────────────────────────────────
    public function cancel(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->findOrFail($id);

        if ($booking->booking_type === 'instant') {
            return back()->with('error', 'Pesanan instan tidak bisa dibatalkan secara manual.');
        }

        if (! in_array($booking->status, ['pending_payment', 'confirmed'])) {
            return back()->with('error', 'Booking ini tidak bisa dibatalkan.');
        }

        try {
            $this->bookingService->cancelBooking($booking, 'user_cancelled');

            return redirect()
                ->route('client.booking.index')
                ->with('success', 'Booking berhasil dibatalkan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // DETAIL BOOKING
    // GET /client/booking/{id}
    // ──────────────────────────────────────────────
    public function show(int $id)
    {
        $booking = Booking::with([
            'expertProfile.user.profile',
            'expertProfile.category',
            'availability',
            'payment',
            'consultation',
            'review',
        ])
        ->where('client_id', auth()->id())
        ->findOrFail($id);

        return view('client.booking.show', compact('booking'));
    }

    // ──────────────────────────────────────────────
    // RUANG CHAT KONSULTASI TERJADWAL
    // GET /client/booking/{id}/room
    // ──────────────────────────────────────────────
    public function room(int $id)
    {
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category', 'consultation'])
            ->where('client_id', auth()->id())
            ->where('booking_type', 'scheduled')
            ->findOrFail($id);

        // Blokir jika waktu sesi belum tiba
        $sessionStart = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
        if (now()->isBefore($sessionStart)) {
            return redirect()->route('client.booking.show', $booking->id)
                ->with('error', 'Ruang chat belum dibuka. Sesi baru bisa diakses pada ' . $sessionStart->format('d M Y, H:i') . ' WIB.');
        }

        // Jika status masih confirmed, mulai sesi saat client/expert masuk
        if ($booking->status === 'confirmed') {
            try {
                $this->bookingService->startSession($booking);
                $booking->refresh();
            } catch (\Exception $e) {
                return redirect()->route('client.booking.show', $booking->id)->with('error', $e->getMessage());
            }
        }

        // Tandai client hadir
        $this->bookingService->markAttendance($booking, 'client');
        $booking->refresh();

        if ($booking->status === 'cancelled') {
            return redirect()->route('client.booking.show', $booking->id)
                ->with('error', 'Sesi konsultasi terjadwal ini telah dibatalkan.');
        }

        $secondsRemaining = $booking->attendance_deadline
            ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
            : null;

        $messages = $booking->consultation
            ? \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->orderBy('created_at', 'asc')
                ->get()
            : collect();

        return view('client.booking.room', compact('booking', 'secondsRemaining', 'messages'));
    }

    // ──────────────────────────────────────────────
    // KIRIM PESAN CHAT TERJADWAL
    // POST /client/booking/{id}/message
    // ──────────────────────────────────────────────
    public function sendMessage(Request $request, int $id)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $booking = Booking::with('consultation')
            ->where('client_id', auth()->id())
            ->where('booking_type', 'scheduled')
            ->where('status', 'ongoing')
            ->findOrFail($id);

        if (!$booking->consultation) {
            return response()->json(['error' => 'Sesi aktif tidak ditemukan.'], 404);
        }

        $msgId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId([
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
    // POLLING STATUS TERJADWAL
    // GET /client/booking/{id}/status
    // ──────────────────────────────────────────────
    public function checkStatus(Request $request, int $id)
    {
        $booking = Booking::with('consultation')
            ->where('client_id', auth()->id())
            ->findOrFail($id);

        $this->bookingService->checkAndAutoEndSession($booking);
        $booking->refresh();

        $lastId = (int) $request->query('last_id', 0);
        $newMessages = [];

        if ($booking->consultation) {
            $newMessages = \Illuminate\Support\Facades\DB::table('chat_messages')
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
            'expert_joined'      => (bool) $booking->expert_joined,
            'seconds_remaining'  => $booking->attendance_deadline
                ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
                : null,
            'redirect_to_result' => $booking->status === 'cancelled',
            'new_messages'       => $newMessages,
        ]);
    }

    public function storeReview(Request $request, int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->findOrFail($id);

        if (!in_array($booking->status, ['completed', 'pending_settlement'])) {
            return back()->with('error', 'Ulasan hanya dapat diberikan jika sesi konsultasi telah selesai.');
        }

        // Cek ulasan sudah ada
        if ($booking->review) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk booking ini.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($booking, $request) {
                // Jika status masih pending_settlement, cairkan dana terlebih dahulu
                if ($booking->status === 'pending_settlement') {
                    $this->paymentService->settlePayment($booking);
                }

                $booking->review()->create([
                    'client_id' => auth()->id(),
                    'expert_profile_id' => $booking->expert_profile_id,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);
            });

            return back()->with('success', 'Terima kasih atas ulasan Anda! Sesi konsultasi telah diselesaikan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveSettlement(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->where('status', 'pending_settlement')
            ->findOrFail($id);

        try {
            $this->paymentService->settlePayment($booking);
            return back()->with('success', 'Konsultasi berhasil diselesaikan. Dana telah dicairkan ke pakar.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // AKHIRI SESI MANUALLY BY CLIENT
    // POST /client/booking/{id}/end
    // ──────────────────────────────────────────────
    public function endSession(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->where('status', 'ongoing')
            ->findOrFail($id);

        try {
            $this->bookingService->endSession($booking);
            return redirect()->route('client.booking.show', $booking->id)
                ->with('success', 'Sesi konsultasi telah diakhiri. Silakan berikan ulasan Anda.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // DOWNLOAD RESUME (PDF)
    // GET /client/booking/{id}/pdf
    // ──────────────────────────────────────────────
    public function downloadPdf(int $id)
    {
        $booking = Booking::with([
            'expertProfile.user.profile',
            'expertProfile.category',
            'client.profile',
            'consultation.chatMessages.sender'
        ])
        ->where('client_id', auth()->id())
        ->whereIn('status', ['completed', 'pending_settlement'])
        ->findOrFail($id);

        $pdf = Pdf::loadView('client.booking.pdf', compact('booking'));
        return $pdf->download("resume-konsultasi-{$booking->id}.pdf");
    }
}

