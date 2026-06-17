@extends('layouts.app')
@section('title', 'Pembayaran')

@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Countdown Banner --}}
    <div id="countdown-banner" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">Selesaikan pembayaran sebelum waktu habis</p>
                <p class="text-xs text-amber-600">Slot akan dilepas otomatis jika tidak dibayar</p>
            </div>
        </div>
        <div id="countdown-timer" class="text-2xl font-bold text-amber-700 tabular-nums">
            15:00
        </div>
    </div>

    {{-- Card Detail Booking --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Header: profil expert --}}
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

        {{-- Detail jadwal --}}
        <div class="p-6 space-y-4">

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Kategori</span>
                <span class="text-sm font-medium text-slate-800">
                    {{ $booking->expertProfile->category->name ?? '-' }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Tanggal</span>
                <span class="text-sm font-medium text-slate-800">
                    {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('l, d F Y') }}
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Waktu</span>
                <span class="text-sm font-medium text-slate-800">
                    {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }} WIB
                </span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Durasi</span>
                <span class="text-sm font-medium text-slate-800">60 menit</span>
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-between py-4 bg-slate-50 rounded-xl px-4 -mx-1">
                <span class="text-sm font-semibold text-slate-700">Total Pembayaran</span>
                <span class="text-xl font-bold text-blue-900">
                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                </span>
            </div>

        </div>

        {{-- Metode Pembayaran (Simulasi) --}}
        <div class="px-6 pb-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Metode Pembayaran</h3>

            <div class="border-2 border-teal-500 bg-teal-50 rounded-xl p-4 flex items-center justify-between mb-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Dompet Digital (Simulasi)</p>
                        <p class="text-xs text-slate-500">
                            Saldo kamu: <strong>Rp {{ number_format(auth()->user()->wallet->balance ?? 0, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>

            {{-- Peringatan saldo kurang --}}
            @if((auth()->user()->wallet->balance ?? 0) < $booking->total_price)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                ⚠️ Saldo kamu tidak mencukupi untuk booking ini.
            </div>
            @endif
        </div>

        {{-- Tombol Aksi --}}
        <div class="px-6 pb-6 flex gap-3">
            <form method="POST" action="{{ route('client.booking.cancel', $booking->id) }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        onclick="return confirm('Batalkan booking ini?')"
                        class="px-5 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-medium rounded-xl text-sm transition">
                    Batalkan
                </button>
            </form>

            <form method="POST" action="{{ route('client.booking.pay', $booking->id) }}" class="flex-1">
                @csrf
                <button type="submit"
                        @if((auth()->user()->wallet->balance ?? 0) < $booking->total_price) disabled @endif
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition
                               disabled:opacity-40 disabled:cursor-not-allowed">
                    Bayar Sekarang
                </button>
            </form>
        </div>

    </div>

    {{-- Info keamanan --}}
    <div class="flex items-center justify-center gap-2 mt-5 text-xs text-slate-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Dana kamu aman tersimpan di sistem escrow sampai sesi selesai
    </div>

</div>

@push('scripts')
<script>
// ════════════════════════════════════════════════
// COUNTDOWN TIMER 15 MENIT
// Sinkron dengan payment_deadline dari server
// ════════════════════════════════════════════════
let secondsRemaining = {{ $secondsRemaining }};

function updateCountdown() {
    if (secondsRemaining <= 0) {
        // waktu habis — redirect ke halaman expert dengan pesan
        document.getElementById('countdown-timer').textContent = '00:00';
        document.getElementById('countdown-banner').classList.remove('bg-amber-50', 'border-amber-200');
        document.getElementById('countdown-banner').classList.add('bg-red-50', 'border-red-200');

        setTimeout(() => {
            window.location.href = "{{ route('experts.index') }}?expired=1";
        }, 1500);
        return;
    }

    const minutes = Math.floor(secondsRemaining / 60);
    const seconds = secondsRemaining % 60;
    document.getElementById('countdown-timer').textContent =
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

    // warna jadi merah saat < 2 menit
    if (secondsRemaining <= 120) {
        document.getElementById('countdown-timer').classList.add('text-red-600');
        document.getElementById('countdown-timer').classList.remove('text-amber-700');
    }

    secondsRemaining--;
}

updateCountdown();
setInterval(updateCountdown, 1000);
</script>
@endpush

@endsection