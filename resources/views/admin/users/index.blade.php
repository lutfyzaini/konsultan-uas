<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna — E-Konsul</title>
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
                    👥 Manajemen Pengguna
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Lihat daftar seluruh akun, peran (role), status keaktifan, dan lakukan penangguhan (suspend) bagi akun pelanggar.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 text-left font-semibold">User</th>
                            <th class="p-4 text-left font-semibold">Email</th>
                            <th class="p-4 text-left font-semibold">Peran (Role)</th>
                            <th class="p-4 text-left font-semibold">Status Keaktifan</th>
                            <th class="p-4 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- User Column --}}
                                <td class="p-4 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs
                                        {{ $user->role === 'admin' ? 'bg-rose-100 text-rose-800' : ($user->role === 'expert' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ strtoupper(substr($user->username, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">@ {{ $user->username }}</div>
                                        <div class="text-xs text-slate-400">ID: {{ $user->id }}</div>
                                    </div>
                                </td>

                                {{-- Email Column --}}
                                <td class="p-4 text-sm text-slate-600 font-medium">
                                    {{ $user->email }}
                                </td>

                                {{-- Role Column --}}
                                <td class="p-4">
                                    @if($user->role == 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                            Admin
                                        </span>
                                    @elseif($user->role == 'expert')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                            Expert
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Client
                                        </span>
                                    @endif
                                </td>

                                {{-- Status Column --}}
                                <td class="p-4">
                                    @if($user->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Suspended
                                        </span>
                                    @endif
                                </td>

                                {{-- Action Column --}}
                                <td class="p-4 text-center">
                                    @if($user->role !== 'admin')
                                        <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}">
                                            @csrf
                                            <button class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition shadow-sm
                                                {{ $user->status === 'active' 
                                                    ? 'bg-white hover:bg-rose-50 text-rose-600 border-rose-200 hover:border-rose-300' 
                                                    : 'bg-emerald-600 hover:bg-emerald-700 text-white border-transparent' }}">
                                                {{ $user->status === 'active' ? 'Suspend Akun' : 'Aktifkan Akun' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No Action (Admin)</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>