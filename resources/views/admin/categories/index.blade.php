<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kategori</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">

<div class="max-w-5xl mx-auto bg-white rounded-2xl shadow p-8">

    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('admin.dashboard') }}"
            class="bg-gray-500 text-white px-4 py-2 rounded">
            ← Dashboard
            </a>
            <h1 class="text-2xl font-bold text-gray-800">
                Daftar Kategori
            </h1>
            <p class="text-sm text-gray-500">
                Kelola kategori spesialisasi ahli
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}"
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah Kategori
        </a>
    </div>

    <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Nama Kategori</th>
                <th class="p-3 text-center">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($categories as $category)
            <tr class="border-t">
                <td class="p-3">{{ $category->id }}</td>

                <td class="p-3">
                    {{ $category->name }}
                </td>

                <td class="p-3 text-center">
                    <div class="flex justify-center gap-2">

                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form action="{{ route('admin.categories.destroy', $category->id) }}"
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



</div>

</body>
</html>