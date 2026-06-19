<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index()
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        // Urutan hari untuk sorting custom
        $dayOrder = [
            'Senin'  => 1,
            'Selasa' => 2,
            'Rabu'   => 3,
            'Kamis'  => 4,
            'Jumat'  => 5,
            'Sabtu'  => 6,
            'Minggu' => 7,
        ];

        // Ambil semua slot
        $slots = Availability::where('expert_profile_id', $expert->id)
            ->get()
            ->sortBy(function ($slot) use ($dayOrder) {
                return ($dayOrder[$slot->day_of_week] ?? 8) . '_' . $slot->start_time;
            });

        return view('expert.slots.index', compact('slots'));
    }

    public function store(Request $request)
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        $request->validate([
            'day_of_week' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $day = $request->day_of_week;
        $start = $request->start_time;
        $end = $request->end_time;

        // Pengecekan overlap / tabrakan jadwal
        $overlap = Availability::where('expert_profile_id', $expert->id)
            ->where('day_of_week', $day)
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'Jadwal tabrakan dengan slot yang sudah ada pada hari yang sama.');
        }

        Availability::create([
            'expert_profile_id' => $expert->id,
            'day_of_week'       => $day,
            'start_time'        => $start,
            'end_time'          => $end,
            'status'            => 'available',
            'is_active'         => true,
        ]);

        return redirect()->route('expert.slots.index')->with('success', 'Slot jadwal berhasil ditambahkan.');
    }

    public function destroy(int $id)
    {
        $expert = auth()->user()->expertProfile;

        if (!$expert) {
            return redirect()->route('home')->with('error', 'Profil Expert tidak ditemukan.');
        }

        $slot = Availability::where('expert_profile_id', $expert->id)->findOrFail($id);

        if ($slot->status !== 'available') {
            return back()->with('error', 'Slot yang sudah dikunci atau dipesan tidak dapat dihapus.');
        }

        $slot->delete();

        return redirect()->route('expert.slots.index')->with('success', 'Slot jadwal berhasil dihapus.');
    }
}
