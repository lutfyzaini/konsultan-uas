@extends('layouts.app')
@section('title', 'Riwayat Booking')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-2xl font-bold text-blue-900 mb-6">Riwayat Booking Saya</h1>

    @if($bookings->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 p-16 text-center">
        <div class="text-5xl mb-4">📅</div>
        <h3 class="font-semibold text-slate-800 mb-2">Belum ada booking</h3>
        <p class="text-slate-500 text-sm mb-5">Mulai konsultasi dengan ahli pertama kamu sekarang</p>
        <a href="{{ route('experts.index') }}"
           class="inline-block px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-600 transition">
            Cari Ahli
        </a>
    </div>
    @else

    <div class="space-y-3">
        @foreach($bookings as $booking)
        <a href="{{ route('client.booking.show', $booking->id) }}"
           class="block bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-200 transition">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center text-blue-900 font-bold flex-shrink-0">
                        {{ strtoupper(substr($booking->expertProfile->user->profile->name ?? 'XX', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-slate-800 text-sm truncate">
                            {{ $booking->expertProfile->user->profile->name ?? 'Konsultan' }}
                        </h3>
                        <p class="text-xs text-slate-400">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }} • {{ substr($booking->start_time,0,5) }} WIB
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0">
                    <span @class([
                        'text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap',
                        'bg-amber-50 text-amber-700' => $booking->status === 'pending_payment',
                        'bg-blue-50 text-blue-700'   => $booking->status === 'confirmed',
                        'bg-teal-50 text-teal-700'   => $booking->status === 'ongoing',
                        'bg-purple-50 text-purple-700' => $booking->status === 'pending_settlement',
                        'bg-green-50 text-green-700' => $booking->status === 'completed',
                        'bg-red-50 text-red-700'     => in_array($booking->status, ['cancelled','disputed']),
                    ])>
                        {{ [
                            'pending_payment'    => 'Belum Bayar',
                            'confirmed'          => 'Terkonfirmasi',
                            'ongoing'             => 'Berlangsung',
                            'pending_settlement'  => 'Menunggu',
                            'completed'           => 'Selesai',
                            'cancelled'           => 'Dibatalkan',
                            'disputed'            => 'Disengketakan',
                        ][$booking->status] ?? $booking->status }}
                    </span>
                    <span class="text-sm font-semibold text-blue-900 whitespace-nowrap">
                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>

    @endif
</div>

@endsection