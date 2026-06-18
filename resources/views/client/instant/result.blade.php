@extends('layouts.app')
@section('title', 'Hasil Konsultasi')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-12">
<div class="max-w-lg w-full space-y-6">

    {{-- ═══════════════════════════════════════════════
         BLOK UTAMA — Kondisi sesi (berhasil / expert mangkir)
    ═══════════════════════════════════════════════ --}}

    @php
        /*
         * Tentukan kondisi akhir sesi:
         *  - expert_no_show : booking dibatalkan karena expert tidak hadir (client dapat refund)
         *  - completed      : sesi selesai normal
         *  - default        : cancelled karena alasan lain
         */
        $isExpertNoShow = $booking->cancel_reason === 'expert_no_show'
                       || $booking->client_notes  === 'EXPERT_ABSENT_REFUND';
        $isCompleted    = $booking->status === 'completed';
    @endphp

    {{-- ─── KASUS 1: Expert Mangkir (Refund) ─── --}}
    @if($isExpertNoShow)

    {{-- Icon & Judul --}}
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-800">Expert Tidak Hadir</h1>
        <p class="text-slate-500 text-sm mt-1">Sesi konsultasi Anda dibatalkan otomatis</p>
    </div>

    {{-- Alert Box Refund --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 002 0V7zm-1 7a1 1 0 100-2 1 1 0 000 2z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-800 mb-1">Dana Anda Aman</p>
                <p class="text-sm text-amber-700 leading-relaxed">
                    Mohon maaf, Expert tidak hadir dalam waktu 10 menit.
                    Dana Anda telah di-<strong>refund 100%</strong> ke Wallet dan siap digunakan kembali.
                </p>
            </div>
        </div>
    </div>

    {{-- Detail Booking --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Expert</span>
            <span class="text-sm font-medium text-slate-800">
                {{ $booking->expertProfile->user->profile->name ?? 'N/A' }}
            </span>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Kategori</span>
            <span class="text-sm font-medium text-slate-800">
                {{ $booking->expertProfile->category->name ?? 'N/A' }}
            </span>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Dana Dikembalikan</span>
            <span class="text-sm font-bold text-teal-600">
                Rp {{ number_format($booking->total_price, 0, ',', '.') }}
            </span>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Status</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-red-600 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                Dibatalkan
            </span>
        </div>
    </div>

    {{-- ─── TOMBOL AKSI RETENSI ─── --}}
    <div class="space-y-3">
        {{-- Tombol 1: Cari Expert Sejenis --}}
        <a href="{{ route('experts.index', ['category_id' => $booking->expertProfile->category_id ?? '']) }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 bg-amber-600 hover:bg-amber-700 active:scale-[0.98]
                  text-white font-semibold rounded-xl shadow-sm transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Cari Ahli Sejenis
        </a>

        {{-- Tombol 2: Jadwalkan Ulang (ke halaman detail expert untuk booking terjadwal biasa) --}}
        <a href="{{ route('experts.show', $booking->expert_profile_id) }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 bg-white hover:bg-slate-50 active:scale-[0.98]
                  text-slate-700 font-semibold rounded-xl border border-slate-300 shadow-sm transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Jadwalkan Ulang
        </a>
    </div>

    {{-- ─── KASUS 2: Sesi Selesai Normal ─── --}}
    @elseif($isCompleted)

    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-800">Sesi Selesai!</h1>
        <p class="text-slate-500 text-sm mt-1">Konsultasi Anda telah berhasil diselesaikan</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Expert</span>
            <span class="text-sm font-medium text-slate-800">
                {{ $booking->expertProfile->user->profile->name ?? 'N/A' }}
            </span>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Total Dibayar</span>
            <span class="text-sm font-bold text-blue-900">
                Rp {{ number_format($booking->total_price, 0, ',', '.') }}
            </span>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5">
            <span class="text-sm text-slate-500">Status</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-teal-50 text-teal-700 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>
                Selesai
            </span>
        </div>
    </div>

    <div class="space-y-3">
        {{-- Kembali ke katalog --}}
        <a href="{{ route('experts.index') }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 bg-blue-900 hover:bg-blue-800 active:scale-[0.98]
                  text-white font-semibold rounded-xl shadow-sm transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kembali ke Beranda
        </a>
        {{-- Booking ulang dengan expert yang sama --}}
        <a href="{{ route('experts.show', $booking->expert_profile_id) }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 bg-white hover:bg-slate-50 active:scale-[0.98]
                  text-slate-700 font-semibold rounded-xl border border-slate-300 shadow-sm transition text-sm">
            Booking Lagi dengan Expert Ini
        </a>
    </div>

    {{-- ─── KASUS 3: Dibatalkan karena alasan lain ─── --}}
    @else

    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-800">Sesi Dibatalkan</h1>
        <p class="text-slate-500 text-sm mt-1">Sesi konsultasi ini telah berakhir</p>
    </div>

    <div class="space-y-3">
        <a href="{{ route('experts.index') }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 bg-amber-600 hover:bg-amber-700 active:scale-[0.98]
                  text-white font-semibold rounded-xl shadow-sm transition text-sm">
            Cari Expert Lain
        </a>
    </div>

    @endif

</div>
</div>
@endsection