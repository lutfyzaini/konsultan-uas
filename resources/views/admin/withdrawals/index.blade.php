<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penarikan Saldo — E-Konsul</title>
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

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
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
                    💸 Kelola Penarikan Saldo
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Verifikasi dan konfirmasi pencairan dana pendapatan pakar/expert.
                </p>
            </div>

            {{-- Flash Alert --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 rounded-xl text-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 text-left font-semibold">Pakar</th>
                            <th class="p-4 text-left font-semibold">Nominal</th>
                            <th class="p-4 text-left font-semibold">Rekening Tujuan</th>
                            <th class="p-4 text-left font-semibold">Status</th>
                            <th class="p-4 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($withdrawals as $withdrawal)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- User Column --}}
                                <td class="p-4 font-bold text-slate-900">
                                    {{ $withdrawal->user->profile->name ?? $withdrawal->user->username }}<br>
                                    <span class="text-xs text-slate-400 font-normal">Requested: {{ $withdrawal->created_at->format('d M Y, H:i') }}</span>
                                </td>

                                {{-- Amount Column --}}
                                <td class="p-4 font-extrabold text-blue-900 text-base">
                                    Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                </td>

                                {{-- Account details Column --}}
                                <td class="p-4">
                                    <div class="font-semibold text-slate-700">{{ $withdrawal->bank_name }} - {{ $withdrawal->account_number }}</div>
                                    <div class="text-xs text-slate-500">a/n {{ $withdrawal->account_name }}</div>
                                </td>

                                {{-- Status Column --}}
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $withdrawal->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                        {{ $withdrawal->status === 'completed' ? 'bg-teal-50 text-teal-700 border border-teal-100' : '' }}
                                        {{ $withdrawal->status === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-100' : '' }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>

                                {{-- Action Column --}}
                                <td class="p-4 text-center">
                                    @if($withdrawal->status === 'pending')
                                        <div class="flex flex-col gap-2 max-w-[200px] mx-auto">
                                            <!-- Approve Form -->
                                            <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-1.5 p-2 bg-slate-50 rounded-xl border border-slate-200">
                                                @csrf
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase text-left">Upload Struk</label>
                                                <input type="file" name="receipt" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                <button type="submit" class="w-full py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] rounded-lg transition">
                                                    Setujui (Transfer Selesai)
                                                </button>
                                            </form>

                                            <!-- Reject Form -->
                                            <form action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}" method="POST" class="flex flex-col gap-1.5 p-2 bg-slate-50 rounded-xl border border-slate-200">
                                                @csrf
                                                <input type="text" name="admin_notes" required placeholder="Alasan penolakan" class="block w-full rounded-md border border-slate-200 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-rose-500">
                                                <button type="submit" class="w-full py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] rounded-lg transition">
                                                    Tolak Permintaan
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        @if($withdrawal->receipt_path)
                                            <a href="{{ asset('storage/' . $withdrawal->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-800 text-xs font-bold rounded-xl transition">
                                                🔍 Lihat Struk
                                            </a>
                                        @elseif($withdrawal->admin_notes)
                                            <span class="text-xs text-rose-600 italic">Ditolak: "{{ $withdrawal->admin_notes }}"</span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 italic">
                                    Belum ada permintaan penarikan saldo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $withdrawals->links() }}
            </div>

        </div>

    </div>

</body>
</html>
