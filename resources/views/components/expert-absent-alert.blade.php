{{--
    ================================================================
    KOMPONEN: Alert Notifikasi "Expert Tidak Hadir"
    ================================================================
    Penggunaan di view manapun:
        <x-expert-absent-alert :booking="$booking" />

    Props yang dibutuhkan:
        $booking  — instance App\Models\Booking yang statusnya 'cancelled'
                    karena expert tidak hadir pada sesi instant.

    Data yang diambil otomatis dari $booking:
        - expertProfile->category_id  → untuk filter "Cari Ahli Sejenis"
        - expertProfile->id           → untuk link ke kalender expert
        - total_price                 → untuk tampilkan nominal refund
    ================================================================
--}}

@props([
    'booking' => null,
])

{{-- Hanya tampilkan jika booking valid dan statusnya dibatalkan karena ketidakhadiran expert --}}
@if ($booking && $booking->status === 'cancelled')

@php
    $expertProfile = $booking->expertProfile;
    $categoryId    = $expertProfile?->category_id;
    $expertId      = $expertProfile?->id;
    $refundAmount  = number_format((float) $booking->total_price, 0, ',', '.');

    {{-- URL aksi: filter expert berdasarkan kategori yang sama --}}
    $urlCariSejenis = route('experts.index', ['category' => $categoryId]);

    {{-- URL aksi: ke halaman detail/kalender expert untuk reschedule --}}
    $urlJadwalUlang = route('experts.show', $expertId);
@endphp

{{--
    ┌──────────────────────────────────────────────────────────────┐
    │  CONTAINER ALERT                                             │
    │  Lebar penuh, dengan animasi fade-in dari atas               │
    └──────────────────────────────────────────────────────────────┘
--}}
<div
    id="alert-expert-absent"
    role="alert"
    aria-live="assertive"
    class="w-full rounded-2xl border border-amber-200 bg-white shadow-md overflow-hidden
           animate-[fadeInDown_0.4s_ease_both]"
    style="animation: fadeInDown 0.4s ease both;"
>
    {{-- ── Garis aksen oranye di atas (visual cue warna peringatan) ── --}}
    <div class="h-1 w-full bg-gradient-to-r from-amber-400 to-orange-400"></div>

    <div class="p-5 sm:p-6">

        {{-- ── Baris 1: Ikon + Teks Utama ── --}}
        <div class="flex items-start gap-4">

            {{-- Ikon peringatan dalam lingkaran oranye lembut --}}
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-10 h-10 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center">
                    {{-- Ikon jam / waktu kadaluarsa --}}
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>

            {{-- Konten teks --}}
            <div class="flex-1 min-w-0">

                {{-- Label kecil di atas: badge status --}}
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                 bg-amber-100 text-amber-700 border border-amber-200">
                        {{-- Titik indikator --}}
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                        Sesi Dibatalkan
                    </span>
                    <span class="text-xs text-slate-400">Konsultasi Instant</span>
                </div>

                {{-- Pesan utama (teks yang diminta oleh spesifikasi) --}}
                <p class="text-sm font-semibold text-slate-800 leading-snug">
                    Mohon maaf, Expert tidak hadir dalam 10 menit.
                </p>
                <p class="text-sm text-slate-600 mt-0.5">
                    Dana Anda sebesar
                    <span class="font-semibold text-slate-800">Rp {{ $refundAmount }}</span>
                    telah dikembalikan 100% ke Wallet Anda.
                </p>

                {{-- Informasi nama expert (opsional, membantu konteks) --}}
                @if ($expertProfile)
                    <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Expert: <span class="font-medium text-slate-500">{{ $expertProfile->user->name ?? '-' }}</span>
                    </p>
                @endif

            </div>{{-- /konten teks --}}

            {{-- Tombol tutup (dismiss) sudut kanan atas --}}
            <button
                type="button"
                onclick="document.getElementById('alert-expert-absent').remove()"
                class="flex-shrink-0 w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600
                       hover:bg-slate-100 transition-colors flex items-center justify-center"
                aria-label="Tutup notifikasi"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>{{-- /baris 1 --}}

        {{-- ── Garis pemisah halus ── --}}
        <div class="my-4 border-t border-slate-100"></div>

        {{-- ── Baris 2: Tombol Aksi ── --}}
        <div class="flex flex-col sm:flex-row gap-2.5">

            {{--
                TOMBOL 1: "Cari Ahli Sejenis"
                Mengarahkan ke halaman daftar expert dengan filter category_id
                yang sama dengan expert yang batal hadir tadi.
                Warna: amber/oranye → warna dominan tema peringatan
            --}}
            <a
                id="btn-cari-sejenis"
                href="{{ $urlCariSejenis }}"
                class="inline-flex items-center justify-center gap-2 flex-1 px-4 py-2.5 rounded-xl
                       bg-amber-500 hover:bg-amber-600 active:bg-amber-700
                       text-white text-sm font-semibold
                       shadow-sm hover:shadow-md
                       transition-all duration-200 group"
            >
                {{-- Ikon search --}}
                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Cari Ahli Sejenis
            </a>

            {{--
                TOMBOL 2: "Jadwalkan Ulang"
                Mengarahkan ke halaman detail/kalender expert yang sama
                agar client bisa memesan sesi terjadwal (bukan instant).
                Warna: outline abu-abu → pilihan sekunder yang tidak mengganggu
            --}}
            <a
                id="btn-jadwal-ulang"
                href="{{ $urlJadwalUlang }}"
                class="inline-flex items-center justify-center gap-2 flex-1 px-4 py-2.5 rounded-xl
                       bg-white hover:bg-slate-50 active:bg-slate-100
                       text-slate-700 hover:text-slate-900 text-sm font-semibold
                       border border-slate-200 hover:border-slate-300
                       shadow-sm hover:shadow
                       transition-all duration-200 group"
            >
                {{-- Ikon kalender --}}
                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-hover:scale-110 transition-all"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Jadwalkan Ulang
            </a>

        </div>{{-- /baris 2 tombol aksi --}}

    </div>{{-- /padding inner --}}
</div>{{-- /container alert --}}

{{-- ── Animasi CSS inline (fallback jika Tailwind belum punya keyframe ini) ── --}}
<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

@endif {{-- /end @if booking cancelled --}}
