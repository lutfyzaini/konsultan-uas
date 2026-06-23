@extends('layouts.app')
@section('title', 'E-Konsul — Temukan Ahli Profesional')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     HERO SECTION
     Grid 2 kolom: kiri headline + CTA, kanan ilustrasi
═══════════════════════════════════════════════════════════ --}}
<section class="bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Kolom Kiri: Headline & CTA --}}
            <div class="order-2 lg:order-1">

                {{-- Badge atas --}}
                <div class="inline-flex items-center gap-2 bg-teal-50 border border-teal-200 text-teal-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
                    <div class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></div>
                    100+ Ahli Terverifikasi Siap Membantu
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl lg:text-5xl xl:text-6xl font-extrabold text-blue-900 leading-tight mb-6">
                    Temukan Solusi
                    <span class="text-teal-600">Tepat</span> Bersama
                    Ahli Terpercaya
                </h1>

                {{-- Deskripsi --}}
                <p class="text-slate-500 text-lg leading-relaxed mb-8 max-w-lg">
                    Konsultasikan masalah hukum, bisnis, IT, atau kesehatan kamu langsung dengan para profesional bersertifikat. Cepat, aman, dan terjangkau.
                </p>

                {{-- Tombol CTA --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('experts.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition shadow-lg shadow-amber-200 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Temukan Ahli Sekarang
                    </a>
                    <a href="#cara-kerja"
                       class="inline-flex items-center justify-center gap-2 px-7 py-3.5 border border-slate-300 text-slate-700 hover:border-blue-900 hover:text-blue-900 font-medium rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cara Kerjanya
                    </a>
                </div>

                {{-- Stats kecil --}}
                <div class="flex items-center gap-6 mt-10 pt-8 border-t border-slate-100">
                    <div>
                        <div class="text-2xl font-bold text-blue-900">500+</div>
                        <div class="text-xs text-slate-500 mt-0.5">Sesi Konsultasi</div>
                    </div>
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">100+</div>
                        <div class="text-xs text-slate-500 mt-0.5">Ahli Aktif</div>
                    </div>
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">4.9★</div>
                        <div class="text-xs text-slate-500 mt-0.5">Rating Rata-rata</div>
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan: Ilustrasi --}}
            <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
                <div class="relative w-full max-w-md">

                    {{-- Placeholder ilustrasi --}}
                    <div class="bg-gradient-to-br from-blue-50 to-teal-50 rounded-3xl aspect-square flex items-center justify-center border border-blue-100 shadow-xl shadow-blue-100">
                        <div class="text-center p-8 flex flex-col items-center justify-center">
                            {{-- Logo E-Konsul besar --}}
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-900 to-blue-700 rounded-3xl flex items-center justify-center shadow-lg shadow-blue-900/30 mb-4 transform hover:scale-105 transition duration-300">
                                <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-3xl font-extrabold text-blue-900 tracking-tight font-heading">E-Konsul</span>
                            <p class="text-slate-500 text-sm mt-1 max-w-[200px] text-center">Menghubungkan Solusi & Keahlian</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     VALUE PROPOSITION
     4 kolom keunggulan platform
═══════════════════════════════════════════════════════════ --}}
<section class="bg-slate-50 py-16 lg:py-20 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Heading section --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-blue-900 mb-3">Mengapa Memilih E-Konsul?</h2>
            <p class="text-slate-500 max-w-xl mx-auto">Kami hadir untuk memastikan setiap konsultasi berjalan dengan aman, transparan, dan memberikan hasil nyata.</p>
        </div>

        {{-- 4 Kolom Keunggulan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Card 1: Ahli Bersertifikat --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-200 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center mb-5 transition">
                    <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">Ahli Bersertifikat</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Semua konsultan telah melewati proses verifikasi ketat oleh tim kami sebelum bisa melayani.</p>
            </div>

            {{-- Card 2: Privasi Aman --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-teal-200 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-teal-50 group-hover:bg-teal-100 rounded-xl flex items-center justify-center mb-5 transition">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">Privasi Aman</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Setiap percakapan dienkripsi dan dijaga kerahasiaannya. Data kamu tidak akan pernah dibagikan.</p>
            </div>

            {{-- Card 3: Respons Cepat --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-200 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center mb-5 transition">
                    <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">Respons Cepat</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Booking jadwal dalam hitungan menit. Tidak perlu antre lama atau menunggu konfirmasi manual.</p>
            </div>

            {{-- Card 4: Harga Transparan --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-amber-200 hover:shadow-md transition group">
                <div class="w-12 h-12 bg-amber-50 group-hover:bg-amber-100 rounded-xl flex items-center justify-center mb-5 transition">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-2">Harga Transparan</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Tarif jelas tertera di profil setiap ahli. Tidak ada biaya tersembunyi atau kejutan di akhir sesi.</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FEATURED CONSULTANTS
     Tampilkan 4 consultant card terbaik
═══════════════════════════════════════════════════════════ --}}
<section class="bg-white py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Heading --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-blue-900 mb-2">Konsultan Unggulan</h2>
                <p class="text-slate-500">Pilihan teratas berdasarkan rating dan jumlah sesi</p>
            </div>
            <a href="{{ route('experts.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-600 hover:text-teal-700 transition flex-shrink-0">
                Lihat semua ahli
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Grid Consultant Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @forelse($featuredExperts ?? [] as $expert)
            {{-- Card dinamis dari database --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 overflow-hidden group">
                <div class="p-6">
                    {{-- Avatar --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center text-blue-900 font-bold text-lg">
                            {{ strtoupper(substr($expert->user->profile->name ?? $expert->title, 0, 2)) }}
                        </div>
                        {{-- Badge online --}}
                        @if($expert->is_online)
                        <span class="flex items-center gap-1.5 text-xs font-medium text-teal-700 bg-teal-50 px-2 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>
                            Online
                        </span>
                        @endif
                    </div>

                    {{-- Nama & Title --}}
                    <h3 class="font-semibold text-slate-800 text-sm mb-1">
                        {{ $expert->user->profile->name ?? 'Konsultan' }}
                    </h3>
                    <p class="text-xs text-slate-400 mb-3">{{ $expert->title }}</p>

                    {{-- Spesialisasi badge --}}
                    <span class="inline-block bg-teal-50 text-teal-700 text-xs font-medium px-2.5 py-1 rounded-full border border-teal-100 mb-4">
                        {{ $expert->category->name ?? 'Umum' }}
                    </span>

                    {{-- Rating & Sesi --}}
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-5">
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-medium text-slate-700">{{ number_format($expert->average_rating, 1) }}</span>
                            <span>({{ $expert->total_sessions }} sesi)</span>
                        </div>
                        <span class="font-semibold text-blue-900">
                            Rp {{ number_format($expert->hourly_rate, 0, ',', '.') }}/jam
                        </span>
                    </div>

                    {{-- Tombol --}}
                    <a href="{{ route('experts.show', $expert->id) }}"
                       class="block w-full text-center py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl transition">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
            @empty
            {{-- Placeholder kalau belum ada data --}}
            @foreach(range(1,4) as $i)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center text-blue-900 font-bold text-lg">
                            {{ ['SH','BD','AI','RK'][$i-1] }}
                        </div>
                        <span class="flex items-center gap-1.5 text-xs font-medium text-teal-700 bg-teal-50 px-2 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>
                            Online
                        </span>
                    </div>
                    <h3 class="font-semibold text-slate-800 text-sm mb-1">
                        {{ ['Dr. Siti Rahayu, S.H.', 'Budi Santoso, M.Des.', 'Andi Pratama, S.Kom.', 'Rina Kartini, M.M.'][$i-1] }}
                    </h3>
                    <p class="text-xs text-slate-400 mb-3">
                        {{ ['Konsultan Hukum & Advokat', 'UI/UX Designer & Brand Consultant', 'Full Stack Developer', 'Konsultan Bisnis & Keuangan'][$i-1] }}
                    </p>
                    <span class="inline-block bg-teal-50 text-teal-700 text-xs font-medium px-2.5 py-1 rounded-full border border-teal-100 mb-4">
                        {{ ['Hukum & Legalitas', 'Desain & Kreatif', 'Teknologi & IT', 'Keuangan'][$i-1] }}
                    </span>
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-5">
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-medium text-slate-700">{{ ['4.9','4.7','4.8','4.6'][$i-1] }}</span>
                            <span>({{ [24,12,18,9][$i-1] }} sesi)</span>
                        </div>
                        <span class="font-semibold text-blue-900">
                            Rp {{ ['150.000','100.000','200.000','125.000'][$i-1] }}/jam
                        </span>
                    </div>
                    <a href="{{ route('experts.index') }}"
                       class="block w-full text-center py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl transition">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
            @endforeach
            @endforelse

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     HOW IT WORKS
     3 langkah mudah
═══════════════════════════════════════════════════════════ --}}
<section id="cara-kerja" class="bg-gradient-to-br from-blue-900 to-blue-800 py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Heading --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-white mb-3">Cara Kerjanya Mudah</h2>
            <p class="text-blue-200 max-w-lg mx-auto">Mulai konsultasi profesional hanya dalam 3 langkah sederhana</p>
        </div>

        {{-- 3 Langkah --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">

            {{-- Garis penghubung (desktop) --}}
            <div class="hidden md:block absolute top-10 left-1/3 right-1/3 h-px bg-blue-600 z-0"></div>

            {{-- Langkah 1 --}}
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/20 shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="inline-block bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">Langkah 1</div>
                <h3 class="font-semibold text-white text-lg mb-2">Cari & Pilih Ahli</h3>
                <p class="text-blue-200 text-sm leading-relaxed px-4">
                    Telusuri ratusan konsultan berdasarkan kategori, rating, dan ketersediaan waktu.
                </p>
            </div>

            {{-- Langkah 2 --}}
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/20 shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="inline-block bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">Langkah 2</div>
                <h3 class="font-semibold text-white text-lg mb-2">Pilih Jadwal & Bayar</h3>
                <p class="text-blue-200 text-sm leading-relaxed px-4">
                    Pilih slot waktu yang tersedia dan lakukan pembayaran aman lewat dompet digital.
                </p>
            </div>

            {{-- Langkah 3 --}}
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-5 border border-white/20 shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div class="inline-block bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">Langkah 3</div>
                <h3 class="font-semibold text-white text-lg mb-2">Mulai Konsultasi</h3>
                <p class="text-blue-200 text-sm leading-relaxed px-4">
                    Masuk ke ruang chat eksklusif dan konsultasikan masalahmu langsung dengan ahlinya.
                </p>
            </div>

        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     TESTIMONI
     Social proof dari klien sebelumnya
═══════════════════════════════════════════════════════════ --}}
<section class="bg-slate-50 py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-blue-900 mb-3">Apa Kata Mereka?</h2>
            <p class="text-slate-500">Ribuan pengguna telah merasakan manfaat konsultasi profesional</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @foreach([
                ['Sangat membantu! Konsultasi hukum yang saya butuhkan terselesaikan dalam 1 sesi. Ahlinya sangat responsif dan penjelasannya mudah dipahami.', 'Rina K.', 'Client Hukum', '5'],
                ['Desain brand saya akhirnya jadi sesuai visi setelah diskusi panjang dengan konsultan di sini. Prosesnya mudah dan harganya sangat worth it!', 'Doni S.', 'Client Desain', '5'],
                ['Saya punya masalah bug di sistem, dan dalam 1 jam konsultasi sudah ketemu solusinya. Booking-nya gampang banget, langsung ketemu ahlinya.', 'Maya R.', 'Client IT', '4'],
            ] as [$quote, $name, $role, $rating])
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                {{-- Bintang --}}
                <div class="flex gap-0.5 mb-4">
                    @for($s = 1; $s <= 5; $s++)
                    <svg class="w-4 h-4 {{ $s <= $rating ? 'text-amber-400' : 'text-slate-200' }} fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                {{-- Quote --}}
                <p class="text-slate-600 text-sm leading-relaxed mb-5 italic">"{{ $quote }}"</p>
                {{-- User --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-100 to-teal-100 rounded-full flex items-center justify-center text-blue-900 font-semibold text-xs">
                        {{ substr($name, 0, 2) }}
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-800">{{ $name }}</div>
                        <div class="text-xs text-slate-400">{{ $role }}</div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     CTA AKHIR
     Dorong user untuk registrasi
═══════════════════════════════════════════════════════════ --}}
<section class="bg-white py-16 lg:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-gradient-to-br from-blue-900 to-teal-700 rounded-3xl p-10 lg:p-14 shadow-2xl shadow-blue-200">
            <h2 class="text-3xl lg:text-4xl font-extrabold text-white mb-4 leading-tight">
                Siap Menemukan Solusi?
            </h2>
            <p class="text-blue-100 text-base mb-8 max-w-lg mx-auto">
                Bergabunglah dengan ribuan pengguna yang sudah mendapatkan solusi nyata dari para ahli profesional kami.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl transition shadow-lg shadow-amber-900/30 text-sm">
                    Daftar Gratis Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('experts.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition text-sm border border-white/20">
                    Lihat Semua Ahli
                </a>
            </div>
        </div>
    </div>
</section>

@endsection