<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Slot Jadwal - E-Konsul</title>
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
            
            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm h-fit">
                <h3 class="font-bold text-slate-800 text-lg mb-2">Tambah Slot Jadwal</h3>
                <p class="text-xs text-slate-400 mb-6">Tentukan hari dan rentang waktu konsultasi terjadwal.</p>

                <form action="{{ route('expert.slots.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Hari</label>
                        <select name="day_of_week" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jam Mulai</label>
                            <input type="time" name="start_time" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jam Selesai</label>
                            <input type="time" name="end_time" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm text-slate-700 font-medium" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 mt-2 bg-blue-900 hover:bg-indigo-900 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all">
                        Tambahkan Slot
                    </button>
                </form>
            </div>

            <!-- List Slot Card -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-2">Daftar Slot Ketersediaan</h3>
                <p class="text-xs text-slate-400 mb-6">Slot jadwal aktif Anda untuk konsultasi terjadwal klien.</p>

                @if($slots->isEmpty())
                    <div class="py-12 text-center text-slate-400 text-sm">
                        Belum ada slot jadwal yang dibuat. Gunakan form di samping untuk membuat.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                    <th class="py-3 px-4">Hari</th>
                                    <th class="py-3 px-4">Jam Sesi</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-600">
                                @foreach($slots as $slot)
                                    <tr>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            {{ $slot->day_of_week }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }} WIB
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $slot->status === 'available' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $slot->status === 'locked' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $slot->status === 'booked' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                {{ $slot->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($slot->status === 'available')
                                                <form action="{{ route('expert.slots.destroy', $slot->id) }}" method="POST" onsubmit="return confirm('Hapus slot ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline transition-all">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs font-medium text-slate-300 select-none">
                                                    Locked/Booked
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

    </div>
</body>
</html>
