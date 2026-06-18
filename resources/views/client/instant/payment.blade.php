@extends('layouts.app')
@section('title', 'Bayar Konsultasi Instan')

@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Badge instant --}}
    <div class="flex items-center justify-center gap-2 mb-5">
        <span class="flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.78 1.63l-9 11A1 1 0 016 19v-5H2a1 1 0 01-.78-1.63l9-11a1 1 0 011.08-.324z"/>
            </svg>
            Konsultasi Instan
        </span>
    </div>

    {{-- Countdown Banner --}}
    <div id="countdown-banner" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">Selesaikan pembayaran sebelum waktu habis</p>
                <p class="text-xs text-amber-600">Setelah bayar, kamu langsung masuk ke ruang konsultasi</p>
            </div>
        </div>
        <div id="countdown-timer" class="text-2xl font-bold text-amber-700 tabular-nums">15:00</div>
    </div>

    {{-- Card Detail --}}
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
                <span class="text-sm text-slate-500">Tipe Sesi</span>
                <span class="text-sm font-medium text-amber-700">⚡ Instan — mulai sekarang</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-sm text-slate-500">Durasi</span>
                <span class="text-sm font-medium text-slate-800">60 menit</span>
            </div>

            <div class="flex items-center justify-between py-4 bg-slate-50 rounded-xl px-4 -mx-1">
                <span class="text-sm font-semibold text-slate-700">Total Pembayaran</span>
                <span class="text-xl font-bold text-blue-900">
                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="px-6 pb-6">
            <div class="border-2 border-teal-500 bg-teal-50 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Dompet Digital</p>
                        <p class="text-xs text-slate-500">
                            Saldo: <strong>Rp {{ number_format(auth()->user()->wallet->balance ?? 0, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>

            @if((auth()->user()->wallet->balance ?? 0) < $booking->total_price)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                ⚠️ Saldo kamu tidak mencukupi.
            </div>
            @endif
        </div>

        {{-- Peringatan no-show --}}
        <div class="px-6 pb-6">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-500 leading-relaxed">
                <strong class="text-slate-700">Penting:</strong> Setelah membayar, kamu punya waktu <strong>10 menit</strong>
                untuk bergabung ke ruang konsultasi. Jika kamu tidak hadir, dana tidak dapat dikembalikan.
                Jika expert tidak hadir, dana akan dikembalikan penuh.
            </div>
        </div>

        <div class="px-6 pb-6">
            <form method="POST" action="{{ route('client.instant.pay', $booking->id) }}">
                @csrf
                <button type="submit"
                        @if((auth()->user()->wallet->balance ?? 0) < $booking->total_price) disabled @endif
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition
                               disabled:opacity-40 disabled:cursor-not-allowed">
                    Bayar & Mulai Konsultasi
                </button>
            </form>
        </div>

    </div>

</div>

@push('scripts')
<script>
let secondsRemaining = {{ $secondsRemaining }};

function updateCountdown() {
    if (secondsRemaining <= 0) {
        document.getElementById('countdown-timer').textContent = '00:00';
        setTimeout(() => { window.location.href = "{{ route('experts.index') }}"; }, 1500);
        return;
    }
    const m = Math.floor(secondsRemaining / 60);
    const s = secondsRemaining % 60;
    document.getElementById('countdown-timer').textContent =
        String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    secondsRemaining--;
}
updateCountdown();
setInterval(updateCountdown, 1000);
</script>
@endpush

@endsection