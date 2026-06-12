Set-Content -Path "resources\views\admin\dashboard.blade.php" -Encoding UTF8 -Value @'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Admin Panel</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 p-8">
<div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-8 text-center">
    <div class="text-4xl mb-4">🛡️</div>
    <h1 class="text-xl font-bold text-gray-800">Admin Panel</h1>
    <p class="text-gray-500 mt-2 text-sm">Halo, {{ auth()->user()->username }}!</p>
    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-700">
            Expert Pending<br>
            <strong class="text-2xl">{{ \App\Models\ExpertProfile::where('verification_status','pending')->count() }}</strong>
        </div>
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-700">
            Total User<br>
            <strong class="text-2xl">{{ \App\Models\User::count() }}</strong>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="text-sm text-red-500 hover:underline">Logout</button>
    </form>
</div>
</body>
</html>
'@