<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    // GET /api/bookings
    public function index()
    {
        $bookings = Booking::with([
            'expertProfile',
            'availability'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    // POST /api/bookings
    public function store(Request $request)
    {
        $request->validate([
            'availability_id' => 'required|exists:availabilities,id',
            'client_id' => 'required|exists:users,id',
        ]);

        try {

            $booking = $this->bookingService->lockSlot(
                $request->availability_id,
                $request->client_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Slot berhasil dikunci.',
                'data' => $booking
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        }
    }

    // GET /api/bookings/{id}
    public function show($id)
    {
        $booking = Booking::with([
            'expertProfile',
            'availability',
            'payment',
            'consultation'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    // POST /api/bookings/{id}/cancel
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        try {

            $this->bookingService->cancelBooking(
                $booking,
                'user_cancelled'
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibatalkan.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        }
    }
}