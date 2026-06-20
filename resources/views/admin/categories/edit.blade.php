<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori — E-Konsul</title>
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

    <div class="max-w-xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation Header --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Kategori
            </a>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-6 mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                    ✏️ Edit Kategori
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Perbarui nama kategori bidang kepakaran terpilih.
                </p>
            </div>

            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Kategori</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $category->name) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-900 focus:bg-white transition @error('name') border-rose-500 @enderror">
                    
                    @error('name')
                        <p class="text-rose-600 text-xs font-bold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border border-slate-200 text-slate-500 hover:text-slate-700 font-semibold text-sm rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-900 hover:bg-blue-850 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                        Perbarui Kategori
                    </button>
                </div>

            </form>
        </div>

    </div>

</body>
</html>