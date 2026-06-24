<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Klien - E-Konsul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-900 to-indigo-700 bg-clip-text text-transparent">E-Konsul</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Expert Panel</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('expert.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-all">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        
        <!-- Page Title & Navigation -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Ulasan Klien</h1>
                <p class="text-sm text-slate-400 mt-1">Daftar ulasan dan penilaian yang diberikan oleh klien Anda.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Average Rating Card -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 text-white/10 text-9xl font-bold pointer-events-none">⭐</div>
                <p class="text-xs text-white/80 uppercase tracking-wider font-semibold">Rata-rata Rating</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <h3 class="text-4xl font-extrabold">{{ number_format($averageRating, 1) }}</h3>
                    <span class="text-sm text-amber-100 font-medium">/ 5.0</span>
                </div>
                <div class="flex items-center gap-1 mt-4">
                    @php $stars = round($averageRating); @endphp
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= $stars ? 'text-white fill-current' : 'text-white/30 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span class="text-xs text-amber-100 ml-2 font-medium">Berdasarkan data ulasan masuk</span>
                </div>
            </div>

            <!-- Total Reviews Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Ulasan</p>
                    <h3 class="text-4xl font-extrabold text-slate-800 mt-2">{{ $totalReviews }}</h3>
                </div>
                <p class="text-xs text-slate-400 mt-4 border-t border-slate-100 pt-4 flex items-center gap-1.5">
                    <span>💬</span>
                    Umpan balik membantu meningkatkan kualitas konsultasi Anda.
                </p>
            </div>
        </div>

        <!-- Reviews List Section -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 text-lg mb-6">Daftar Feedback Klien</h3>

            @if($reviews->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        ⭐
                    </div>
                    <p class="text-slate-400 text-sm font-medium">Belum ada klien yang memberikan ulasan untuk Anda.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($reviews as $review)
                        @php
                            $client = $review->booking->client ?? null;
                            $profile = $client?->profile ?? null;
                            $name = $profile?->name ?? 'Klien E-Konsul';
                            
                            $avatarRaw = $profile?->avatar_url ?? null;
                            $avatarUrl = $avatarRaw
                                ? asset($avatarRaw)
                                : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1e3a5f&color=fff&size=96&bold=true';
                        @endphp
                        
                        <div class="flex gap-4 p-5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-all">
                            <!-- Avatar Client -->
                            <img src="{{ $avatarUrl }}" 
                                 alt="{{ $name }}" 
                                 class="w-12 h-12 rounded-full object-cover flex-shrink-0 border border-slate-200 shadow-sm"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=1e3a5f&color=fff&size=96&bold=true'">

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                                    <div>
                                        <h4 class="font-semibold text-slate-800 text-sm truncate">
                                            {{ $name }}
                                        </h4>
                                        <p class="text-[10px] text-slate-400">
                                            Konsultasi selesai pada {{ $review->created_at->translatedFormat('d F Y, H:i') }} WIB
                                        </p>
                                    </div>
                                    <!-- Stars representation -->
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="text-sm text-slate-600 leading-relaxed break-words">
                                        {{ $review->comment }}
                                    </p>
                                @else
                                    <p class="text-xs text-slate-400 italic">
                                        Tidak ada komentar tertulis.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="mt-8 border-t border-slate-100 pt-6">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>

    </div>

</body>
</html>
