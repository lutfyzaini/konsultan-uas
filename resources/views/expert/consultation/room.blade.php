@extends('layouts.app')
@section('title', 'Ruang Konsultasi — ' . ($booking->client->profile->name ?? 'Klien'))

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col">

    {{-- HEADER — Info Klien & Status Sesi --}}
    <div class="bg-blue-900 text-white shadow-lg flex-shrink-0">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

            {{-- Avatar + Info Klien --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('expert.dashboard') }}" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-all font-semibold mr-1" title="Kembali ke Dashboard">
                    ←
                </a>
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($booking->client->profile->name ?? 'XX', 0, 2)) }}
                </div>
                <div>
                    <p class="font-semibold text-sm leading-tight">
                        Klien: {{ $booking->client->profile->name ?? 'Klien' }}
                    </p>
                    <p class="text-blue-300 text-xs">
                        Tipe Sesi: {{ ucfirst($booking->booking_type) }}
                    </p>
                </div>
            </div>

            {{-- Status & Countdown & Settle button --}}
            <div class="flex items-center gap-4">
                
                {{-- Indikator Kehadiran Klien --}}
                <div id="client-status" class="flex items-center gap-1.5 text-xs">
                    @if($booking->client_joined)
                        <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
                        <span class="text-teal-300 font-medium">Klien Online</span>
                    @else
                        <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                        <span class="text-amber-300 font-medium">Menunggu Klien...</span>
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

                {{-- Tombol Akhiri Sesi --}}
                <form action="{{ route('expert.consultation.end', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri sesi konsultasi ini?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Akhiri Sesi
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- BODY — Chat Box --}}
    <div class="flex-1 max-w-4xl w-full mx-auto px-4 py-4 flex flex-col" style="height: calc(100vh - 130px)">

        @if(session('success'))
        <div class="mb-3 p-3 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-700">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-3 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700">
            {{ session('error') }}
        </div>
        @endif

        {{-- Notifikasi Klien Belum Hadir --}}
        @if(!$booking->client_joined)
        <div class="mb-3 flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Klien belum memasuki ruang chat. Mohon tunggu. Jika klien tidak masuk setelah batas waktu, sesi akan dibatalkan otomatis dan Anda mendapat kompensasi penuh.</span>
        </div>
        @endif

        {{-- CHAT BOX --}}
        <div id="chat-box"
             class="flex-1 overflow-y-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3 mb-3">

            <div class="flex justify-center">
                <span class="text-[11px] text-slate-400 bg-slate-50 border border-slate-100 rounded-full px-3 py-1">
                    Sesi dimulai — {{ \Carbon\Carbon::parse($booking->consultation->started_at ?? now())->format('d M Y, H:i') }}
                </span>
            </div>

            {{-- Render pesan server-side --}}
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

        {{-- INPUT PESAN --}}
        <div class="flex gap-2 items-end flex-shrink-0">
            <div class="flex-1 bg-white border border-slate-200 rounded-2xl shadow-sm flex items-end gap-2 px-4 py-2.5">
                <textarea id="msg-input" rows="1"
                          placeholder="Tulis pesan respon konsultasi..."
                          onkeydown="handleEnter(event)"
                          class="flex-1 resize-none text-sm focus:outline-none text-slate-800 placeholder-slate-400 max-h-32"
                          style="min-height: 24px;"></textarea>
            </div>
            <button onclick="sendMessage()"
                    id="send-btn"
                    class="flex-shrink-0 w-11 h-11 bg-blue-900 hover:bg-indigo-900 active:scale-95 text-white rounded-xl shadow-sm transition flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const BOOKING_ID    = {{ $booking->id }};
const AUTH_ID       = {{ auth()->id() }};
const STATUS_URL    = "{{ route('expert.consultation.status', $booking->id) }}";
const MESSAGE_URL   = "{{ route('expert.consultation.message', $booking->id) }}";
const REDIRECT_URL  = "{{ route('expert.dashboard') }}";
const CSRF_TOKEN    = "{{ csrf_token() }}";

let lastMessageId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
let secs = {{ $secondsRemaining !== null ? $secondsRemaining : 'null' }};

// Countdown Timer Kehadiran
const countdownEl = document.getElementById('countdown');
if (secs !== null && countdownEl) {
    const timerInterval = setInterval(() => {
        if (secs <= 0) {
            clearInterval(timerInterval);
            countdownEl.textContent = '00:00';
            return;
        }
        secs--;
        const m = Math.floor(secs / 60).toString().padStart(2, '0');
        const s = (secs % 60).toString().padStart(2, '0');
        countdownEl.textContent = m + ':' + s;

        if (secs < 120) {
            countdownEl.classList.add('text-red-300');
        }
    }, 1000);
}

// Append bubble chat
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
    box.scrollTop = box.scrollHeight;

    if (msg.id && msg.id > lastMessageId) {
        lastMessageId = msg.id;
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Kirim pesan AJAX
async function sendMessage() {
    const input  = document.getElementById('msg-input');
    const text   = input.value.trim();
    if (!text) return;

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
            appendMessage({ ...data, is_own: true });
        } else {
            alert('Gagal mengirim pesan.');
        }
    } catch (err) {
        alert('Koneksi bermasalah.');
    } finally {
        document.getElementById('send-btn').disabled = false;
        input.focus();
    }
}

function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Polling status dan pesan baru
async function pollStatus() {
    try {
        const res = await fetch(`${STATUS_URL}?last_id=${lastMessageId}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) return;
        const data = await res.json();

        if (data.redirect_to_result) {
            window.location.href = REDIRECT_URL;
            return;
        }

        if (data.client_joined) {
            const statusEl = document.getElementById('client-status');
            if (statusEl && statusEl.dataset.joined !== '1') {
                statusEl.dataset.joined = '1';
                statusEl.innerHTML = `
                    <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
                    <span class="text-teal-300 font-medium">Klien Online</span>
                `;
                const wrap = document.getElementById('countdown-wrap');
                if (wrap) wrap.classList.add('hidden');
            }
        }

        if (data.seconds_remaining !== null && secs !== null) {
            secs = data.seconds_remaining;
        }

        if (data.new_messages && data.new_messages.length > 0) {
            data.new_messages.forEach(msg => appendMessage(msg));
        }

    } catch (err) {
        console.warn('Polling error:', err);
    }
}

setInterval(pollStatus, 4000);

document.addEventListener('DOMContentLoaded', () => {
    const box = document.getElementById('chat-box');
    box.scrollTop = box.scrollHeight;
});
</script>
@endpush
