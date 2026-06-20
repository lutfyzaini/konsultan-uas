<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Ahli</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white rounded-2xl shadow p-6">

    <h1 class="text-2xl font-bold mb-6">
        Verifikasi Ahli
    </h1>

    <table class="w-full">

        <thead>
            <tr class="border-b">
                <th class="text-left p-3">Nama</th>
                <th class="text-left p-3">Email</th>
                <th class="text-left p-3">Status</th>
                <th class="text-center p-3">Aksi</th>
            </tr>
        </thead>

        <tbody>

        @foreach($experts as $expert)

        <tr class="border-b">

            <td class="p-3">
                {{ $expert->user->username }}
            </td>

            <td class="p-3">
                {{ $expert->user->email }}
            </td>

            <td class="p-3">
                {{ $expert->verification_status }}
            </td>

            <td class="p-3 text-center">

                @if($expert->verification_status == 'pending')

                    <div class="flex justify-center gap-2">

                        <form method="POST"
                              action="{{ route('admin.verifications.approve',$expert->id) }}">
                            @csrf
                            <button
                                class="bg-green-500 text-white px-3 py-1 rounded">
                                Approve
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.verifications.reject',$expert->id) }}">
                            @csrf
                            <button
                                class="bg-red-500 text-white px-3 py-1 rounded">
                                Reject
                            </button>
                        </form>

                    </div>

                @endif

            </td>

        </tr>

        @endforeach

        </tbody>

    </table>

</div>

</body>
</html>