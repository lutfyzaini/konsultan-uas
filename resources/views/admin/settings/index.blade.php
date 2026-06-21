<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Platform — E-Konsul</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation Header --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Settings Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            
            <div class="border-b border-slate-100 pb-6 mb-6">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    ⚙️ Pengaturan Platform
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Konfigurasi parameter bisnis, tarif platform, dan limit waktu sistem secara real-time.
                </p>
            </div>

            {{-- Flash Alert --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 rounded-xl text-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                
                @foreach($settings as $setting)
                    <div class="space-y-2">
                        <label for="setting-{{ $setting->key }}" class="block text-sm font-semibold text-slate-700">
                            {{ $setting->label ?? $setting->key }}
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <input 
                                type="text" 
                                name="settings[{{ $setting->key }}]" 
                                id="setting-{{ $setting->key }}" 
                                value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-blue-900 focus:outline-none focus:ring-1 focus:ring-blue-900 sm:text-sm"
                            >
                        </div>
                        <p class="text-xs text-slate-400">Key: <span class="font-mono">{{ $setting->key }}</span></p>
                    </div>
                @endforeach

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-blue-900 hover:bg-blue-800 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>

    </div>

</body>
</html>
