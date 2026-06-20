<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Booking — E-Konsul</title>
    {{-- Google Fonts: Inter + Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation Header --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-6 mb-6">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    📅 Monitoring Booking Sesi
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Awasi seluruh transaksi pemesanan sesi konsultasi terjadwal (`scheduled`) maupun instan (`instant`), termasuk durasi dan alasan pembatalan.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 text-left font-semibold">Sesi & ID</th>
                            <th class="p-4 text-left font-semibold">Tipe</th>
                            <th class="p-4 text-left font-semibold">Client (Klien)</th>
                            <th class="p-4 text-left font-semibold">Expert (Pakar)</th>
                            <th class="p-4 text-left font-semibold">Waktu Jadwal</th>
                            <th class="p-4 text-right font-semibold">Harga Total</th>
                            <th class="p-4 text-center font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- Booking ID --}}
                                <td class="p-4">
                                    <div class="font-bold text-slate-900">Sesi #{{ $booking->id }}</div>
                                    <div class="text-[10px] text-slate-400">Created: {{ $booking->created_at->format('d M Y') }}</div>
                                </td>

                                {{-- Type Column --}}
                                <td class="p-4">
                                    @if($booking->booking_type === 'instant')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase tracking-wider">
                                            Instant
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-wider">
                                            Scheduled
                                        </span>
                                    @endif
                                </td>

                                {{-- Client Column --}}
                                <td class="p-4 text-sm font-semibold text-slate-700">
                                    {{ $booking->client->profile->name ?? 'User @'.$booking->client->username }}
                                    <span class="block text-[10px] text-slate-400 font-normal">@ {{ $booking->client->username }}</span>
                                </td>

                                {{-- Expert Column --}}
                                <td class="p-4 text-sm font-semibold text-slate-700">
                                    {{ $booking->expertProfile->user->profile->name ?? 'User @'.$booking->expertProfile->user->username }}
                                    <span class="block text-[10px] text-slate-400 font-normal">@ {{ $booking->expertProfile->user->username }}</span>
                                </td>

                                {{-- Schedule Column --}}
                                <td class="p-4 text-xs text-slate-600 font-medium">
                                    <div>{{ $booking->booking_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold">{{ substr($booking->start_time, 0, 5) }} — {{ substr($booking->end_time, 0, 5) }}</div>
                                </td>

                                {{-- Price Column --}}
                                <td class="p-4 text-right font-extrabold text-slate-900 text-sm">
                                    Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                </td>

                                {{-- Status Column --}}
                                <td class="p-4 text-center">
                                    @php
                                        $statusStyles = match($booking->status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'ongoing' => 'bg-purple-50 text-purple-700 border-purple-100 animate-pulse',
                                            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'pending_payment' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            default => 'bg-slate-50 text-slate-700 border-slate-100'
                                        };
                                    @endphp
                                    <span class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusStyles }}">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                        @if($booking->status === 'cancelled' && $booking->cancel_reason)
                                            <span class="text-[9px] text-rose-500 font-bold mt-1">Reason: {{ str_replace('_', ' ', $booking->cancel_reason) }}</span>
                                        @endif
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 italic text-sm">
                                    Belum ada data pemesanan sesi konsultasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>