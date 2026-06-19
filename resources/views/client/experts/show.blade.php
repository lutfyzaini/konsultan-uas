@extends('layouts.app')
@section('title', $expert->user->profile->name ?? 'Detail Ahli')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-slate-600 transition">Home</a>
        <span>/</span>
        <a href="{{ route('experts.index') }}" class="hover:text-slate-600 transition">Cari Ahli</a>
        <span>/</span>
        <span class="text-slate-600">{{ $expert->user->profile->name ?? 'Profil' }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── KOLOM KIRI: Profil + Skills + Reviews ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Profil Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row gap-5">

                    {{-- Avatar besar --}}
                    @php
                        $avatarRaw = $expert->user->profile->avatar_url ?? null;
                        $avatarUrl = $avatarRaw
                            ? asset($avatarRaw)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($expert->user->profile->name ?? 'K') . '&background=1e3a5f&color=fff&size=192&bold=true';
                    @endphp
                    <img src="{{ $avatarUrl }}"
                         alt="{{ $expert->user->profile->name ?? 'Konsultan' }}"
                         class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl object-cover flex-shrink-0 border border-slate-200 shadow-sm"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($expert->user->profile->name ?? 'K') }}&background=1e3a5f&color=fff&size=192&bold=true'">

                    <div class="flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                            <div>
                                <h1 class="text-xl font-bold text-slate-800">
                                    {{ $expert->user->profile->name ?? 'Konsultan' }}
                                </h1>
                                <p class="text-slate-500 text-sm mt-0.5">{{ $expert->title }}</p>
                            </div>
                            {{-- Status Online --}}
                            @if($expert->is_online)
                            <span class="flex items-center gap-1.5 text-sm font-medium text-teal-700 bg-teal-50 px-3 py-1.5 rounded-full border border-teal-100">
                                <span class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                                Online & Siap Konsultasi
                            </span>
                            @else
                            <span class="text-sm text-slate-400 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
                                Sedang Offline
                            </span>
                            @endif
                        </div>

                        {{-- Stats row --}}
                        <div class="flex flex-wrap gap-5 text-sm">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-semibold text-slate-800">{{ number_format($expert->average_rating, 1) }}</span>
                                <span class="text-slate-400">({{ $expert->reviews->count() }} ulasan)</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $expert->total_sessions }} sesi selesai
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $expert->experience_years }} tahun pengalaman
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $expert->location ?? 'Indonesia' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kategori + Skills --}}
                <div class="mt-5 pt-5 border-t border-slate-100">
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-teal-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                            {{ $expert->category->name ?? '-' }}
                        </span>
                        @foreach($expert->skills as $skill)
                        <span class="bg-slate-100 text-slate-700 text-xs font-medium px-3 py-1.5 rounded-full">
                            {{ $skill->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bio / Tentang --}}
            @if($expert->bio)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-semibold text-slate-800 mb-3 text-base">Tentang Saya</h2>
                <p class="text-slate-600 text-sm leading-relaxed">{{ $expert->bio }}</p>
            </div>
            @endif

            {{-- Pendidikan & Sertifikasi --}}
            @if($expert->educations->isNotEmpty() || $expert->certifications->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Pendidikan --}}
                @if($expert->educations->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="font-semibold text-slate-800 mb-4 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                        </svg>
                        Pendidikan
                    </h2>
                    <div class="space-y-4">
                        @foreach($expert->educations as $edu)
                        <div class="relative pl-6 border-l-2 border-slate-100 last:pb-0 pb-1">
                            <div class="absolute -left-1.5 top-1.5 w-3 h-3 bg-blue-900 rounded-full"></div>
                            <h4 class="font-semibold text-sm text-slate-800">{{ $edu->institution_name }}</h4>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $edu->degree }} • {{ $edu->field_of_study }}</p>
                            <span class="inline-block text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100 mt-1">
                                {{ $edu->start_year }} - {{ $edu->end_year ?? 'Sekarang' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Sertifikasi --}}
                @if($expert->certifications->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="font-semibold text-slate-800 mb-4 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Sertifikasi
                    </h2>
                    <div class="space-y-4">
                        @foreach($expert->certifications as $cert)
                        <div class="relative pl-6 border-l-2 border-slate-100 last:pb-0 pb-1">
                            <div class="absolute -left-1.5 top-1.5 w-3 h-3 bg-teal-500 rounded-full"></div>
                            <h4 class="font-semibold text-sm text-slate-800">{{ $cert->certification_name }}</h4>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $cert->issuing_organization }}</p>
                            <span class="inline-block text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100 mt-1">
                                Diterbitkan: {{ $cert->issued_year }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Ulasan --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-semibold text-slate-800 text-base">Ulasan Klien</h2>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-bold text-slate-800">{{ number_format($expert->average_rating, 1) }}</span>
                        <span class="text-slate-400 text-sm">dari {{ $expert->reviews->count() }} ulasan</span>
                    </div>
                </div>

                @forelse($reviews as $review)
                <div class="py-4 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-900 font-semibold text-xs flex-shrink-0">
                            {{ strtoupper(substr($review->client->profile->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium text-slate-800 text-sm">
                                    {{ $review->client->profile->name ?? 'Pengguna' }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ $review->created_at->diffForHumans() }}
                                </span>
                            </div>
                            {{-- Bintang --}}
                            <div class="flex gap-0.5 mb-2">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'text-amber-400' : 'text-slate-200' }} fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            @if($review->comment)
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $review->comment }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm">Belum ada ulasan untuk ahli ini.</p>
                </div>
                @endforelse
            </div>

        </div>

        {{-- ── KOLOM KANAN: Booking Card (Sticky) ── --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:sticky lg:top-20">

                {{-- Harga --}}
                <div class="text-center pb-5 border-b border-slate-100 mb-5">
                    <div class="text-3xl font-bold text-blue-900">
                        Rp {{ number_format($expert->hourly_rate, 0, ',', '.') }}
                    </div>
                    <div class="text-slate-400 text-sm mt-1">per sesi (60 menit)</div>
                    <div class="mt-2">
                        <span @class([
                            'text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider',
                            'bg-amber-50 text-amber-700 border border-amber-100' => $expert->badge === 'Rising Star',
                            'bg-blue-50 text-blue-700 border border-blue-100'   => $expert->badge === 'Top Active',
                            'bg-purple-50 text-purple-700 border border-purple-100' => $expert->badge === 'Top Rated',
                        ])>
                            {{ $expert->badge === 'Top Rated' ? '⭐' : ($expert->badge === 'Top Active' ? '🔥' : '🚀') }} {{ $expert->badge }}
                        </span>
                    </div>
                </div>

                {{-- Pilih Jadwal --}}
                <h3 class="font-semibold text-slate-800 text-sm mb-4">Pilih Jadwal Konsultasi</h3>

                @if($slots->isEmpty())
                <div class="text-center py-6 bg-slate-50 rounded-xl">
                    <p class="text-slate-400 text-sm">Tidak ada slot tersedia saat ini</p>
                </div>
                @else

                {{-- Tabs hari --}}
                <div class="flex gap-1.5 flex-wrap mb-4" id="day-tabs">
                    @foreach($slots as $day => $daySlots)
                    <button onclick="showDay('{{ $day }}')"
                            id="tab-{{ $day }}"
                            class="day-tab px-3 py-1.5 text-xs font-medium rounded-lg border transition
                                   {{ $loop->first
                                      ? 'bg-blue-900 text-white border-blue-900'
                                      : 'bg-white text-slate-600 border-slate-200 hover:border-blue-900 hover:text-blue-900' }}">
                        {{ $day }}
                    </button>
                    @endforeach
                </div>

                {{-- Slot per hari --}}
                @foreach($slots as $day => $daySlots)
                <div id="slots-{{ $day }}" class="day-slots {{ !$loop->first ? 'hidden' : '' }}">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($daySlots as $slot)
                        <button onclick="selectSlot(this, {{ $slot->id }}, '{{ $day }}', '{{ substr($slot->start_time, 0, 5) }}')"
                                class="slot-btn px-3 py-2.5 text-xs font-medium rounded-lg border border-slate-200 bg-white hover:border-teal-500 hover:bg-teal-50 hover:text-teal-700 transition text-center"
                                data-slot="{{ $slot->id }}">
                            {{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- Slot terpilih --}}
                <div id="selected-info" class="hidden mt-4 p-3 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-700">
                    <div class="font-medium mb-1">Jadwal dipilih:</div>
                    <div id="selected-text" class="font-semibold"></div>
                </div>

                {{-- Tombol Booking --}}
              {{-- Tombol Booking Reguler (Sudah ada di kodinganmu) --}}
<div class="mt-4">
    @auth
        @if(auth()->user()->isClient())
        <form id="booking-form" method="POST" action="{{ route('client.booking.store') }}">
            @csrf
            <input type="hidden" name="expert_profile_id" value="{{ $expert->id }}">
            <input type="hidden" name="booking_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            <input type="hidden" name="availability_id" id="availability_id" value="">
            <button type="submit" id="btn-booking" disabled
                    class="w-full py-3 bg-blue-900 text-white font-semibold rounded-xl text-sm transition disabled:opacity-40 disabled:cursor-not-allowed enabled:hover:bg-blue-800 shadow-sm">
                Booking Sesuai Jadwal
            </button>
        </form>

       {{-- ── UBAH BAGIAN FORM INSTAN INI ── --}}
<div class="mt-2 pt-2 border-t border-dashed border-slate-200">
    @if($expert->is_online)
        <!-- Ganti action form agar mengarah ke rute client.instant.create milikmu -->
<form method="POST" action="{{ route('client.instant.create', $expert->id) }}">            @csrf
            <button type="submit" 
                    class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center gap-2">
                <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
                Konsultasi Langsung Sekarang (Instant)
            </button>
        </form>
    @else
        <button disabled 
                class="w-full py-3 bg-slate-100 text-slate-400 font-medium rounded-xl text-sm cursor-not-allowed border border-slate-200">
            Konsultasi Instan (Offline)
        </button>
    @endif
</div>
        {{-- ── AKHIR BLOK FORM KONSULTASI INSTAN ── --}}

        @else
        <div class="text-center p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
            Hanya client yang bisa melakukan booking
        </div>
        @endif
    @else
    <a href="{{ route('login') }}"
       class="block w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition text-center">
        Masuk untuk Booking
    </a>
    @endauth
</div>

                @endif

                {{-- Info tambahan --}}
                <div class="mt-5 pt-5 border-t border-slate-100 space-y-2">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Dana aman via sistem escrow
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Konfirmasi instan, tidak perlu tunggu
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Bayar via saldo dompet digital
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// Ganti tab hari
function showDay(day) {
    document.querySelectorAll('.day-slots').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.day-tab').forEach(btn => {
        btn.classList.remove('bg-blue-900', 'text-white', 'border-blue-900');
        btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
    });
    document.getElementById('slots-' + day)?.classList.remove('hidden');
    const tab = document.getElementById('tab-' + day);
    if (tab) {
        tab.classList.add('bg-blue-900', 'text-white', 'border-blue-900');
        tab.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
    }
    // Reset pilihan slot
    document.querySelectorAll('.slot-btn').forEach(b => {
        b.classList.remove('border-teal-500', 'bg-teal-100', 'text-teal-800');
    });
    document.getElementById('selected-info').classList.add('hidden');
    document.getElementById('availability_id').value = '';
    document.getElementById('btn-booking').disabled = true;
}

// Pilih slot
function selectSlot(btn, slotId, day, time) {
    document.querySelectorAll('.slot-btn').forEach(b => {
        b.classList.remove('border-teal-500', 'bg-teal-100', 'text-teal-800');
    });
    btn.classList.add('border-teal-500', 'bg-teal-100', 'text-teal-800');

    document.getElementById('availability_id').value = slotId;
    document.getElementById('selected-text').textContent = day + ', pukul ' + time;
    document.getElementById('selected-info').classList.remove('hidden');
    document.getElementById('btn-booking').disabled = false;
}
</script>
@endpush

@endsection