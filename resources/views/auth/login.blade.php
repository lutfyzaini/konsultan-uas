@extends('layouts.auth')
@section('title', 'Login')

@section('content')

<h2 class="text-xl font-semibold text-gray-800 mb-6">Masuk ke akun kamu</h2>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    {{-- Email --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="nama@email.com"
            class="w-full px-4 py-2.5 rounded-lg border text-sm transition
                   @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 rounded-lg border text-sm transition
                       @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-10"
            >
            {{-- toggle show/hide password --}}
            <button type="button" onclick="togglePassword()"
                    class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
        @error('password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Remember me --}}
    <div class="flex items-center">
        <input type="checkbox" name="remember" id="remember"
               class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
        <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4
                   rounded-lg transition duration-150 text-sm mt-2">
        Masuk
    </button>

</form>

{{-- Divider --}}
<div class="my-6 flex items-center">
    <div class="flex-1 border-t border-gray-200"></div>
    <span class="px-3 text-xs text-gray-400">atau</span>
    <div class="flex-1 border-t border-gray-200"></div>
</div>

{{-- Register link --}}
<p class="text-center text-sm text-gray-600">
    Belum punya akun?
    <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Daftar sekarang</a>
</p>



<script>
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

@endsection