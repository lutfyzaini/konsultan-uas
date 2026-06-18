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
    $request->validate([
        'expert_profile_id' => 'required'
    ]);

    return DB::transaction(function () use ($request) {
        // 1. Pastikan Expert benar-benar online
        $expert = DB::table('expert_profiles')
            ->where('id', $request->expert_profile_id)
            ->where('is_online', true)
            ->first();

        if (!$expert) {
            return back()->with('error', 'Maaf, Expert sedang tidak online atau baru saja offline.');
        }

        $clientId = Auth::id();
        $wallet = DB::table('wallets')->where('user_id', $clientId)->first();

        // 2. Proteksi Saldo: Cek apakah uang client cukup
        if (!$wallet || $wallet->balance < $expert->hourly_rate) {
            return back()->with('error', 'Saldo dompet digital Anda tidak cukup untuk melakukan konsultasi langsung.');
        }

        $balanceBefore = $wallet->balance;
        $balanceAfter = $balanceBefore - $expert->hourly_rate;

        // 3. Potong Saldo Client (Instant Payment)
        DB::table('wallets')->where('user_id', $clientId)->update([
            'balance' => $balanceAfter,
            'updated_at' => Carbon::now()
        ]);

        // 4. Buat Booking dengan status 'ongoing', type 'instant', dan gembok hadir 10 menit
        $bookingId = DB::table('bookings')->insertGetId([
            'client_id' => $clientId,
            'expert_profile_id' => $expert->id,
            'availability_id' => null, // NULL karena walk-in langsung, bukan lewat kalender
            'booking_date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->format('H:i:s'),
            'end_time' => Carbon::now()->addHour()->format('H:i:s'),
            'status' => 'ongoing',
            'total_price' => $expert->hourly_rate,
            'booking_type' => 'instant',
            'attendance_deadline' => Carbon::now()->addMinutes(10), // Tenggat hadir 10 menit!
            'client_joined' => true, // Karena client yang bayar, otomatis dia dianggap sudah stand-by
            'expert_joined' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // 5. Catat ke Histori Transaksi Wallet Client
        DB::table('wallet_transactions')->insert([
            'wallet_id' => $wallet->id,
            'booking_id' => $bookingId,
            'type' => 'debit',
            'amount' => $expert->hourly_rate,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => 'Pembayaran Konsul Instan dengan Expert ID #' . $expert->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // 6. Buat Sesi Konsultasi Aktif di Ruang Chat
        $consultationId = DB::table('consultations')->insertGetId([
            'booking_id' => $bookingId,
            'type' => 'chat',
            'status' => 'active', // Sesuai enum 'active' di migrasimu
            'started_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
return redirect()->route('client.instant.room', $consultationId)
                 ->with('success', 'Konsultasi langsung dimulai! Mohon tunggu kehadiran Expert.');
    });
}

public function room($id)
{
    // Ambil data konsultasi beserta booking-nya
    $consultation = DB::table('consultations')
        ->join('bookings', 'consultations.booking_id', '=', 'bookings.id')
        ->where('consultations.id', $id)
        ->select('consultations.*', 'bookings.expert_profile_id', 'bookings.client_id', 'bookings.attendance_deadline', 'bookings.status as booking_status')
        ->first();

    if (!$consultation) {
        abort(404, 'Sesi konsultasi tidak ditemukan.');
    }

    // Ambil data profil expert untuk ditampilkan namanya
    $expert = DB::table('expert_profiles')
        ->join('user_profiles', 'expert_profiles.user_id', '=', 'user_profiles.user_id')
        ->where('expert_profiles.id', $consultation->expert_profile_id)
        ->select('user_profiles.name', 'expert_profiles.category_id')
        ->first();

    // Jika status booking tiba-tiba berubah jadi cancelled atau completed (dipicu oleh cron job), tendang ke halaman hasil
    if (in_array($consultation->booking_status, ['cancelled', 'completed'])) {
        return redirect()->route('client.instant.result', $id);
    }

    // Ambil histori chat sederhana
    $messages = DB::table('chat_message')
        ->where('consultation_id', $id)
        ->orderBy('created_at', 'asc')
        ->get();

    return view('client.experts.room', compact('consultation', 'expert', 'messages'));
}

// ── JALUR HALAMAN HASIL KONSULTASI (SUKSES / DIBATALKAN EXPIRED) ──
public function result($id)
{
    $consultation = DB::table('consultations')
        ->join('bookings', 'consultations.booking_id', '=', 'bookings.id')
        ->where('consultations.id', $id)
        ->select('bookings.*', 'consultations.status as cons_status')
        ->first();

    return view('client.experts.result', compact('consultation'));
}
}