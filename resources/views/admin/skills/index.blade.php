<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Skill</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">

<div class="max-w-5xl mx-auto bg-white rounded-2xl shadow p-8">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Daftar Skill
            </h1>
            <p class="text-sm text-gray-500">
                Kelola skill spesialisasi ahli
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}"
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah Skill
        </a>
    </div>

    <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Nama Skill</th>
                <th class="p-3 text-center">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($skills as $skill)
            <tr class="border-t">
                <td class="p-3">{{ $skill->id }}</td>

                <td class="p-3">
                    {{ $skill->name }}
                </td>

                <td class="p-3 text-center">
                    <div class="flex justify-center gap-2">

                        <a href="{{ route('admin.skills.edit', $skill->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form action="{{ route('admin.skills.destroy', $skill->id) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                Hapus
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="{{ route('admin.dashboard') }}"
           class="text-blue-500 hover:underline">
            ← Kembali ke Dashboard
        </a>
    </div>

</div>

</body>
</html>