@extends('layouts.app')
@section('title', 'Ruang Konsultasi — ' . ($booking->expertProfile->user->profile->name ?? 'Expert'))

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col">

    {{-- ═══════════════════════════════════════════════
         HEADER — Info Expert & Status Sesi
    ═══════════════════════════════════════════════ --}}
    <div class="bg-blue-900 text-white shadow-lg flex-shrink-0">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

            {{-- Avatar + Info Expert --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('client.dashboard') }}" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-all font-semibold mr-1" title="Kembali ke Dashboard">
                    ←
                </a>
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($booking->expertProfile->user->profile->name ?? 'XX', 0, 2)) }}
                </div>
                <div>
                    <p class="font-semibold text-sm leading-tight">
                        {{ $booking->expertProfile->user->profile->name ?? 'Konsultan' }}
                    </p>
                    <p class="text-blue-300 text-xs">
                        {{ $booking->expertProfile->category->name ?? '' }}
                    </p>
                </div>
            </div>

            {{-- Status & Countdown & End button --}}
            <div class="flex items-center gap-4">
                {{-- Indikator kehadiran expert --}}
                <div id="expert-status" class="flex items-center gap-1.5 text-xs">
                    @if($booking->expert_joined)
                        <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
                        <span class="text-teal-300 font-medium">Expert Online</span>
                    @else
                        <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                        <span class="text-amber-300 font-medium">Menunggu Expert...</span>
                    @endif
                </div>

                {{-- Countdown gembok kehadiran 10 menit --}}
                @if($secondsRemaining !== null)
                <div id="countdown-wrap" class="bg-white/10 rounded-lg px-3 py-1.5 text-center">
                    <p class="text-[10px] text-blue-300 leading-none mb-0.5">Tenggat Hadir</p>
                    <p id="countdown" class="text-sm font-bold tabular-nums leading-none">
                        {{ gmdate('i:s', $secondsRemaining) }}
                    </p>
                </div>
                @endif

                {{-- Tombol Akhiri Konsultasi --}}
                @if(!in_array($booking->status, ['completed', 'pending_settlement']))
                <form id="end-session-form" action="{{ route('client.instant.end', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri sesi konsultasi instan ini?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Akhiri Konsultasi
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         BODY — Chat Box
    ═══════════════════════════════════════════════ --}}
    <div class="flex-1 max-w-4xl w-full mx-auto px-4 py-4 flex flex-col" style="height: calc(100vh - 130px)">

        {{-- Flash message --}}
        @if(session('success'))
        <div class="mb-3 p-3 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-700">
            {{ session('success') }}
        </div>
        @endif

        {{-- Notifikasi sistem --}}
        @if(!$booking->expert_joined)
        <div class="mb-3 flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Kami telah memberi tahu Expert. Mohon tunggu — jika Expert tidak hadir dalam batas waktu, dana Anda akan dikembalikan 100%.</span>
        </div>
        @endif

        {{-- ─── KOTAK CHAT ─── --}}
        <div id="chat-box"
             class="flex-1 overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3 mb-3">

            {{-- Pesan awal sistem --}}
            <div class="flex justify-center">
                <span class="text-[11px] text-slate-400 bg-slate-50 border border-slate-100 rounded-full px-3 py-1">
                    Sesi dimulai — {{ \Carbon\Carbon::parse($booking->consultation->started_at ?? now())->format('d M Y, H:i') }}
                </span>
            </div>

            {{-- Render pesan yang sudah ada (server-side) --}}
            @foreach($messages as $msg)
                @php $isOwn = $msg->sender_id === auth()->id(); @endphp
                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md">
                        <div @class([
                            'px-4 py-2.5 rounded-2xl text-sm shadow-sm',
                            'bg-blue-900 text-white rounded-tr-none' => $isOwn,
                            'bg-slate-100 text-slate-800 rounded-tl-none' => !$isOwn,
                        ])>
                            {{ $msg->message }}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 {{ $isOwn ? 'text-right' : 'text-left' }}">
                            {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- INPUT PESAN ATAU BANNER READ-ONLY --}}
        @if(in_array($booking->status, ['completed', 'pending_settlement']))
            <div class="p-4 bg-slate-100 border border-slate-200 rounded-2xl text-center flex-shrink-0 flex items-center justify-between gap-4">
                <div class="text-left">
                    <p class="text-sm font-semibold text-slate-700">Sesi Konsultasi Telah Selesai</p>
                    <p class="text-xs text-slate-500">Ruang chat ini sekarang dalam mode Baca-Saja (Read-Only).</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.booking.pdf', $booking->id) }}" class="px-4 py-2 bg-blue-900 hover:bg-indigo-900 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                        Unduh Resume (PDF)
                    </a>
                    <a href="{{ route('client.instant.result', $booking->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition shadow-sm">
                        Kembali ke Hasil
                    </a>
                </div>
            </div>
        @else
            <div id="input-container" class="flex gap-2 items-end flex-shrink-0">
                <div class="flex-1 bg-white border border-slate-200 rounded-2xl shadow-sm flex items-end gap-2 px-4 py-2.5">
                    <textarea id="msg-input" rows="1"
                              placeholder="Tulis pesan konsultasi..."
                              onkeydown="handleEnter(event)"
                              class="flex-1 resize-none text-sm focus:outline-none text-slate-800 placeholder-slate-400 max-h-32"
                              style="min-height: 24px;"></textarea>
                </div>
                <button onclick="sendMessage()"
                        id="send-btn"
                        class="flex-shrink-0 w-11 h-11 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl shadow-sm transition flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
            <div id="readonly-banner" class="hidden p-4 bg-slate-100 border border-slate-200 rounded-2xl text-center flex-shrink-0 flex items-center justify-between gap-4">
                <div class="text-left">
                    <p class="text-sm font-semibold text-slate-700">Sesi Konsultasi Telah Selesai</p>
                    <p class="text-xs text-slate-500">Ruang chat ini sekarang dalam mode Baca-Saja (Read-Only).</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.booking.pdf', $booking->id) }}" class="px-4 py-2 bg-blue-900 hover:bg-indigo-900 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                        Unduh Resume (PDF)
                    </a>
                    <a href="{{ route('client.instant.result', $booking->id) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition shadow-sm">
                        Kembali ke Hasil
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
// ──────────────────────────────────────────────────────────────
// KONFIGURASI
// ──────────────────────────────────────────────────────────────
const BOOKING_ID    = {{ $booking->id }};
const AUTH_ID       = {{ auth()->id() }};
const STATUS_URL    = "{{ route('client.instant.status', $booking->id) }}";
const MESSAGE_URL   = "{{ route('client.instant.message', $booking->id) }}";
const RESULT_URL    = "{{ route('client.instant.result', $booking->id) }}";
const CSRF_TOKEN    = "{{ csrf_token() }}";

