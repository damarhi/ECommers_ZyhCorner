<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi Retur Produk</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; padding: 25px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .body-content { padding: 30px; }
        .alert-badge { display: inline-block; background: #fffbe6; color: #d97706; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-table td.label { font-weight: 600; color: #666666; width: 40%; }
        .info-table td.value { color: #1a1a1a; font-weight: 500; }
        .btn-action { display: block; width: 200px; margin: 25px auto 0; padding: 12px 20px; background: #d97706; color: #ffffff; text-align: center; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 14px; }
        .footer { background: #f8faf9; padding: 15px 30px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <h1>↩️ Pengajuan Retur Produk Baru</h1>
            <p>Dasbor Kasir Zyh Corner</p>
        </div>
        <div class="body-content">
            <div class="alert-badge">Status: MENUNGGU VERIFIKASI</div>
            
            <p style="font-size: 14px; line-height: 1.5; margin-top: 0;">Halo Tim Kasir, pelanggan mengajukan pengembalian/retur produk berikut:</p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nomor Pesanan</td>
                    <td class="value"><strong>{{ $retur->order_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Nama Pelanggan</td>
                    <td class="value">{{ $retur->customer_name }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Produk / Pesanan</td>
                    <td class="value">{{ $retur->product_name }}</td>
                </tr>
                <tr>
                    <td class="label">Alasan Retur</td>
                    <td class="value" style="color: #d97706; font-weight: 700;">{{ $retur->reason }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengajuan</td>
                    <td class="value">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d F Y') : '-' }}</td>
                </tr>
                @if($retur->foto_retur)
                <tr>
                    <td class="label">Foto Bukti Barang</td>
                    <td class="value">
                        <a href="{{ url($retur->foto_retur) }}" target="_blank">
                            <img src="{{ url($retur->foto_retur) }}" alt="Foto Bukti Retur" style="max-width: 180px; max-height: 180px; border-radius: 8px; border: 1px solid #e0e0e0; margin-top: 5px;">
                        </a>
                    </td>
                </tr>
                @endif
            </table>

            <a href="{{ url('admin/returs') }}" class="btn-action">Kelola Retur Produk</a>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner.
        </div>
    </div>
</body>
</html>
