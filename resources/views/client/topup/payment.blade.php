
@extends('layouts.app')
@section('title', 'Pembayaran Top Up')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Header status -->
        <div class="bg-slate-900 text-white p-6 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/20 text-amber-400 text-xs font-bold rounded-full border border-amber-500/30 mb-2">
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                Menunggu Pembayaran
            </span>
            <h1 class="text-lg font-bold">Gerbang Pembayaran</h1>
        </div>

        <div class="p-6 space-y-6">
            <!-- Amount Details -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 text-center">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Pembayaran</p>
                <p class="text-3xl font-extrabold text-blue-900 mt-1">
                    Rp {{ number_format($amount, 0, ',', '.') }}
                </p>
            </div>

            <!-- Transfer Instructions -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-800">Petunjuk Pembayaran</h3>

                @if(in_array($method, ['gopay', 'ovo']))
                    <!-- QR Mock -->
                    <div class="flex flex-col items-center justify-center p-6 border border-slate-200 rounded-2xl bg-white shadow-sm space-y-3">
                        <div class="w-48 h-48 bg-slate-100 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-300 relative overflow-hidden">
                            <!-- Draw a mock QR Code with simple div squares -->
                            <div class="absolute inset-4 grid grid-cols-4 gap-2 opacity-80">
                                @for($i = 0; $i < 16; $i++)
                                    <div class="bg-slate-800 rounded @if($i % 3 === 0 || $i % 5 === 0) opacity-100 @else opacity-0 @endif"></div>
                                @endfor
                            </div>
                            <span class="relative z-10 text-xs font-semibold text-slate-500 uppercase tracking-widest bg-white px-2 py-1 rounded shadow border">QR CODE MOCK</span>
                        </div>
                        <p class="text-xs text-slate-500 text-center">Scan kode QR di atas menggunakan aplikasi <strong>{{ strtoupper($method) }}</strong> Anda.</p>
                    </div>
                @else
                    <!-- Bank Transfer Mock -->
                    <div class="p-5 border border-slate-200 rounded-2xl space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400 font-semibold">Nama Bank</span>
                            <span class="font-bold text-slate-800">{{ strtoupper($method) }} Virtual Account</span>
                        </div>
                        <div class="h-px bg-slate-100"></div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400 font-semibold">Nomor VA</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-blue-900 tracking-wider">880192837465</span>
                                <button onclick="alert('Nomor VA berhasil disalin!')" class="text-xs text-teal-600 font-bold hover:underline">Salin</button>
                            </div>
                        </div>
                        <div class="h-px bg-slate-100"></div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400 font-semibold">Nama Penerima</span>
                            <span class="font-bold text-slate-800">E-Konsul ({{ auth()->user()->profile->name ?? auth()->user()->username }})</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Simulation Action Form -->
            <form action="{{ route('client.topup.pay') }}" method="POST" class="pt-4">
                @csrf
                <input type="hidden" name="amount" value="{{ $amount }}">
                <input type="hidden" name="method" value="{{ $method }}">

                <button type="submit"
                        class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl transition shadow-lg shadow-teal-100 text-center text-sm flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pembayaran Berhasil
                </button>
            </form>

            <!-- Cancel Button -->
            <a href="{{ route('client.topup.index') }}"
               class="block w-full py-3.5 border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800 font-semibold rounded-2xl transition text-center text-xs">
                Batalkan Top Up
            </a>
        </div>
    </div>
</div>
@endsection
