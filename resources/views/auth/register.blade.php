@extends('layouts.auth')
@section('title', 'Daftar')

@section('content')

<h2 class="text-xl font-semibold text-gray-800 mb-6">Buat akun baru</h2>

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    {{-- Pilih Role -- dipasang di atas agar user tahu konteks form --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Daftar sebagai</label>
        <div class="grid grid-cols-2 gap-3">

            <label class="relative cursor-pointer">
                <input type="radio" name="role" value="client"
                       class="peer sr-only" {{ old('role', 'client') === 'client' ? 'checked' : '' }}>
                <div class="border-2 rounded-xl p-3 text-center transition
                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 hover:border-gray-300">
                    <div class="text-2xl mb-1">🔍</div>
                    <div class="text-sm font-medium text-gray-700">Client</div>
                    <div class="text-xs text-gray-400">Cari & sewa expert</div>
                </div>
            </label>

            <label class="relative cursor-pointer">
                <input type="radio" name="role" value="expert"
                       class="peer sr-only" {{ old('role') === 'expert' ? 'checked' : '' }}>
                <div class="border-2 rounded-xl p-3 text-center transition
                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 hover:border-gray-300">
                    <div class="text-2xl mb-1">🎓</div>
                    <div class="text-sm font-medium text-gray-700">Expert</div>
                    <div class="text-xs text-gray-400">Tawarkan keahlian</div>
                </div>
            </label>

        </div>
        @error('role')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Nama Lengkap --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kamu"
               class="w-full px-4 py-2.5 rounded-lg border text-sm transition
                      @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Username --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm">@</span>
            <input type="text" name="username" value="{{ old('username') }}" placeholder="username_kamu"
                   class="w-full pl-7 pr-4 py-2.5 rounded-lg border text-sm transition
                          @error('username') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        @error('username')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
               class="w-full px-4 py-2.5 rounded-lg border text-sm transition
                      @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" placeholder="Minimal 8 karakter"
               class="w-full px-4 py-2.5 rounded-lg border text-sm transition
                      @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        @error('password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Konfirmasi Password --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" placeholder="Ulangi password"
               class="w-full px-4 py-2.5 rounded-lg border text-sm transition border-gray-300
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
    </div>

    {{-- Catatan untuk expert --}}
    <div id="expert-note" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-blue-700">
            ℹ️ Sebagai Expert, kamu perlu melengkapi profil dan mengunggah sertifikasi.
            Profil akan ditinjau oleh Admin sebelum bisa menerima pesanan.
        </p>
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4
                   rounded-lg transition duration-150 text-sm mt-2">
        Buat Akun
    </button>

</form>

<p class="text-center text-sm text-gray-600 mt-6">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="text-indigo-600 font-medium hover:underline">Masuk di sini</a>
</p>

<script>
// tampilkan catatan expert saat role expert dipilih
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.getElementById('expert-note').classList.toggle('hidden', this.value !== 'expert');
    });
});
// cek saat load (kalau old value = expert)
const checked = document.querySelector('input[name="role"]:checked');
if (checked?.value === 'expert') {
    document.getElementById('expert-note').classList.remove('hidden');
}
</script>

@endsection