// Lacak ID pesan terakhir — hanya minta pesan baru, bukan semua ulang
let lastMessageId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};

// Sisa detik countdown (null = sudah tidak ada deadline)
let secs = {{ $secondsRemaining !== null ? $secondsRemaining : 'null' }};

// ──────────────────────────────────────────────────────────────
// COUNTDOWN TIMER GEMBOK KEHADIRAN
// ──────────────────────────────────────────────────────────────
const countdownEl = document.getElementById('countdown');
if (secs !== null && countdownEl) {
    const timerInterval = setInterval(() => {
        if (secs <= 0) {
            clearInterval(timerInterval);
            countdownEl.textContent = '00:00';
            // Saat habis, beri jeda 2 detik sebelum redirect — polling akan tangkap lebih cepat
            return;
        }
        secs--;
        const m = Math.floor(secs / 60).toString().padStart(2, '0');
        const s = (secs % 60).toString().padStart(2, '0');
        countdownEl.textContent = m + ':' + s;

        // Ubah warna jadi merah jika sisa < 2 menit
        if (secs < 120) {
            countdownEl.classList.add('text-red-300');
        }
    }, 1000);
}

// ──────────────────────────────────────────────────────────────
// RENDER SATU BUBBLE PESAN KE CHAT BOX
// ──────────────────────────────────────────────────────────────
function appendMessage(msg) {
    const box  = document.getElementById('chat-box');
    const wrap = document.createElement('div');
    wrap.className = 'flex ' + (msg.is_own ? 'justify-end' : 'justify-start');

    const isOwnBubble = msg.is_own
        ? 'bg-blue-900 text-white rounded-tr-none'
        : 'bg-slate-100 text-slate-800 rounded-tl-none';

    wrap.innerHTML = `
        <div class="max-w-xs lg:max-w-md">
            <div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm ${isOwnBubble}">
                ${escapeHtml(msg.message)}
            </div>
            <p class="text-[10px] text-slate-400 mt-1 ${msg.is_own ? 'text-right' : 'text-left'}">
                ${msg.time || msg.created_at || ''}
            </p>
        </div>
    `;
    box.appendChild(wrap);
    // Auto-scroll ke bawah setelah append
    box.scrollTop = box.scrollHeight;

    // Update last message ID untuk polling berikutnya
    if (msg.id && msg.id > lastMessageId) {
        lastMessageId = msg.id;
    }
}

