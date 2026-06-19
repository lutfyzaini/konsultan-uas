@extends('layouts.app')
@section('title', 'Cari Ahli')

@section('content')

{{-- ── HEADER SECTION ── --}}
<section class="bg-blue-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white mb-2">Cari Ahli Profesional</h1>
        <p class="text-blue-200 text-sm mb-6">Temukan konsultan terbaik sesuai kebutuhan kamu</p>

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('experts.index') }}" class="flex gap-2">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari berdasarkan nama, keahlian, atau topik..."
                       class="w-full pl-9 pr-4 py-3 rounded-xl text-sm border-0 focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800">
            </div>
            {{-- Pertahankan filter lain saat search --}}
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            <button type="submit"
                    class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl text-sm transition">
                Cari
            </button>
        </form>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ── SIDEBAR FILTER ── --}}
        <aside class="w-full lg:w-64 flex-shrink-0">
            <form method="GET" action="{{ route('experts.index') }}" id="filter-form">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                {{-- Filter Kategori --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-4">
                    <h3 class="font-semibold text-slate-800 text-sm mb-3">Kategori</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="radio" name="category" value=""
                                   {{ !request('category') ? 'checked' : '' }}
                                   onchange="document.getElementById('filter-form').submit()"
                                   class="w-4 h-4 text-blue-900 border-slate-300">
                            <span class="text-sm text-slate-600 group-hover:text-slate-900">Semua Kategori</span>
                        </label>
                        @foreach($categories as $cat)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="radio" name="category" value="{{ $cat->id }}"
                                   {{ request('category') == $cat->id ? 'checked' : '' }}
                                   onchange="document.getElementById('filter-form').submit()"
                                   class="w-4 h-4 text-blue-900 border-slate-300">
                            <span class="text-sm text-slate-600 group-hover:text-slate-900">{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Status Online --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-4">
                    <h3 class="font-semibold text-slate-800 text-sm mb-3">Status</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="online" value="1"
                               {{ request('online') ? 'checked' : '' }}
                               onchange="document.getElementById('filter-form').submit()"
                               class="w-4 h-4 text-teal-600 border-slate-300 rounded">
                        <span class="text-sm text-slate-600">Hanya yang Online</span>
                        <span class="ml-auto w-2 h-2 bg-teal-500 rounded-full"></span>
                    </label>
                </div>

                {{-- Sorting --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 text-sm mb-3">Urutkan</h3>
                    <div class="space-y-2">
                        @foreach([
                            'rating'     => 'Rating Tertinggi',
                            'sessions'   => 'Paling Berpengalaman',
                            'price_asc'  => 'Harga Terendah',
                            'price_desc' => 'Harga Tertinggi',
                        ] as $val => $label)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="radio" name="sort" value="{{ $val }}"
                                   {{ request('sort', 'rating') === $val ? 'checked' : '' }}
                                   onchange="document.getElementById('filter-form').submit()"
                                   class="w-4 h-4 text-blue-900 border-slate-300">
                            <span class="text-sm text-slate-600 group-hover:text-slate-900">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Reset filter --}}
                @if(request()->hasAny(['category','online','sort','search']))
                <a href="{{ route('experts.index') }}"
                   class="block mt-3 text-center text-xs text-slate-400 hover:text-red-500 transition py-2">
                    ✕ Reset semua filter
                </a>
                @endif
            </form>
        </aside>

        {{-- ── MAIN CONTENT ── --}}
        <div class="flex-1 min-w-0">

            {{-- Info hasil & jumlah --}}
            <div class="flex items-center justify-between mb-5">
                <p class="text-sm text-slate-500">
                    Menampilkan
                    <strong class="text-slate-800">{{ $experts->total() }}</strong> ahli
                    @if(request('search'))
                        untuk "<strong class="text-slate-800">{{ request('search') }}</strong>"
                    @endif
                </p>
                <span class="text-xs text-slate-400">
                    Halaman {{ $experts->currentPage() }} dari {{ $experts->lastPage() }}
                </span>
            </div>

            @if($experts->isEmpty())
            {{-- Empty state --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-16 text-center">
                <div class="text-5xl mb-4">🔍</div>
                <h3 class="font-semibold text-slate-800 mb-2">Tidak ada ahli ditemukan</h3>
                <p class="text-slate-500 text-sm mb-5">Coba ubah filter atau kata kunci pencarian kamu</p>
                <a href="{{ route('experts.index') }}"
                   class="inline-block px-5 py-2 bg-blue-900 text-white text-sm font-medium rounded-xl hover:bg-blue-800 transition">
                    Lihat Semua Ahli
                </a>
            </div>
            @else

            {{-- Grid Expert Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($experts as $expert)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 overflow-hidden flex flex-col">
                    <div class="p-5 flex-1">

                        {{-- Header card: foto + online badge --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                {{-- Avatar foto --}}
                                @php
                                    $avatarRaw = $expert->user->profile->avatar_url ?? null;
                                    $avatarUrl = $avatarRaw
                                        ? asset($avatarRaw)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($expert->user->profile->name ?? 'K') . '&background=1e3a5f&color=fff&size=96&bold=true';
                                @endphp
                                <img src="{{ $avatarUrl }}"
                                     alt="{{ $expert->user->profile->name ?? 'Konsultan' }}"
                                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-100"
                                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($expert->user->profile->name ?? 'K') }}&background=1e3a5f&color=fff&size=96&bold=true'">
                                <div>
                                    <h3 class="font-semibold text-slate-800 text-sm leading-tight">
                                        {{ $expert->user->profile->name ?? 'Konsultan' }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $expert->title }}</p>
                                </div>
                            </div>
                            @if($expert->is_online)
                            <span class="flex items-center gap-1 text-xs font-medium text-teal-700 bg-teal-50 px-2 py-1 rounded-full border border-teal-100 flex-shrink-0">
                                <span class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></span>
                                Online
                            </span>
                            @else
                            <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded-full border border-slate-100 flex-shrink-0">
                                Offline
                            </span>
                            @endif
                        </div>

                        {{-- Kategori + Skills --}}
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <span class="bg-teal-50 text-teal-700 text-xs font-medium px-2.5 py-1 rounded-full border border-teal-100">
                                {{ $expert->category->name ?? '-' }}
                            </span>
                            @foreach($expert->skills->take(2) as $skill)
                            <span class="bg-slate-50 text-slate-600 text-xs px-2.5 py-1 rounded-full border border-slate-100">
                                {{ $skill->name }}
                            </span>
                            @endforeach
                            @if($expert->skills->count() > 2)
                            <span class="text-xs text-slate-400 px-2 py-1">+{{ $expert->skills->count() - 2 }}</span>
                            @endif
                        </div>

                        {{-- Bio singkat --}}
                        @if($expert->bio)
                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4">{{ $expert->bio }}</p>
                        @endif

                        {{-- Stats: rating, sesi, pengalaman --}}
                        <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-medium text-slate-700">{{ number_format($expert->average_rating, 1) }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $expert->total_sessions }} sesi
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $expert->experience_years }} thn
                            </div>
                        </div>

                        {{-- Level badge --}}
                        <div class="flex items-center justify-between">
                            <span @class([
                                'text-xs font-semibold px-2.5 py-1 rounded-full',
                                'bg-amber-50 text-amber-700 border border-amber-100' => $expert->commission_level === 'newbie',
                                'bg-blue-50 text-blue-700 border border-blue-100'   => $expert->commission_level === 'pro',
                                'bg-purple-50 text-purple-700 border border-purple-100' => $expert->commission_level === 'master',
                            ])>
                                {{ ['newbie' => '🌱 Newbie', 'pro' => '⭐ Pro', 'master' => '🏆 Master'][$expert->commission_level] }}
                            </span>
                            <span class="font-bold text-blue-900 text-sm">
                                Rp {{ number_format($expert->hourly_rate, 0, ',', '.') }}<span class="font-normal text-slate-400 text-xs">/jam</span>
                            </span>
                        </div>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="px-5 pb-5">
                        <a href="{{ route('experts.show', $expert->id) }}"
                           class="block w-full text-center py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl transition">
                            Lihat Profil & Jadwal
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($experts->hasPages())
            <div class="mt-8">
                {{ $experts->links() }}
            </div>
            @endif

            @endif
        </div>
    </div>
</div>

@endsection