<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — E-Konsul</title>
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

    {{-- Main Container --}}
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        {{-- Header Section --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 mb-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 text-xs font-semibold rounded-full border border-rose-100">Portal Keamanan</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    🛡️ Admin Panel
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Selamat datang kembali, <span class="font-semibold text-slate-700">{{ auth()->user()->username }}</span>. Kelola dan awasi operasional platform E-Konsul.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.withdrawals.index') }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-medium text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tarik Saldo
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-4 py-2 bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 font-medium text-sm rounded-xl transition border border-slate-200 hover:border-red-100 shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            
            {{-- Stat 1: Expert Pending --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-amber-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-amber-600 tracking-wider uppercase">Pending Ahli</span>
                    <span class="p-1 bg-amber-50 rounded-lg text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\ExpertProfile::where('verification_status','pending')->count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Butuh verifikasi</p>
            </div>

            {{-- Stat 2: Total User --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-blue-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-600 tracking-wider uppercase">Total User</span>
                    <span class="p-1 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\User::count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Pengguna terdaftar</p>
            </div>

            {{-- Stat 3: Total Kategori --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-emerald-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-emerald-600 tracking-wider uppercase">Kategori</span>
                    <span class="p-1 bg-emerald-50 rounded-lg text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\Category::count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Bidang konsultasi</p>
            </div>

            {{-- Stat 4: Total Skill --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-purple-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-purple-600 tracking-wider uppercase">Total Skill</span>
                    <span class="p-1 bg-purple-50 rounded-lg text-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\Skill::count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Keahlian spesifik</p>
            </div>

            {{-- Stat 5: Total Booking --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-rose-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-rose-600 tracking-wider uppercase">Sesi Selesai</span>
                    <span class="p-1 bg-rose-50 rounded-lg text-rose-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\Booking::count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Transaksi booking</p>
            </div>

            {{-- Stat 6: Total Payment --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-cyan-300 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-cyan-600 tracking-wider uppercase">Total Bayar</span>
                    <span class="p-1 bg-cyan-50 rounded-lg text-cyan-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold text-slate-900 mt-2">
                    {{ \App\Models\Payment::count() }}
                </div>
                <p class="text-slate-400 text-[10px] mt-1">Invoices terbit</p>
            </div>

        </div>

        {{-- Administration Menu Grid --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span>📂</span> Menu Administrasi Platform
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Card 1: Kelola Kategori --}}
                <a href="{{ route('admin.categories.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        📂
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-blue-900 transition">Kelola Kategori</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Tambah, ubah, atau hapus kategori utama bidang konsultasi.
                        </p>
                    </div>
                </a>

                {{-- Card 2: Kelola Skill --}}
                <a href="{{ route('admin.skills.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-emerald-50/50 hover:border-emerald-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        🛠️
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-emerald-900 transition">Kelola Skill</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Definisikan keahlian khusus penunjang filter pencarian ahli.
                        </p>
                    </div>
                </a>

                {{-- Card 3: Verifikasi Ahli --}}
                <a href="{{ route('admin.verifications.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-amber-50/50 hover:border-amber-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        ✅
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-amber-900 transition">Verifikasi Ahli</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Periksa berkas ijazah & sertifikat, lalu verifikasi pengajuan expert.
                        </p>
                    </div>
                </a>

                {{-- Card 4: User Management --}}
                <a href="{{ route('admin.users.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-indigo-50/50 hover:border-indigo-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        👥
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-indigo-900 transition">User Management</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Kelola data klien/pakar dan suspend akun bermasalah.
                        </p>
                    </div>
                </a>

                {{-- Card 5: Monitoring Payment --}}
                <a href="{{ route('admin.payments.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-pink-50/50 hover:border-pink-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center text-pink-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        💳
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-pink-900 transition">Monitoring Payment</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Lihat invoices, biaya platform 10%, dan transfer pendapatan ahli.
                        </p>
                    </div>
                </a>

                {{-- Card 6: Monitoring Booking --}}
                <a href="{{ route('admin.bookings.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-purple-50/50 hover:border-purple-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        📅
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-purple-900 transition">Monitoring Booking</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Pantau riwayat sesi konsultasi terjadwal maupun instan.
                        </p>
                    </div>
                </a>

                {{-- Card 7: Pengaturan Platform --}}
                <a href="{{ route('admin.settings.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-cyan-50/50 hover:border-cyan-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center text-cyan-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        ⚙️
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-cyan-900 transition">Pengaturan Platform</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Konfigurasi tarif platform, bonus discount badge, dan limit waktu.
                        </p>
                    </div>
                </a>

                {{-- Card 8: Permintaan Pencairan --}}
                <a href="{{ route('admin.withdrawals.index') }}" class="group border border-slate-100 bg-slate-50 hover:bg-rose-50/50 hover:border-rose-200 p-6 rounded-2xl transition duration-300 flex items-start gap-4">
                    <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center text-rose-900 text-xl font-semibold flex-shrink-0 group-hover:scale-105 transition duration-300">
                        💸
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 group-hover:text-rose-900 transition">Pencairan Saldo</h3>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                            Proses permintaan transfer manual dana pendapatan pakar.
                        </p>
                    </div>
                </a>

            </div>
        </div>

    </div>

</body>
</html>