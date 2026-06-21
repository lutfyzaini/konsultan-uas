<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class SlotApiController extends Controller
{
    public function index()
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Expert tidak ditemukan.'
            ], 404);
        }

        $slots = Availability::where('expert_profile_id', $expert->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slots
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'expert_profile_id' => 'required|exists:expert_profiles,id',
            'day_of_week'       => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
        ]);

        $overlap = Availability::where('expert_profile_id', $request->expert_profile_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
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
            'day_of_week'       => $request->day_of_week,
            'start_time'        => $request->start_time,
            'end_time'          => $request->end_time,
            'status'            => 'available',
            'is_active'         => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil ditambahkan.',
            'data'    => $slot
        ], 201);
    }
    public function destroy($id)
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return response()->json([
                'success' => false,
                'message' => 'Profil Expert tidak ditemukan.'
            ], 404);
        }

        $slot = Availability::where('expert_profile_id', $expert->id)
            ->findOrFail($id);

        if ($slot->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Slot yang sudah dipesan tidak dapat dihapus.'
            ], 422);
        }

        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil dihapus.'
        ]);
    }
}