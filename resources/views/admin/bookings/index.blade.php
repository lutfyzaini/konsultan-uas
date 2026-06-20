<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">
        <a href="{{ route('admin.dashboard') }}"
        class="bg-gray-500 text-white px-4 py-2 rounded">
        ← Dashboard
        </a>
    <h1 class="text-3xl font-bold mb-6">
        Monitoring Booking
    </h1>

    <table class="w-full border-collapse">

        <thead>
            <tr class="bg-gray-100">
                <th class="border p-3">ID</th>
                <th class="border p-3">Client</th>
                <th class="border p-3">Expert</th>
                <th class="border p-3">Status</th>
                <th class="border p-3">Tanggal</th>
            </tr>
        </thead>

        <tbody>
        @forelse($bookings as $booking)
            <tr>
                <td class="border p-3">{{ $booking->id }}</td>
                <td class="border p-3">{{ $booking->client_id }}</td>
                <td class="border p-3">{{ $booking->expert_id }}</td>
                <td class="border p-3">{{ $booking->status }}</td>
                <td class="border p-3">{{ $booking->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="border p-3 text-center text-gray-500">
                    Belum ada booking
                </td>
            </tr>
        @endforelse
        </tbody>

    </table>

</div>

</body>
</html>