<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Skill — E-Konsul</title>
    {{-- Google Fonts: Inter + Plus Jakarta Sans --}}
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

    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation Header --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            
            {{-- Header Title and Add Button --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-6 mb-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        🛠️ Kelola Skill
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">
                        Daftar keahlian spesifik ahli untuk membantu penyaringan pencarian klien.
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.skills.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-900 hover:bg-emerald-850 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                        <span>+</span> Tambah Skill
                    </a>
                </div>
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

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 text-left font-semibold w-24">ID</th>
                            <th class="p-4 text-left font-semibold">Nama Skill</th>
                            <th class="p-4 text-center font-semibold w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($skills as $skill)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- ID Column --}}
                                <td class="p-4 text-sm font-bold text-slate-400">
                                    #{{ $skill->id }}
                                </td>

                                {{-- Name Column --}}
                                <td class="p-4 text-sm font-bold text-slate-900">
                                    {{ $skill->name }}
                                </td>

                                {{-- Action Column --}}
                                <td class="p-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('admin.skills.edit', $skill->id) }}" class="px-3 py-1.5 bg-white border border-slate-200 hover:border-amber-300 text-slate-600 hover:text-amber-700 font-bold text-xs rounded-lg transition shadow-sm">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skill ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-white border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 font-bold text-xs rounded-lg transition shadow-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-400 italic text-sm">
                                    Belum ada data skill terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>