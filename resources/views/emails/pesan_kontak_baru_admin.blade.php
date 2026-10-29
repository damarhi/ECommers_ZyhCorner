<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak Baru Masuk</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            opacity: 0.9;
        }
        .body-content {
            padding: 32px;
        }
        .status-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            margin-bottom: 20px;
        }
        .intro-text {
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 20px 0;
            color: #475569;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .info-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .info-table td.label {
            font-weight: 600;
            color: #64748b;
            width: 35%;
        }
        .info-table td.value {
            color: #0f172a;
            font-weight: 500;
        }
        .message-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4f46e5;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }
        .message-box-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .message-box-content {
            font-size: 14px;
            line-height: 1.6;
            color: #1e293b;
            white-space: pre-line;
            margin: 0;
        }
        .btn-action {
            display: block;
            width: 220px;
            margin: 0 auto;
            padding: 13px 20px;
            background: #4f46e5;
            color: #ffffff !important;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
        }
        .footer {
            background: #f8fafc;
            padding: 18px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>📬 Pesan Kontak Baru Masuk!</h1>
            <p>Dasbor Notifikasi Admin — Zyh Corner</p>
        </div>
        <div class="body-content">
            <div class="status-badge">Status: Belum Dibaca</div>
            
            <p class="intro-text">
                Halo Admin Zyh Corner, terdapat pesan kontak baru yang dikirimkan oleh pengunjung melalui formulir <strong>Hubungi Kami</strong>:
            </p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nama Pengirim</td>
                    <td class="value"><strong>{{ $messageData->nama }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Alamat Email</td>
                    <td class="value">
                        <a href="mailto:{{ $messageData->email }}" style="color: #4f46e5; text-decoration: none;">
                            {{ $messageData->email }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="label">Waktu Kirim</td>
                    <td class="value">
                        {{ $messageData->created_at ? $messageData->created_at->format('d M Y, H:i') . ' WIB' : now()->format('d M Y, H:i') . ' WIB' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Subjek Pesan</td>
                    <td class="value"><strong>{{ $messageData->subjek }}</strong></td>
                </tr>
            </table>

            <div class="message-box">
                <div class="message-box-title">Isi Pesan:</div>
                <p class="message-box-content">{{ $messageData->pesan }}</p>
            </div>

            <a href="{{ url('admin/messages') }}" class="btn-action">Buka Dasbor Admin</a>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner saat formulir kontak disubmit.<br>
            Anda dapat membalas pesan ini langsung ke alamat email pengirim di atas.
        </div>
    </div>
</body>
</html>
