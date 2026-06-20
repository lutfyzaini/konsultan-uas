<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pembayaran — E-Konsul</title>
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
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-100">Bagi Hasil 90:10</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    💳 Monitoring Pembayaran
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Pantau rincian biaya platform (10% potongan), pendapatan bersih ahli (90%), serta metode transaksi finansial.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 text-left font-semibold">Invoice & ID</th>
                            <th class="p-4 text-left font-semibold">Metode</th>
                            <th class="p-4 text-right font-semibold">Subtotal (Gross)</th>
                            <th class="p-4 text-right font-semibold">Platform Fee (10%)</th>
                            <th class="p-4 text-right font-semibold">Pendapatan Bersih Ahli (90%)</th>
                            <th class="p-4 text-center font-semibold">Status</th>
                            <th class="p-4 text-left font-semibold">Tanggal Dibayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- Invoice & ID Column --}}
                                <td class="p-4">
                                    <div class="font-bold text-slate-900">{{ $payment->invoice ?? 'INV/GEN/'.str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-[10px] text-slate-400">Payment ID: #{{ $payment->id }} • Booking ID: #{{ $payment->booking_id }}</div>
                                </td>

                                {{-- Method Column --}}
                                <td class="p-4 text-sm font-medium text-slate-600">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-xs uppercase font-bold tracking-wider">
                                        {{ $payment->method ?? 'Wallet' }}
                                    </span>
                                </td>

                                {{-- Amount Column --}}
                                <td class="p-4 text-right font-bold text-slate-800 text-sm">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>

                                {{-- Commission Column --}}
                                <td class="p-4 text-right text-xs font-semibold text-rose-600">
                                    - Rp {{ number_format($payment->platform_commission, 0, ',', '.') }}
                                </td>

                                {{-- Earnings Column --}}
                                <td class="p-4 text-right text-sm font-extrabold text-emerald-600">
                                    Rp {{ number_format($payment->expert_earnings, 0, ',', '.') }}
                                </td>

                                {{-- Status Column --}}
                                <td class="p-4 text-center">
                                    @if($payment->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Paid
                                        </span>
                                    @elseif($payment->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-100 animate-pulse">
                                            Unpaid
                                        </span>
                                    @elseif($payment->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                            Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Date Column --}}
                                <td class="p-4 text-xs text-slate-500 font-medium">
                                    {{ $payment->paid_at ? $payment->paid_at->translatedFormat('d M Y, H:i') : 'Belum Dibayar' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 italic text-sm">
                                    Belum ada catatan riwayat transaksi pembayaran.
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