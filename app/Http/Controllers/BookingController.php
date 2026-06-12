<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    // A. Fitur Booking Terjadwal
    public function storeScheduled(Request $request)
    {
        $request->validate([
            'expert_profile_id' => 'required',
            'availability_id'   => 'required',
            'booking_date'      => 'required|date'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Cek slot dan Lock
            $slot = DB::table('availabilities')->where('id', $request->availability_id)->where('status', 'available')->first();
            
            if (!$slot) return back()->with('error', 'Slot sudah tidak tersedia.');

            DB::table('availabilities')->where('id', $request->availability_id)->update(['status' => 'booked']);

            // 2. Simpan Booking
            $expert = DB::table('expert_profiles')->find($request->expert_profile_id);
            $bookingId = DB::table('bookings')->insertGetId([
                'client_id' => Auth::id(),
                'expert_profile_id' => $request->expert_profile_id,
                'availability_id' => $request->availability_id,
                'booking_date' => $request->booking_date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => 'pending',
                'total_price' => $expert->hourly_rate,
            ]);

            return redirect()->route('booking.detail', $bookingId)->with('success', 'Booking berhasil dibuat!');
        });
    }

    // B. Fitur Konsul Langsung (Instant)
    public function storeInstant(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $expert = DB::table('expert_profiles')->where('id', $request->expert_profile_id)->where('is_online', true)->first();
            if (!$expert) return back()->with('error', 'Expert sedang tidak online.');

            // 1. Buat Booking otomatis untuk 1 jam ke depan
            $bookingId = DB::table('bookings')->insertGetId([
                'client_id' => Auth::id(),
                'expert_profile_id' => $expert->id,
                'booking_date' => Carbon::now()->format('Y-m-d'),
                'start_time' => Carbon::now()->format('H:i:s'),
                'end_time' => Carbon::now()->addHour()->format('H:i:s'),
                'status' => 'pending_settlement',
                'total_price' => $expert->hourly_rate,
            ]);

            // 2. Langsung buat sesi konsultasi
            $consultationId = DB::table('consultasions')->insertGetId([
                'booking_id' => $bookingId,
                'type' => 'chat',
                'status' => 'active',
                'started_at' => Carbon::now(),
            ]);

            return redirect()->route('consultation.room', $consultationId)->with('success', 'Konsultasi dimulai!');
        });
    }
}