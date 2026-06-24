<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Sesi Konsultasi — E-Konsul</title>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px border #e2e8f0;
        }
        .header {
            background-color: #1e3a8a;
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            color: #93c5fd;
        }
        .content {
            padding: 32px 24px;
        }
        .welcome-text {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }
        .card-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .card-row:first-child {
            padding-top: 0;
        }
        .label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        .value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
        .btn-container {
            text-align: center;
            margin-top: 32px;
        }
        .btn {
            display: inline-block;
            background-color: #d97706;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.2);
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #b45309;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

<div class="container">
    {{-- Header --}}
    <div class="header">
        <h1>E-Konsul</h1>
        <p>Solusi Konsultasi Pakar Online Anda</p>
    </div>

    {{-- Content --}}
    <div class="content">
        <div class="welcome-text">
            @if($role === 'client')
                <p>Halo <strong>{{ $booking->client->profile->name ?? 'Klien' }}</strong>,</p>
                <p>Ini adalah pengingat bahwa sesi konsultasi terjadwal Anda akan segera dimulai dalam **30 menit**. Silakan bersiap dan masuk ke ruang chat tepat waktu.</p>
            @else
                <p>Halo Pakar <strong>{{ $booking->expertProfile->user->profile->name ?? 'Expert' }}</strong>,</p>
                <p>Ini adalah pengingat bahwa Anda memiliki sesi konsultasi terjadwal dengan klien yang akan segera dimulai dalam **30 menit**. Silakan bersiap dan masuk ke ruang chat tepat waktu.</p>
            @endif
        </div>

        {{-- Details Card --}}
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 0; font-size: 13px; color: #64748b; font-weight: 500;">Pakar</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #0f172a; font-weight: 600; text-align: right;">
                        {{ $booking->expertProfile->user->profile->name ?? 'Pakar' }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 0; font-size: 13px; color: #64748b; font-weight: 500;">Klien</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #0f172a; font-weight: 600; text-align: right;">
                        {{ $booking->client->profile->name ?? 'Klien' }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 0; font-size: 13px; color: #64748b; font-weight: 500;">Jadwal Tanggal</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #0f172a; font-weight: 600; text-align: right;">
                        {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-size: 13px; color: #64748b; font-weight: 500;">Waktu Sesi</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #0f172a; font-weight: 600; text-align: right; color: #1e3a8a;">
                        {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WIB
                    </td>
                </tr>
            </table>
        </div>

        {{-- Call To Action Button --}}
        <div class="btn-container">
            @php
                $url = $role === 'client'
                    ? route('client.booking.room', $booking->id)
                    : route('expert.consultation.room', $booking->id);
            @endphp
            <a href="{{ $url }}" class="btn">
                Masuk ke Ruang Chat Konsultasi
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Email ini dikirimkan secara otomatis oleh sistem E-Konsul.</p>
        <p>&copy; {{ date('Y') }} E-Konsul. All rights reserved.</p>
    </div>
</div>

</body>
</html>
