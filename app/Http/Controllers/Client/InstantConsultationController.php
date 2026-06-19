<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstantConsultationController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PaymentService $paymentService,
    ) {}

    // ──────────────────────────────────────────────
    // BUAT SESI INSTANT
    // Dipanggil dari halaman detail expert, tombol "Konsultasi Sekarang"
    // POST /client/instant/{expertId}
    // ──────────────────────────────────────────────
    public function create(int $expertId)
    {
        try {
            $booking = $this->bookingService->createInstantBooking($expertId, auth()->id());

            return redirect()
                ->route('client.instant.payment', $booking->id)
                ->with('success', 'Selesaikan pembayaran untuk langsung mulai konsultasi.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // HALAMAN PEMBAYARAN INSTANT (mirip payment biasa tapi tanpa pilih slot)
    // GET /client/instant/{id}/payment
    // ──────────────────────────────────────────────
    public function payment(int $id)
    {
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category'])
            ->where('client_id', auth()->id())
            ->where('booking_type', 'instant')
            ->findOrFail($id);

        if ($booking->status === 'ongoing' || $booking->status === 'confirmed') {
            return redirect()->route('client.instant.room', $booking->id);
        }

        if ($booking->status === 'cancelled') {
            return redirect()->route('experts.index')
                ->with('error', 'Sesi ini sudah dibatalkan / waktu pembayaran habis.');
        }

        $secondsRemaining = max(0, (int) now()->diffInSeconds($booking->payment_deadline, false));

        return view('client.instant.payment', compact('booking', 'secondsRemaining'));
    }

    // ──────────────────────────────────────────────
    // PROSES BAYAR → LANGSUNG MULAI SESI
    // POST /client/instant/{id}/pay
    // ──────────────────────────────────────────────
    public function pay(int $id)
    {
        $booking = Booking::where('client_id', auth()->id())
            ->where('booking_type', 'instant')
            ->findOrFail($id);

        try {
            $this->paymentService->processPayment($booking);

            // langsung mulai sesi setelah bayar (beda dari booking terjadwal)
            $this->bookingService->startInstantSession($booking->fresh());

            return redirect()
                ->route('client.instant.room', $booking->id)
                ->with('success', 'Pembayaran berhasil! Kamu masuk ke ruang konsultasi.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // RUANG TUNGGU / KONSULTASI INSTANT
    // GET /client/instant/{id}/room
    // ──────────────────────────────────────────────
    public function room(int $id)
    {
        // Ambil booking + relasi profil expert + sesi konsultasi
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category', 'consultation'])
            ->where('client_id', auth()->id())
            ->where('booking_type', 'instant')
            ->findOrFail($id);

        // Tandai client sudah hadir di ruang chat
        $this->bookingService->markAttendance($booking, 'client');

        // Jika booking sudah selesai/dibatalkan (oleh cron), tendang ke halaman hasil
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return redirect()->route('client.instant.result', $booking->id);
        }

        $booking->refresh();

        // Hitung sisa detik menuju attendance_deadline untuk countdown JS
        $secondsRemaining = $booking->attendance_deadline
            ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
            : null;

        // Ambil histori pesan dari sesi ini (urutan kronologis)
        $messages = $booking->consultation
            ? DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->orderBy('created_at', 'asc')
                ->get()
            : collect();

        return view('client.instant.room', compact('booking', 'secondsRemaining', 'messages'));
    }

    // ──────────────────────────────────────────────
    // KIRIM PESAN CHAT
    // POST /client/instant/{id}/message
    // Dipanggil via AJAX dari halaman room
    // ──────────────────────────────────────────────
    public function sendMessage(Request $request, int $id)
    {
        // Validasi input teks pesan
        $request->validate(['message' => 'required|string|max:1000']);

        // Pastikan booking milik client yang login dan statusnya aktif
        $booking = Booking::with('consultation')
            ->where('client_id', auth()->id())
            ->where('booking_type', 'instant')
            ->where('status', 'ongoing')
            ->findOrFail($id);

        if (! $booking->consultation) {
            return response()->json(['error' => 'Sesi konsultasi tidak ditemukan.'], 404);
        }

        // Simpan pesan ke tabel chat_messages
        $msg = DB::table('chat_messages')->insertGetId([
            'consultation_id' => $booking->consultation->id,
            'sender_id'       => auth()->id(),
            'message'         => $request->message,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Kembalikan data pesan yang baru disimpan untuk di-render di frontend
        return response()->json([
            'id'         => $msg,
            'sender_id'  => auth()->id(),
            'message'    => $request->message,
            'created_at' => now()->format('H:i'),
            'is_own'     => true,
        ]);
    }

    // ──────────────────────────────────────────────
    // HALAMAN HASIL — setelah no-show atau sesi selesai normal
    // GET /client/instant/{id}/result
    // ──────────────────────────────────────────────
    public function result(int $id)
    {
        $booking = Booking::with(['expertProfile.user.profile', 'expertProfile.category'])
            ->where('client_id', auth()->id())
            ->findOrFail($id);

        // rekomendasi expert lain di kategori yang sama (kalau expert no-show)
        $alternativeExperts = [];
        if ($booking->cancel_reason === 'expert_no_show') {
            $alternativeExperts = ExpertProfile::with('user.profile')
                ->where('category_id', $booking->expertProfile->category_id)
                ->where('id', '!=', $booking->expert_profile_id)
                ->where('verification_status', 'approved')
                ->where('is_online', true)
                ->orderByDesc('average_rating')
                ->limit(3)
                ->get();
        }

        return view('client.instant.result', compact('booking', 'alternativeExperts'));
    }

    // ──────────────────────────────────────────────
    // API: CEK STATUS + PESAN BARU (AJAX polling 4 detik)
    // GET /client/instant/{id}/status?last_id={lastMessageId}
    // ──────────────────────────────────────────────
    public function checkStatus(Request $request, int $id)
    {
        $booking = Booking::with('consultation')
            ->where('client_id', auth()->id())
            ->findOrFail($id);

        // Ambil pesan baru sejak last_id terakhir yang diterima client
        $lastId   = (int) $request->query('last_id', 0);
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
            'status'              => $booking->status,
            'expert_joined'       => (bool) $booking->expert_joined,
            'seconds_remaining'   => $booking->attendance_deadline
                ? max(0, (int) now()->diffInSeconds($booking->attendance_deadline, false))
                : null,
            // true = arahkan browser ke halaman result
            'redirect_to_result'  => in_array($booking->status, ['cancelled', 'completed']),
            'new_messages'        => $newMessages,
        ]);
    }
}