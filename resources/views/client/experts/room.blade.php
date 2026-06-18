@extends('layouts.app')
@section('title', 'Ruang Konsultasi')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Header Ruangan --}}
        <div class="bg-blue-900 p-4 text-white flex items-center justify-between">
            <div>
                <h2 class="font-bold text-lg">Sesi Bersama: {{ $expert->name }}</h2>
                <p class="text-xs text-slate-300">Tenggat Kehadiran: {{ \Carbon\Carbon::parse($consultation->attendance_deadline)->format('H:i:s') }} WIB</p>
            </div>
            <div class="px-3 py-1 bg-teal-600 rounded-full text-xs font-bold animate-pulse">
                Sesi Aktif
            </div>
        </div>

        {{-- Box Obrolan --}}
        <div class="p-6 h-96 overflow-y-auto bg-slate-50 space-y-4" id="chat-box">
            <div class="text-center p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl max-w-md mx-auto">
                Sistem mendeteksi kehadiran. Jika salah satu pihak tidak mengirimkan pesan dalam waktu 10 menit sejak sesi dibuat, konsultasi otomatis berakhir.
            </div>

            @foreach($messages as $msg)
                <div @class([
                    'flex flex-col max-w-xs rounded-2xl p-3 text-sm shadow-sm',
                    'bg-blue-900 text-white ml-auto rounded-tr-none' => $msg->sender_id == auth()->id(),
                    'bg-white text-slate-800 rounded-tl-none border border-slate-200' => $msg->sender_id != auth()->id(),
                ])>
                    <p>{{ $msg->message }}</p>
                    <span class="text-[10px] opacity-60 mt-1 block text-right">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</span>
                </div>
            @endforeach
        </div>

        {{-- Form Kirim Pesan Sederhana (Simulasi) --}}
        <div class="p-4 bg-white border-t border-slate-100 flex gap-2">
            <input type="text" placeholder="Tulis pesan konsultasi..." class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-900">
            <button class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl transition">
                Kirim
            </button>
        </div>
    </div>
</div>

<script>
    // Auto-scroll chat box ke bawah
    const chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;

    // Polling setiap 5 detik untuk cek status no-show dari background scheduler
    setInterval(() => {
        window.location.reload();
    }, 5000);
</script>
@endsection