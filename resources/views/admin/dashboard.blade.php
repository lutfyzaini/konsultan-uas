<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto py-10 px-6">

    <div class="bg-white rounded-2xl shadow p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            🛡️ Admin Panel
        </h1>

        <p class="text-gray-500 mt-2">
            Selamat datang, {{ auth()->user()->username }}
        </p>
    </div>

    <div class="grid md:grid-cols-4 gap-4 mb-8">

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <div class="text-sm text-amber-700">
                Expert Pending
            </div>

            <div class="text-3xl font-bold text-amber-800 mt-2">
                {{ \App\Models\ExpertProfile::where('verification_status','pending')->count() }}
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <div class="text-sm text-blue-700">
                Total User
            </div>

            <div class="text-3xl font-bold text-blue-800 mt-2">
                {{ \App\Models\User::count() }}
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <div class="text-sm text-green-700">
                Total Kategori
            </div>

            <div class="text-3xl font-bold text-green-800 mt-2">
                {{ \App\Models\Category::count() }}
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
            <div class="text-sm text-purple-700">
                Total Skill
            </div>

            <div class="text-3xl font-bold text-purple-800 mt-2">
                {{ \App\Models\Skill::count() }}
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl shadow p-6">

        <h2 class="text-xl font-semibold mb-4">
            Menu Administrasi
        </h2>

        <div class="grid md:grid-cols-3 gap-4">

            <a href="{{ route('admin.categories.index') }}"
               class="bg-blue-500 hover:bg-blue-600 text-white p-5 rounded-xl text-center">

                📂 Kelola Kategori
            </a>

            <a href="{{ route('admin.skills.index') }}"
               class="bg-green-500 hover:bg-green-600 text-white p-5 rounded-xl text-center">

                🛠️ Kelola Skill
            </a>

            <a href="{{ route('admin.verifications.index') }}"
            class="bg-yellow-500 hover:bg-yellow-600 text-white p-5 rounded-xl text-center">

                ✅ Verifikasi Ahli

            </a>

        </div>

        <form method="POST"
              action="{{ route('logout') }}"
              class="mt-8">
            @csrf

            <button
                class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg">
                Logout
            </button>
        </form>

    </div>

</div>

</body>
</html>