// Escape HTML agar XSS tidak bisa masuk via pesan chat
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ──────────────────────────────────────────────────────────────
// KIRIM PESAN (AJAX POST)
// ──────────────────────────────────────────────────────────────
async function sendMessage() {
    const input  = document.getElementById('msg-input');
    const text   = input.value.trim();
    if (!text) return;

    // Kosongkan input & nonaktifkan tombol sementara
    input.value = '';
    document.getElementById('send-btn').disabled = true;

    try {
        const res = await fetch(MESSAGE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        });

        if (res.ok) {
            const data = await res.json();
            // Tampilkan pesan milik sendiri langsung tanpa tunggu polling
            appendMessage({ ...data, is_own: true });
        } else {
            alert('Gagal mengirim pesan. Coba lagi.');
        }
    } catch (err) {
        alert('Koneksi error. Periksa internet Anda.');
    } finally {
        document.getElementById('send-btn').disabled = false;
        input.focus();
    }
}

// Kirim dengan Enter (Shift+Enter = baris baru)
function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// ──────────────────────────────────────────────────────────────
// AJAX POLLING — setiap 4 detik
// Cek: status booking + pesan baru dari expert
// ──────────────────────────────────────────────────────────────
// Web Audio API sound alert chime
function playNotificationSound() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        const osc1 = audioCtx.createOscillator();
        const osc2 = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
        osc1.frequency.exponentialRampToValueAtTime(659.25, audioCtx.currentTime + 0.1); // E5
        
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.1); // E5
        osc2.frequency.exponentialRampToValueAtTime(783.99, audioCtx.currentTime + 0.2); // G5
        
        gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
        
        osc1.connect(gainNode);
        osc2.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        osc1.start();
        osc2.start(audioCtx.currentTime + 0.08);
        
        osc1.stop(audioCtx.currentTime + 0.35);
        osc2.stop(audioCtx.currentTime + 0.35);
    } catch (e) {
        console.warn('AudioContext failed:', e);
    }
}

// ──────────────────────────────────────────────────────────────
// AJAX POLLING — setiap 4 detik
// Cek: status booking + pesan baru dari expert
// ──────────────────────────────────────────────────────────────
async function pollStatus() {
    try {
        const res = await fetch(`${STATUS_URL}?last_id=${lastMessageId}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) return;
        const data = await res.json();

        // 1. Jika booking dibatalkan → redirect ke hasil
        if (data.redirect_to_result) {
            window.location.href = RESULT_URL;
            return;
        }

        // Handle dynamic transition to Read-Only mode
        if (data.status === 'completed' || data.status === 'pending_settlement') {
            const inputContainer = document.getElementById('input-container');
            const readonlyBanner = document.getElementById('readonly-banner');
            const endSessionForm = document.getElementById('end-session-form');
            if (inputContainer) inputContainer.classList.add('hidden');
            if (readonlyBanner) readonlyBanner.classList.remove('hidden');
            if (endSessionForm) endSessionForm.classList.add('hidden');
        }

        // 2. Update status kehadiran expert di header
        if (data.expert_joined) {
            const statusEl = document.getElementById('expert-status');
            if (statusEl && statusEl.dataset.joined !== '1') {
                statusEl.dataset.joined = '1';
                statusEl.innerHTML = `
                    <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
                    <span class="text-teal-300 font-medium">Expert Online</span>
                `;
                // Sembunyikan countdown jika kedua pihak sudah hadir
                const wrap = document.getElementById('countdown-wrap');
                if (wrap) wrap.classList.add('hidden');
            }
        }

        // 3. Update countdown dari server (sinkronisasi anti-drift)
        if (data.seconds_remaining !== null && secs !== null) {
            secs = data.seconds_remaining;
        }

        // 4. Render pesan baru yang datang dari expert
        if (data.new_messages && data.new_messages.length > 0) {
            // Play notification sound only if there are new incoming messages from expert
            const hasIncoming = data.new_messages.some(msg => msg.sender_id !== AUTH_ID);
            if (hasIncoming) {
                playNotificationSound();
            }
            data.new_messages.forEach(msg => appendMessage(msg));
        }

    } catch (err) {
        // Abaikan error jaringan — polling akan coba lagi 4 detik kemudian
        console.warn('Polling error:', err);
    }
}

// Mulai polling
setInterval(pollStatus, 4000);

// Scroll ke pesan terakhir saat halaman pertama kali load
document.addEventListener('DOMContentLoaded', () => {
    const box = document.getElementById('chat-box');
    box.scrollTop = box.scrollHeight;
});
</script>
@endpush
@endsection
