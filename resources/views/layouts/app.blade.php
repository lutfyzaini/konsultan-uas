<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KonsulHub') — Platform Konsultasi Profesional</title>

    {{-- Google Fonts: Inter + Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:    ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#1E3A8A',  // blue-900
                            light:   '#1D4ED8',  // blue-700
                            dark:    '#172554',  // blue-950
                        },
                        secondary: {
                            DEFAULT: '#0D9488',  // teal-600
                            light:   '#CCFBF1',  // teal-100
                        },
                        accent: {
                            DEFAULT: '#D97706',  // amber-600
                            hover:   '#B45309',  // amber-700
                        },
                    },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Animasi smooth untuk navbar mobile */
        #mobile-menu { transition: max-height 0.3s ease, opacity 0.3s ease; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #1E3A8A; border-radius: 3px; }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased">

    {{-- ═══════════════════════════════════════════════
         NAVBAR
    ═══════════════════════════════════════════════ --}}
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-blue-900 tracking-tight">KonsulHub</span>
                </a>

                {{-- Menu Desktop --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition
                              {{ request()->routeIs('home') ? 'text-blue-900 bg-blue-50' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-50' }}">
                        Home
                    </a>
                    <a href="{{ route('experts.index') }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition
                              {{ request()->routeIs('experts.*') ? 'text-blue-900 bg-blue-50' : 'text-slate-600 hover:text-blue-900 hover:bg-slate-50' }}">
                        Cari Ahli
                    </a>
                    <a href="#"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition text-slate-600 hover:text-blue-900 hover:bg-slate-50">
                        Artikel
                    </a>
                </div>

                {{-- CTA & User Menu Desktop --}}
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        {{-- Sudah login: tampilkan nama + link dashboard --}}
                        <a href="{{ route(auth()->user()->role . '.dashboard') }}"
                           class="flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-blue-900 transition">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-900 font-semibold text-xs">
                                {{ strtoupper(substr(auth()->user()->profile->name ?? auth()->user()->username, 0, 2)) }}
                            </div>
                            <span>{{ auth()->user()->profile->name ?? auth()->user()->username }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-red-500 transition">
                                Keluar
                            </button>
                        </form>
                    @else
                        {{-- Belum login --}}
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-50 rounded-lg transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="px-5 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition shadow-sm">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>

                {{-- Hamburger Mobile --}}
                <button id="mobile-menu-btn"
                        class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition"
                        onclick="toggleMobileMenu()">
                    <svg id="icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="md:hidden hidden border-t border-slate-100 bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Home</a>
                <a href="{{ route('experts.index') }}" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Cari Ahli</a>
                <a href="#" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Artikel</a>
                <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                    @auth
                        <a href="{{ route(auth()->user()->role . '.dashboard') }}"
                           class="px-3 py-2 text-sm font-medium text-blue-900 bg-blue-50 rounded-lg text-center">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-center text-blue-900 border border-blue-900 rounded-lg">Masuk</a>
                        <a href="{{ route('register') }}" class="px-3 py-2 text-sm font-semibold text-center text-white bg-amber-600 rounded-lg">Daftar Sekarang</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- ═══════════════════════════════════════════════
         FLASH MESSAGE (global)
    ═══════════════════════════════════════════════ --}}
    @if(session('success'))
        <div id="flash-success"
             class="fixed top-20 right-4 z-50 max-w-sm bg-teal-600 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
            <button onclick="this.parentElement.remove()" class="ml-auto opacity-70 hover:opacity-100">✕</button>
        </div>
        <script>setTimeout(() => { document.getElementById('flash-success')?.remove(); }, 4000);</script>
    @endif

    @if(session('error'))
        <div id="flash-error"
             class="fixed top-20 right-4 z-50 max-w-sm bg-red-600 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
            <button onclick="this.parentElement.remove()" class="ml-auto opacity-70 hover:opacity-100">✕</button>
        </div>
        <script>setTimeout(() => { document.getElementById('flash-error')?.remove(); }, 4000);</script>
    @endif

    {{-- ═══════════════════════════════════════════════
         KONTEN UTAMA
    ═══════════════════════════════════════════════ --}}
    <main>
        @yield('content')
    </main>

    {{-- ═══════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════ --}}
    <footer class="bg-slate-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                {{-- Brand --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">KonsulHub</span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                        Platform konsultasi profesional yang menghubungkan Anda dengan para ahli terpercaya di berbagai bidang.
                    </p>
                </div>

                {{-- Menu --}}
                <div>
                    <h4 class="font-semibold text-sm mb-4 text-slate-300 uppercase tracking-wider">Platform</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('experts.index') }}" class="hover:text-white transition">Cari Ahli</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Daftar Jadi Ahli</a></li>
                        <li><a href="#" class="hover:text-white transition">Artikel</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-sm mb-4 text-slate-300 uppercase tracking-wider">Legal</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-white transition">Hubungi Kami</a></li>
                    </ul>
                </div>

            </div>

            {{-- Copyright --}}
            <div class="mt-10 pt-6 border-t border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-slate-400 text-sm">
                    &copy; {{ date('Y') }} KonsulHub. Semua hak dilindungi.
                </p>
                <p class="text-slate-500 text-xs">Dibuat untuk Proyek UAS Pemrograman Web</p>
            </div>
        </div>
    </footer>

    {{-- Script Navbar Mobile --}}
    <script>
        function toggleMobileMenu() {
            const menu   = document.getElementById('mobile-menu');
            const open   = document.getElementById('icon-open');
            const close  = document.getElementById('icon-close');
            menu.classList.toggle('hidden');
            open.classList.toggle('hidden');
            close.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')
</body>
</html>