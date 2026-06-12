Set-Content -Path "resources\views\expert\dashboard.blade.php" -Encoding UTF8 -Value @'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Dashboard Expert</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 p-8">
<div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-8 text-center">
    <div class="text-4xl mb-4">🎓</div>
    <h1 class="text-xl font-bold text-gray-800">Halo, {{ auth()->user()->profile->name ?? auth()->user()->username }}!</h1>
    <p class="text-gray-500 mt-2 text-sm">Dashboard Expert</p>
    @if(auth()->user()->expertProfile)
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
        Status: <strong>{{ ucfirst(auth()->user()->expertProfile->verification_status) }}</strong>
        &nbsp;|&nbsp; Level: <strong>{{ ucfirst(auth()->user()->expertProfile->commission_level) }}</strong>
    </div>
    @endif
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="text-sm text-red-500 hover:underline">Logout</button>
    </form>
</div>
</body>
</html>
'@