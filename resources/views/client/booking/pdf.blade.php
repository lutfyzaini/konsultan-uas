<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resume Konsultasi #{{ $booking->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            padding: 10px;
        }
        .header {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .logo-tagline {
            font-size: 11px;
            color: #666666;
            margin-top: 2px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #111111;
            margin-top: 15px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-table tr:last-child td {
            border-bottom: none;
        }
        .meta-label {
            font-weight: bold;
            color: #4b5563;
            width: 30%;
        }
        .meta-value {
            color: #1f2937;
        }
        .chat-section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        .chat-container {
            margin-top: 10px;
        }
        .chat-message {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            max-width: 85%;
        }
        .message-client {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            margin-left: 0;
            margin-right: auto;
        }
        .message-expert {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            margin-left: auto;
            margin-right: 0;
        }
        .message-header {
            font-size: 11px;
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 4px;
        }
        .message-content {
            font-size: 12px;
            color: #1f2937;
            word-wrap: break-word;
        }
        .message-time {
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
            margin-top: 4px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="logo">E-Konsul</div>
                    <div class="logo-tagline">Solusi Konsultasi Pakar Online Terpercaya</div>
                </td>
                <td style="text-align: right; vertical-align: bottom; font-size: 11px; color: #666666;">
                    Invoice: {{ $booking->payment->invoice ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="title">Resume Sesi Konsultasi</div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Klien</td>
            <td class="meta-value">{{ $booking->client->profile->name ?? 'Klien' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Pakar</td>
            <td class="meta-value">{{ $booking->expertProfile->user->profile->name ?? 'Pakar' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kategori Spesialisasi</td>
            <td class="meta-value">{{ $booking->expertProfile->category->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Konsultasi</td>
            <td class="meta-value">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu Sesi</td>
            <td class="meta-value">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Tipe Booking</td>
            <td class="meta-value">{{ ucfirst($booking->booking_type) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Pembayaran</td>
            <td class="meta-value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="chat-section-title">Riwayat Percakapan (Obrolan)</div>

    <div class="chat-container">
        @if($booking->consultation && $booking->consultation->chatMessages->isNotEmpty())
            @foreach($booking->consultation->chatMessages as $msg)
                @php
                    $isOwn = $msg->sender_id === $booking->client_id;
                    $senderName = $isOwn 
                        ? ($booking->client->profile->name ?? 'Klien') 
                        : ($booking->expertProfile->user->profile->name ?? 'Pakar');
                @endphp
                
                <div class="chat-message {{ $isOwn ? 'message-client' : 'message-expert' }}">
                    <div class="message-header">
                        {{ $senderName }} ({{ $isOwn ? 'Klien' : 'Pakar' }})
                    </div>
                    <div class="message-content">
                        {{ $msg->message }}
                    </div>
                    <div class="message-time">
                        {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                    </div>
                </div>
            @endforeach
        @else
            <div style="text-align: center; color: #6b7280; padding: 20px; font-style: italic;">
                Tidak ada riwayat pesan obrolan dalam sesi ini.
            </div>
        @endif
    </div>

    <div class="footer">
        Dokumen ini diterbitkan secara sah oleh E-Konsul sebagai bukti resume konsultasi resmi.
        <br>&copy; {{ date('Y') }} E-Konsul. All rights reserved.
    </div>

</body>
</html>
