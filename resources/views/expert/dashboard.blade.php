<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Expert - KonsulHub</title>
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
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-indigo-700 bg-clip-text text-transparent">KonsulHub</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Expert Panel</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-500">Masuk sebagai: <strong>{{ auth()->user()->profile->name ?? auth()->user()->username }}</strong></span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-xl transition-all">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Flash Alert -->
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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Profile Card -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 text-center">
                    <div class="relative w-24 h-24 mx-auto mb-4">
                        @php
                            $avatarRaw = auth()->user()->profile->avatar_url ?? null;
                            $avatarUrl = $avatarRaw
                                ? asset($avatarRaw)
                                : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->profile->name ?? 'E') . '&background=1e3a5f&color=fff&size=192&bold=true';
                        @endphp
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="w-full h-full rounded-2xl object-cover border border-slate-200 shadow-sm">
                    </div>
                    
                    <h2 class="font-bold text-slate-800 text-lg leading-tight">{{ auth()->user()->profile->name ?? 'Expert' }}</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">{{ $expert->title ?? 'Professional Advisor' }}</p>
                    
                    <div class="mt-4 flex flex-col items-center gap-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold 
                            {{ $expert->verification_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                            <span>{{ $expert->verification_status === 'approved' ? '● Approved' : '● ' . ucfirst($expert->verification_status) }}</span>
                        </div>

                        <!-- Dynamic Performance Badge -->
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border 
                                {{ $expert->badge === 'Top Rated' ? 'bg-amber-100 text-amber-800 border-amber-200' : 
                                   ($expert->badge === 'Top Active' ? 'bg-blue-100 text-blue-800 border-blue-200' : 
                                   'bg-slate-100 text-slate-700 border-slate-200') }}">
                                🏆 {{ $expert->badge }}
                            </span>
                        </div>
                    </div>

                    <!-- Online Status Switcher -->
                    @if($expert->verification_status === 'approved')
                        <div class="mt-6 border-t border-slate-100 pt-6">
                            <label class="flex items-center justify-between cursor-pointer p-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all">
                                <span class="text-sm font-medium text-slate-600">Status Online</span>
                                <div class="relative">
                                    <input type="checkbox" id="online-switch" class="sr-only" {{ $expert->is_online ? 'checked' : '' }} onchange="toggleOnlineStatus()">
                                    <div class="block bg-slate-300 w-10 h-6 rounded-full transition-colors duration-200" id="switch-bg"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200 transform {{ $expert->is_online ? 'translate-x-4 bg-emerald-500' : '' }}" id="switch-dot"></div>
                                </div>
                            </label>
                            <p class="text-[10px] text-slate-400 mt-2 text-left leading-relaxed">
                                * Aktifkan agar muncul di pencarian instan klien.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Navigation Menu -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4">
                    <nav class="space-y-1">
                        <a href="{{ route('expert.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium bg-blue-900 text-white shadow-sm transition-all">
                            <span>📊</span> Dashboard
                        </a>
                        <a href="{{ route('expert.slots.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all">
                            <span>📅</span> Kelola Slot Jadwal
                        </a>
                        <a href="{{ route('expert.profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all">
                            <span>⚙️</span> Edit Profil Pakar
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3 space-y-8">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Balance -->
                    <div class="bg-gradient-to-br from-blue-900 to-indigo-900 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 text-white/5 text-9xl font-bold">Rp</div>
                        <p class="text-xs text-white/70 uppercase tracking-wider font-semibold">Saldo Wallet</p>
                        <h3 class="text-2xl font-bold mt-2">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</h3>
                        <div class="mt-4 flex gap-4 text-xs text-white/80 border-t border-white/10 pt-4">
                            <div>Total Pendapatan: <strong class="text-white">Rp {{ number_format($wallet->total_earned, 0, ',', '.') }}</strong></div>
                        </div>
                    </div>
                    
                    <!-- Rating -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Rating Pakar</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-2 flex items-center gap-2">
                            <span>⭐</span> {{ number_format($expert->average_rating, 1) }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-4 border-t border-slate-100 pt-4">
                            Dari total <strong>{{ $expert->total_sessions }}</strong> sesi diselesaikan.
                        </p>
                    </div>

                    <!-- Level & Penalty -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Tingkatan Komisi</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-2 capitalize">{{ $expert->commission_level }}</h3>
                        <div class="mt-4 flex justify-between text-xs text-slate-400 border-t border-slate-100 pt-4">
                            <span>Potongan Komisi: <strong>
                                {{ $expert->commission_level === 'master' ? '10%' : ($expert->commission_level === 'pro' ? '15%' : '20%') }}
                            </strong></span>
                            <span class="text-red-500">Penalti No-Show: <strong>{{ $expert->penalty_count }}/3</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Active Consultations -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-900 rounded-full animate-pulse"></span>
                        Konsultasi Aktif & Terkonfirmasi
                    </h3>

                    @if($activeSessions->isEmpty())
                        <div class="py-8 text-center text-slate-400 text-sm">
                            Tidak ada sesi aktif atau yang dijadwalkan saat ini.
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($activeSessions as $booking)
                                <div class="py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-800 text-sm">{{ $booking->client->profile->name ?? $booking->client->username }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase
                                                {{ $booking->booking_type === 'instant' ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                                {{ $booking->booking_type }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1">
                                            Jadwal: {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }} | {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WIB
                                        </p>
                                        @if($booking->booking_type === 'instant')
                                            <p class="text-xs text-amber-600 font-semibold mt-1">
                                                * Sesi instan. Langsung masuk ruang konsultasi untuk merespon klien.
                                            </p>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('expert.consultation.room', $booking->id) }}" 
                                           class="px-4 py-2 bg-blue-900 hover:bg-indigo-900 text-white rounded-xl text-xs font-semibold shadow-sm transition-all inline-block">
                                            Masuk Ruang Chat
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Consultation History -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-slate-800 mb-4">Riwayat Konsultasi Lampau</h3>
                    
                    @php
                        $pastBookings = $bookings->whereIn('status', ['completed', 'cancelled', 'pending_settlement']);
                    @endphp

                    @if($pastBookings->isEmpty())
                        <div class="py-8 text-center text-slate-400 text-sm">
                            Belum ada riwayat konsultasi.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-medium">
                                        <th class="py-3 px-4">Klien</th>
                                        <th class="py-3 px-4">Tipe</th>
                                        <th class="py-3 px-4">Tanggal / Sesi</th>
                                        <th class="py-3 px-4">Tarif Bersih</th>
                                        <th class="py-3 px-4 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-slate-600">
                                    @foreach($pastBookings as $booking)
                                        <tr>
                                            <td class="py-3 px-4 font-medium text-slate-800">
                                                {{ $booking->client->profile->name ?? $booking->client->username }}
                                            </td>
                                            <td class="py-3 px-4 capitalize">{{ $booking->booking_type }}</td>
                                            <td class="py-3 px-4 text-xs">
                                                {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}<br>
                                                {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                            </td>
                                            <td class="py-3 px-4">
                                                Rp {{ number_format($booking->payment->expert_earnings ?? $booking->total_price, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                    {{ $booking->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                    {{ $booking->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}
                                                    {{ $booking->status === 'pending_settlement' ? 'bg-amber-100 text-amber-800' : '' }}">
                                                    {{ str_replace('_', ' ', $booking->status) }}
                                                </span>
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
    </div>

    <!-- Toggle Online Script -->
    <script>
        function toggleOnlineStatus() {
            const checkbox = document.getElementById('online-switch');
            const bg = document.getElementById('switch-bg');
            const dot = document.getElementById('switch-dot');

            fetch('{{ route("expert.toggle-online") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.is_online) {
                        dot.classList.add('translate-x-4', 'bg-emerald-500');
                        bg.classList.remove('bg-slate-300');
                        bg.classList.add('bg-emerald-100');
                    } else {
                        dot.classList.remove('translate-x-4', 'bg-emerald-500');
                        bg.classList.add('bg-slate-300');
                        bg.classList.remove('bg-emerald-100');
                    }
                } else {
                    alert(data.message);
                    checkbox.checked = !checkbox.checked;
                }
            })
            .catch(err => {
                alert('Gagal memperbarui status online.');
                checkbox.checked = !checkbox.checked;
            });
        }
    </script>
</body>
</html>