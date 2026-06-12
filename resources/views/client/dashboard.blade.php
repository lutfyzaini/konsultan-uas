Set-Content -Path "resources\views\client\dashboard.blade.php" -Encoding UTF8 -Value @'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Dashboard Client</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 p-8">
<div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-8 text-center">
    <div class="text-4xl mb-4">👋</div>
    <h1 class="text-xl font-bold text-gray-800">Halo, {{ auth()->user()->profile->name ?? auth()->user()->username }}!</h1>
    <p class="text-gray-500 mt-2 text-sm">Dashboard Client</p>
    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
        💰 Saldo: <strong>Rp {{ number_format(auth()->user()->wallet->balance ?? 0, 0, ',', '.') }}</strong>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="text-sm text-red-500 hover:underline">Logout</button>
    </form>
</div>
</body>
</html>
'@