<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">
    <a href="{{ route('admin.dashboard') }}"
    class="bg-gray-500 text-white px-4 py-2 rounded">
    ← Dashboard
    </a>
    <h1 class="text-3xl font-bold mb-2">
        💳 Monitoring Payment
    </h1>

    <p class="text-gray-500 mb-6">
        Monitoring transaksi pembayaran pengguna
    </p>

    <table class="w-full border-collapse">

        <thead>
            <tr class="bg-gray-100">
                <th class="border p-3 text-left">ID</th>
                <th class="border p-3 text-left">Nominal</th>
                <th class="border p-3 text-left">Status</th>
            </tr>
        </thead>

        <tbody>

        @forelse($payments as $payment)

            <tr class="hover:bg-gray-50">

                <td class="border p-3">
                    {{ $payment->id }}
                </td>

                <td class="border p-3">
                    Rp {{ number_format($payment->amount,0,',','.') }}
                </td>

                <td class="border p-3">

                    @if($payment->status == 'paid')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                            Paid
                        </span>

                    @elseif($payment->status == 'pending')
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                            Pending
                        </span>

                    @else
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                            Failed
                        </span>
                    @endif

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="3" class="border p-4 text-center text-gray-500">
                    Belum ada data pembayaran
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

</body>
</html>