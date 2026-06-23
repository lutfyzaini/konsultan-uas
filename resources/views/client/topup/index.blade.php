@extends('layouts.app')
@section('title', 'Top Up Saldo Wallet')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('client.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-900 to-indigo-950 p-8 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-teal-500/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Top Up Saldo E-Konsul</h1>
                    <p class="text-blue-200 text-xs mt-1">Tambahkan saldo wallet kamu untuk booking sesi instan atau terjadwal.</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('client.topup.store') }}" method="POST" class="p-8 space-y-6">
            @csrf

            <!-- Nominal Input -->
            <div class="space-y-3">
                <label for="amount" class="block text-sm font-semibold text-slate-700">Nominal Top Up</label>
                <div class="relative rounded-2xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-slate-400 font-bold text-lg">Rp</span>
                    </div>
                    <input type="number" name="amount" id="amount" 
                           class="block w-full pl-12 pr-4 py-4 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent text-xl font-bold text-slate-800"
                           placeholder="0" min="10000" max="10000000" required>
                </div>
                <p class="text-xs text-slate-400">Minimal top-up Rp 10.000, Maksimal Rp 10.000.000</p>

                <!-- Preset Nominal Grid -->
                <div class="grid grid-cols-3 gap-2 mt-3">
                    @foreach([50000, 100000, 200000, 500000, 1000000, 2000000] as $preset)
                        <button type="button" onclick="setAmount({{ $preset }})"
                                class="py-2.5 px-3 border border-slate-200 hover:border-blue-900 hover:bg-blue-50/50 rounded-xl text-xs font-semibold text-slate-600 hover:text-blue-900 transition text-center">
                            +Rp {{ number_format($preset, 0, ',', '.') }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Payment Method -->
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-slate-700">Pilih Metode Pembayaran</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Gopay -->
                    <label class="relative flex items-center gap-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition group">
                        <input type="radio" name="payment_method" value="gopay" class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900" required>
                        <div class="flex-grow">
                            <span class="block text-sm font-bold text-slate-800">GoPay</span>
                            <span class="block text-xs text-slate-400">E-Wallet Instan</span>
                        </div>
                    </label>

                    <!-- OVO -->
                    <label class="relative flex items-center gap-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition group">
                        <input type="radio" name="payment_method" value="ovo" class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900">
                        <div class="flex-grow">
                            <span class="block text-sm font-bold text-slate-800">OVO</span>
                            <span class="block text-xs text-slate-400">E-Wallet Instan</span>
                        </div>
                    </label>

                    <!-- BCA -->
                    <label class="relative flex items-center gap-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition group">
                        <input type="radio" name="payment_method" value="bca" class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900">
                        <div class="flex-grow">
                            <span class="block text-sm font-bold text-slate-800">BCA Virtual Account</span>
                            <span class="block text-xs text-slate-400">Transfer Bank</span>
                        </div>
                    </label>

                    <!-- Mandiri -->
                    <label class="relative flex items-center gap-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition group">
                        <input type="radio" name="payment_method" value="mandiri" class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900">
                        <div class="flex-grow">
                            <span class="block text-sm font-bold text-slate-800">Mandiri VA</span>
                            <span class="block text-xs text-slate-400">Transfer Bank</span>
                        </div>
                    </label>

                    <!-- BRI -->
                    <label class="relative flex items-center gap-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 cursor-pointer transition group">
                        <input type="radio" name="payment_method" value="bri" class="w-4 h-4 text-blue-900 border-slate-300 focus:ring-blue-900">
                        <div class="flex-grow">
                            <span class="block text-sm font-bold text-slate-800">BRIVA</span>
                            <span class="block text-xs text-slate-400">Transfer Bank</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
                <button type="submit"
                        class="w-full py-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-2xl transition shadow-lg shadow-amber-200 text-center text-sm flex items-center justify-center gap-2">
                    Lanjutkan ke Pembayaran
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setAmount(val) {
        document.getElementById('amount').value = val;
    }
</script>
@endsection
