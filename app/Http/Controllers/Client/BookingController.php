<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

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
            ->where('booking_type', 'scheduled')
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
}
