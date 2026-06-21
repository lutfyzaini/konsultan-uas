<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationApiController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    // GET /api/consultations/{id}
    public function show($id)
    {
        $booking = Booking::with([
            'consultation',
            'client',
            'expertProfile'
        ])->findOrFail($id);

        $messages = [];

        if ($booking->consultation) {
            $messages = DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->orderBy('created_at')
                ->get();
        }

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'messages' => $messages
        ]);
    }

    // POST /api/consultations/{id}/message
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'sender_id' => 'required|integer',
            'message' => 'required|string|max:1000'
        ]);

        $booking = Booking::with('consultation')
            ->findOrFail($id);

        if (!$booking->consultation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation tidak ditemukan'
            ], 404);
        }

        $msgId = DB::table('chat_messages')->insertGetId([
            'consultation_id' => $booking->consultation->id,
            'sender_id'       => $request->sender_id,
            'message'         => $request->message,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message_id' => $msgId,
            'message' => 'Pesan berhasil dikirim'
        ]);
    }

    // GET /api/consultations/{id}/status
    public function status(Request $request, $id)
    {
        $booking = Booking::with('consultation')
            ->findOrFail($id);

        $lastId = (int) $request->query('last_id', 0);

        $messages = [];

        if ($booking->consultation) {
            $messages = DB::table('chat_messages')
                ->where('consultation_id', $booking->consultation->id)
                ->where('id', '>', $lastId)
                ->orderBy('created_at')
                ->get();
        }

        return response()->json([
            'success' => true,
            'status' => $booking->status,
            'new_messages' => $messages
        ]);
    }

    // POST /api/consultations/{id}/end
    public function endSession($id)
    {
        $booking = Booking::findOrFail($id);

        try {

            $this->bookingService->endSession($booking);

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil diakhiri'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        }
    }
}