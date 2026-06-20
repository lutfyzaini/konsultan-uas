<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Berkas Ahli — E-Konsul</title>
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

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-6 mb-6">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    ✅ Verifikasi Berkas Ahli
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Tinjau berkas pendidikan, keahlian, dan sertifikasi ahli sebelum memberikan persetujuan aktif.
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 rounded-xl text-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-6">
                @forelse($experts as $expert)
                    <div class="border border-slate-200 rounded-2xl p-6 hover:border-slate-300 transition duration-300">
                        
                        {{-- Expert Header Info --}}
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-900 font-bold text-lg">
                                    {{ strtoupper(substr($expert->user->profile->name ?? $expert->user->username, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        {{ $expert->user->profile->name ?? 'Belum ada Nama' }}
                                        <span class="text-xs font-normal text-slate-400">(@ {{ $expert->user->username }})</span>
                                    </h3>
                                    <p class="text-sm text-slate-500 flex items-center gap-2">
                                        <span>{{ $expert->title ?? 'Tidak Ada Title' }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="font-medium text-blue-900 bg-blue-50 px-2 py-0.5 rounded text-xs">{{ $expert->category->name ?? 'Tanpa Kategori' }}</span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $expert->user->email }}</p>
                                </div>
                            </div>
                            
                            {{-- Status & Quick Action --}}
                            <div class="flex items-center gap-3 lg:self-start">
                                <div class="text-right mr-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Status Verifikasi</span>
                                    @if($expert->verification_status === 'approved')
                                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($expert->verification_status === 'rejected')
                                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-3 py-1 rounded-full text-xs font-semibold">Rejected</span>
                                    @else
                                        <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-semibold animate-pulse">Pending</span>
                                    @endif
                                </div>

                                @if($expert->verification_status === 'pending')
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.verifications.approve', $expert->id) }}">
                                            @csrf
                                            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.verifications.reject', $expert->id) }}">
                                            @csrf
                                            <button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Expert Metadata details --}}
                        <div class="grid md:grid-cols-3 gap-6 pt-6">
                            
                            {{-- Col 1: Bio & Overview --}}
                            <div class="md:col-span-1 border-r border-slate-100 pr-4">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Profil Ringkas & Tarif</h4>
                                <div class="space-y-2">
                                    <div class="text-sm">
                                        <span class="text-slate-500">Pengalaman:</span>
                                        <span class="font-semibold text-slate-700">{{ $expert->experience_years }} Tahun</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-slate-500">Tarif per Jam:</span>
                                        <span class="font-extrabold text-teal-700">Rp {{ number_format($expert->hourly_rate, 0, ',', '.') }}</span>
                                    </div>
                                    @if($expert->bio)
                                        <div class="mt-3">
                                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Bio</span>
                                            <p class="text-xs text-slate-600 italic leading-relaxed">{{ $expert->bio }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Col 2: Pendidikan --}}
                            <div class="md:col-span-1 border-r border-slate-100 pr-4">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Riwayat Pendidikan</h4>
                                <div class="space-y-3">
                                    @forelse($expert->educations as $edu)
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                                            <div class="text-xs font-bold text-slate-900">{{ $edu->degree }} di {{ $edu->field_of_study }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $edu->institution_name }}</div>
                                            <div class="text-[10px] text-slate-400 mt-1 font-semibold">{{ $edu->start_year }} — {{ $edu->end_year ?? 'Sekarang' }}</div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 italic">Tidak ada riwayat pendidikan terdaftar.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Col 3: Sertifikasi & Skill --}}
                            <div class="md:col-span-1">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sertifikasi & Keahlian</h4>
                                <div class="space-y-3">
                                    @forelse($expert->certifications as $cert)
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                                            <div class="text-xs font-bold text-slate-900">{{ $cert->certification_name }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $cert->issuing_organization }}</div>
                                            <div class="text-[10px] text-slate-400 mt-1 font-semibold">Tahun Terbit: {{ $cert->issued_year }}</div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 italic mb-2">Tidak ada berkas sertifikat terdaftar.</p>
                                    @endforelse

                                    @if($expert->skills->isNotEmpty())
                                        <div class="pt-2">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Skill List</span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($expert->skills as $skill)
                                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-medium border border-slate-200">{{ $skill->name }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>

                    </div>
                @empty
                    <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl">
                        <span class="text-4xl">📭</span>
                        <h3 class="font-bold text-slate-900 mt-4">Belum Ada Pengajuan Verifikasi</h3>
                        <p class="text-slate-500 text-xs mt-1">Seluruh data expert sudah diverifikasi atau belum ada data baru.</p>
                    </div>
                @endforelse
            </div>

        </div>

    </div>

</body>
</html>