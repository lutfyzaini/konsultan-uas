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
    // PROSES LOCK SLOT
    // Dipanggil saat client klik "Booking Sekarang" di halaman detail expert
    // POST /client/booking
    // ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'availability_id' => 'required|integer|exists:availabilities,id',
        ], [
            'availability_id.required' => 'Pilih slot jadwal terlebih dahulu.',
            'availability_id.exists'   => 'Slot tidak ditemukan.',
        ]);

        try {
            $booking = $this->bookingService->lockSlot(
                $request->availability_id,
                auth()->id()
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
    // Menampilkan countdown timer + tombol bayar
    // GET /client/booking/{id}/payment
    // ──────────────────────────────────────────────
    public function payment(int $id)
    {
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category', 'availability'])
            ->where('client_id', auth()->id())
            ->findOrFail($id);

        // kalau sudah dibayar / expired, redirect ke halaman yang sesuai
        if ($booking->status === 'confirmed') {
            return redirect()->route('client.booking.show', $booking->id)
                ->with('success', 'Booking ini sudah dibayar.');
        }

        if ($booking->status === 'cancelled') {
            return redirect()->route('experts.index')
                ->with('error', 'Booking ini sudah dibatalkan / waktu pembayaran habis.');
        }

        // hitung sisa waktu dalam detik (untuk countdown JS) — dibulatkan jadi integer
        $secondsRemaining = max(0, (int) now()->diffInSeconds($booking->payment_deadline, false));

        return view('client.booking.payment', compact('booking', 'secondsRemaining'));
    }

    // ──────────────────────────────────────────────
    // PROSES PEMBAYARAN
    // POST /client/booking/{id}/pay
    // ──────────────────────────────────────────────
    public function pay(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())->findOrFail($id);

        try {
            $this->paymentService->processPayment($booking);

            return redirect()
                ->route('client.booking.show', $booking->id)
                ->with('success', 'Pembayaran berhasil! Booking kamu sudah dikonfirmasi.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // DETAIL BOOKING (setelah berhasil dibayar)
    // GET /client/booking/{id}
    // ──────────────────────────────────────────────
    public function show(int $id)
    {
        $booking = Booking::with([
            'expertProfile.user.profile',
            'expertProfile.category',
            'availability',
            'payment',
        ])
        ->where('client_id', auth()->id())
        ->findOrFail($id);

        return view('client.booking.show', compact('booking'));
    }

    // ──────────────────────────────────────────────
    // RIWAYAT BOOKING
    // GET /client/booking
    // ──────────────────────────────────────────────
    public function index()
    {
        $bookings = Booking::with(['expertProfile.user.profile', 'expertProfile.category'])
            ->where('client_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('client.booking.index', compact('bookings'));
    }

    // ──────────────────────────────────────────────
    // BATALKAN BOOKING MANUAL (sebelum bayar)
    // POST /client/booking/{id}/cancel
    // ──────────────────────────────────────────────
    public function cancel(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())->findOrFail($id);

        if ($booking->status !== 'pending_payment') {
            return back()->with('error', 'Booking ini tidak bisa dibatalkan.');
        }

        $this->bookingService->cancelBooking($booking, 'cancelled_by_client');

        return redirect()->route('experts.index')
            ->with('success', 'Booking berhasil dibatalkan.');
    }
}