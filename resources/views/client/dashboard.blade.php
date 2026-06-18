@extends('layouts.app')
@section('title', 'Dashboard Client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Greeting --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">
            Halo, {{ auth()->user()->profile->name ?? auth()->user()->email }}! 👋
        </h1>
        <p class="text-slate-500 text-sm mt-1">Selamat datang di KonsulHub — temukan ahli terbaik untuk kebutuhanmu.</p>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Saldo Wallet</p>
            <p class="text-2xl font-bold text-blue-900">
                Rp {{ number_format(auth()->user()->wallet->balance ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Total Sesi</p>
            <p class="text-2xl font-bold text-blue-900">
                {{ App\Models\Booking::where('client_id', auth()->id())->count() }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Sesi Aktif</p>
            <p class="text-2xl font-bold text-amber-600">
                {{ App\Models\Booking::where('client_id', auth()->id())->where('status','ongoing')->count() }}
            </p>
        </div>
    </div>

    {{-- CTA --}}
    <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-2xl p-8 text-white flex flex-col sm:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-xl font-bold mb-1">Butuh konsultasi sekarang?</h2>
            <p class="text-blue-200 text-sm">Temukan expert online dan mulai konsultasi instan dalam hitungan detik.</p>
        </div>
        <a href="{{ route('experts.index') }}"
           class="flex-shrink-0 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl transition text-sm whitespace-nowrap">
            Cari Expert Sekarang
        </a>
    </div>

    {{-- Riwayat Booking Terakhir --}}
    <div class="mt-8">
        <h2 class="text-base font-semibold text-slate-800 mb-4">Riwayat Booking</h2>
        @php
            $bookings = App\Models\Booking::with(['expertProfile.user.profile','expertProfile.category'])
                ->where('client_id', auth()->id())
                ->latest()
                ->take(5)
                ->get();
        @endphp

        @if($bookings->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
            <p class="text-slate-400 text-sm">Belum ada booking. <a href="{{ route('experts.index') }}" class="text-blue-900 font-medium underline">Cari expert sekarang</a>.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
            @foreach($bookings as $b)
            <div class="flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-900 font-bold text-xs">
                        {{ strtoupper(substr($b->expertProfile->user->profile->name ?? 'XX', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $b->expertProfile->user->profile->name ?? 'Expert' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ $b->expertProfile->category->name ?? '' }} ·
                            {{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}
                            @if($b->booking_type === 'instant')
                            <span class="ml-1 text-amber-600 font-medium">⚡ Instan</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span @class([
                    'text-xs font-semibold px-2.5 py-1 rounded-full',
                    'bg-yellow-50 text-yellow-700'  => $b->status === 'pending_payment',
                    'bg-blue-50 text-blue-700'      => $b->status === 'confirmed',
                    'bg-teal-50 text-teal-700'      => $b->status === 'ongoing',
                    'bg-green-50 text-green-700'    => $b->status === 'completed',
                    'bg-red-50 text-red-600'        => $b->status === 'cancelled',
                    'bg-slate-50 text-slate-500'    => !in_array($b->status, ['pending_payment','confirmed','ongoing','completed','cancelled']),
                ])>
                    {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
