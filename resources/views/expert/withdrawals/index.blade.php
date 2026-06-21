<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penarikan Saldo - E-Konsul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-indigo-700 bg-clip-text text-transparent">E-Konsul</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Expert Panel</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('expert.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-all">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        
        <!-- Alert Flash -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 text-sm">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Wallet Stats & Request Form -->
            <div class="space-y-6">
                <!-- Balance Card -->
                <div class="bg-gradient-to-br from-blue-900 to-indigo-800 text-white rounded-3xl p-6 shadow-md">
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">Saldo Tersedia</span>
                    <h2 class="text-3xl font-extrabold mt-1">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</h2>
                    <div class="mt-4 pt-4 border-t border-white/10 flex justify-between text-xs text-blue-200">
                        <div>
                            <p>Total Pendapatan</p>
                            <p class="font-bold text-white mt-0.5">Rp {{ number_format($wallet->total_earned, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p>Total Ditarik</p>
                            <p class="font-bold text-white mt-0.5">Rp {{ number_format($wallet->total_withdrawn, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="font-bold text-slate-800 text-lg mb-2">Tarik Saldo</h3>
                    <p class="text-xs text-slate-400 mb-6">Ajukan permintaan transfer manual ke rekening bank Anda.</p>

                    <form action="{{ route('expert.withdrawals.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nominal Penarikan</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-slate-400 text-sm font-semibold">Rp</span>
                                <input type="number" min="10000" max="{{ $wallet->balance }}" name="amount" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-semibold" placeholder="Min. 10.000" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Bank</label>
                            <input type="text" name="bank_name" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" placeholder="Contoh: BCA, Mandiri, BRI" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nomor Rekening</label>
                            <input type="text" name="account_number" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" placeholder="Masukkan nomor rekening" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Pemilik Rekening</label>
                            <input type="text" name="account_name" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" placeholder="Sesuai buku tabungan" required>
                        </div>

                        <button type="submit" class="w-full py-3 mt-2 bg-blue-900 hover:bg-indigo-900 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all">
                            Ajukan Penarikan
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Request Card -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-2">Riwayat Penarikan</h3>
                <p class="text-xs text-slate-400 mb-6">Status pengajuan pencairan dana Anda.</p>

                @if($withdrawals->isEmpty())
                    <div class="py-12 text-center text-slate-400 text-sm">
                        Belum ada riwayat penarikan dana.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Jumlah</th>
                                    <th class="py-3 px-4">Rekening</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-600">
                                @foreach($withdrawals as $withdrawal)
                                    <tr>
                                        <td class="py-3.5 px-4">
                                            {{ $withdrawal->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            {{ $withdrawal->bank_name }} - {{ $withdrawal->account_number }}<br>
                                            <span class="text-slate-400">a/n {{ $withdrawal->account_name }}</span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $withdrawal->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $withdrawal->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $withdrawal->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                                {{ $withdrawal->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($withdrawal->receipt_path)
                                                <a href="{{ asset('storage/' . $withdrawal->receipt_path) }}" target="_blank" class="text-xs font-semibold text-blue-900 hover:text-indigo-900 hover:underline transition-all">
                                                    Lihat Struk
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-300">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </div>

        </div>

    </div>
</body>
</html>
