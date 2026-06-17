@extends('layouts.app')
@section('title', 'Detail Booking')

@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Success banner --}}
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 mb-6 text-center">
        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="font-semibold text-teal-800">Booking Terkonfirmasi!</h2>
        <p class="text-sm text-teal-600 mt-1">
            Invoice: <strong>{{ $booking->payment->invoice ?? '-' }}</strong>
        </p>
    </div>

    {{-- Card detail --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="bg-blue-900 p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                    {{ strtoupper(substr($booking->expertProfile->user->profile->name ?? 'XX', 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-white font-semibold">
                        {{ $booking->expertProfile->user->profile->name ?? 'Konsultan' }}
                    </h2>
                    <p class="text-blue-200 text-sm">{{ $booking->expertProfile->title }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Status</span>
                <span @class([
                    'text-xs font-semibold px-3 py-1 rounded-full',
                    'bg-blue-50 text-blue-700' => $booking->status === 'confirmed',
                    'bg-teal-50 text-teal-700' => $booking->status === 'ongoing',
                    'bg-amber-50 text-amber-700' => $booking->status === 'pending_settlement',
                    'bg-green-50 text-green-700' => $booking->status === 'completed',
                ])>
                    {{ [
                        'confirmed'          => 'Terkonfirmasi — Menunggu Sesi',
                        'ongoing'             => 'Sedang Berlangsung',
                        'pending_settlement'  => 'Menunggu Konfirmasi',
                        'completed'           => 'Selesai',
                    ][$booking->status] ?? $booking->status }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Tanggal & Waktu</span>
                <span class="text-sm font-medium text-slate-800">
                    {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }},
                    {{ substr($booking->start_time, 0, 5) }} WIB
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Total Dibayar</span>
                <span class="text-sm font-semibold text-blue-900">
                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Tombol masuk ruang chat (kalau waktunya sudah tiba) --}}
        <div class="px-6 pb-6">
            @if(in_array($booking->status, ['confirmed', 'ongoing']))
                <button disabled
                        class="w-full py-3 bg-slate-100 text-slate-400 font-semibold rounded-xl text-sm cursor-not-allowed">
                    Ruang Konsultasi (tersedia saat jadwal tiba)
                </button>
                <p class="text-center text-xs text-slate-400 mt-2">Fitur chat akan diaktifkan di step selanjutnya</p>
            @endif
        </div>

    </div>

    <a href="{{ route('client.booking.index') }}"
       class="block text-center mt-6 text-sm text-slate-500 hover:text-blue-900 transition">
        ← Lihat semua booking saya
    </a>

</div>

@endsection