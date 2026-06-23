<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class SlotApiController extends Controller
{
    // GET /api/slots
    public function index()
    {
        $slots = Availability::orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slots
        ]);
    }

    // POST /api/slots
    public function store(Request $request)
    {
        $request->validate([
            'expert_profile_id' => 'required|exists:expert_profiles,id',
            'day_of_week'       => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
        ]);

        $day = $request->day_of_week;
        $start = $request->start_time;
        $end = $request->end_time;

        $overlap = Availability::where('expert_profile_id', $request->expert_profile_id)
            ->where('day_of_week', $day)
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tabrakan dengan slot yang sudah ada.'
            ], 422);
        }

        $slot = Availability::create([
            'expert_profile_id' => $request->expert_profile_id,
            'day_of_week'       => $day,
            'start_time'        => $start,
            'end_time'          => $end,
            'status'            => 'available',
            'is_active'         => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil ditambahkan.',
            'data' => $slot
        ], 201);
    }

    // DELETE /api/slots/{id}
    public function destroy($id)
    {
        $slot = Availability::findOrFail($id);

        if ($slot->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Slot yang sudah dikunci atau dipesan tidak dapat dihapus.'
            ], 422);
        }

        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil dihapus.'
        ]);
    }
}