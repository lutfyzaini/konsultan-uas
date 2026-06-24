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
                        'pending_settlement'  => 'Selesai',
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

        {{-- Tombol masuk ruang chat & PDF --}}
        @php
            $sessionStart = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
            $isTimeYet = now()->greaterThanOrEqualTo($sessionStart);
            $roomRoute = $booking->booking_type === 'instant' 
                ? route('client.instant.room', $booking->id) 
                : route('client.booking.room', $booking->id);
        @endphp
        <div class="px-6 pb-6 space-y-3">
            @if(in_array($booking->status, ['confirmed', 'ongoing']))
                @if($isTimeYet || $booking->booking_type === 'instant')
                    <a href="{{ $roomRoute }}"
                       class="block text-center w-full py-3 bg-blue-900 hover:bg-indigo-900 text-white font-semibold rounded-xl text-sm shadow-sm transition-all">
                        Masuk Ruang Konsultasi
                    </a>
                @else
                    <button disabled
                            class="w-full py-3 bg-slate-100 text-slate-400 font-semibold rounded-xl text-sm cursor-not-allowed"
                            title="Tersedia pada jam {{ $sessionStart->format('H:i') }} WIB">
                        Ruang Chat Belum Dibuka (Tersedia pukul {{ $sessionStart->format('H:i') }} WIB)
                    </button>
                @endif
            @elseif(in_array($booking->status, ['completed', 'pending_settlement']))
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ $roomRoute }}"
                       class="block text-center w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-all">
                        Lihat Riwayat Chat
                    </a>
                    <a href="{{ route('client.booking.pdf', $booking->id) }}"
                       class="block text-center w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl text-sm shadow-sm transition-all">
                        Download Resume (PDF)
                    </a>
                </div>
            @endif
        </div>

        {{-- Section Ulasan --}}
        @if($booking->status === 'completed')
            <div class="border-t border-slate-100 p-6 bg-slate-50">
                @if($booking->review)
                    <h3 class="font-semibold text-slate-800 text-sm mb-3">Ulasan Anda</h3>
                    <div class="flex items-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $booking->review->rating ? 'text-amber-400' : 'text-slate-200' }} fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    @if($booking->review->comment)
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $booking->review->comment }}</p>
                    @endif
                @else
                    <h3 class="font-semibold text-slate-800 text-sm mb-3">Berikan Ulasan</h3>
                    <form action="{{ route('client.booking.review', $booking->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Rating</label>
                            <select name="rating" required class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-900">
                                <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                                <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                <option value="2">⭐⭐ (2 - Kurang)</option>
                                <option value="1">⭐ (1 - Kecewa)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Komentar</label>
                            <textarea name="comment" rows="3" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-900" placeholder="Ceritakan pengalaman Anda..."></textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-blue-900 hover:bg-indigo-900 text-white font-semibold rounded-xl text-sm shadow-sm transition-all">
                            Kirim Ulasan
                        </button>
                    </form>
                @endif
            </div>
        @endif

    </div>

    <a href="{{ route('client.booking.index') }}"
       class="block text-center mt-6 text-sm text-slate-500 hover:text-blue-900 transition">
        ← Lihat semua booking saya
    </a>

</div>

@endsection