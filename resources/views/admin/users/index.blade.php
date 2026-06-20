<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">
    <a href="{{ route('admin.dashboard') }}"
    class="bg-gray-500 text-white px-4 py-2 rounded">
    ← Dashboard
    </a>
    <h1 class="text-3xl font-bold mb-2">
        👥 User Management
    </h1>

    <p class="text-gray-500 mb-6">
        Kelola akun pengguna platform
    </p>

    <table class="w-full border-collapse">

        <thead>
            <tr class="bg-gray-100">
                <th class="border p-3 text-left">Nama</th>
                <th class="border p-3 text-left">Email</th>
                <th class="border p-3 text-left">Role</th>
                <th class="border p-3 text-left">Status</th>
                <th class="border p-3 text-center">Aksi</th>
            </tr>
        </thead>

        <tbody>

        @foreach($users as $user)

            <tr class="hover:bg-gray-50">

                <td class="border p-3">
                    {{ $user->username }}
                </td>

                <td class="border p-3">
                    {{ $user->email }}
                </td>

                <td class="border p-3">

                    @if($user->role == 'admin')
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                            Admin
                        </span>

                    @elseif($user->role == 'expert')
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
                            Expert
                        </span>

                    @else
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                            Client
                        </span>
                    @endif

                </td>

                <td class="border p-3">

                    @if($user->is_active)
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                            Aktif
                        </span>
                    @else
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                            Suspend
                        </span>
                    @endif

                </td>

                <td class="border p-3 text-center">

                    <form method="POST"
                          action="{{ route('admin.users.toggle',$user) }}">
                        @csrf

                        <button
                            class="{{ $user->is_active
                                ? 'bg-red-500 hover:bg-red-600'
                                : 'bg-green-500 hover:bg-green-600' }}
                                text-white px-4 py-2 rounded-lg">

                            {{ $user->is_active ? 'Suspend' : 'Aktifkan' }}

                        </button>

                    </form>

                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

</body>
</html>