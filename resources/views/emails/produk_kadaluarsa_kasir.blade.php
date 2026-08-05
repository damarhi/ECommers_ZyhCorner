<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi Produk Kadaluarsa</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; padding: 25px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .body-content { padding: 30px; }
        .alert-badge { display: inline-block; background: #fee2e2; color: #dc2626; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-table td.label { font-weight: 600; color: #666666; width: 40%; }
        .info-table td.value { color: #1a1a1a; font-weight: 500; }
        .btn-action { display: block; width: 220px; margin: 25px auto 0; padding: 12px 20px; background: #dc2626; color: #ffffff; text-align: center; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 14px; }
        .footer { background: #f8faf9; padding: 15px 30px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <h1>⚠️ PERINGATAN PRODUK KADALUARSA</h1>
            <p>Dasbor Kasir & Stok Zyh Corner</p>
        </div>
        <div class="body-content">
            <div class="alert-badge">PERHATIAN: PERLU TINDAKAN STOK</div>
            
            <p style="font-size: 14px; line-height: 1.5; margin-top: 0;">Halo Tim Kasir, sistem mendeteksi produk yang telah melewati tanggal kadaluarsa dan perlu segera ditindaklanjuti dari etalase toko:</p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nama Produk</td>
                    <td class="value"><strong>{{ $produkKadaluarsa->produk->nama_produk ?? 'Produk' }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Kategori</td>
                    <td class="value">{{ $produkKadaluarsa->produk->kategori->nama_kategori ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Kadaluarsa</td>
                    <td class="value" style="color: #dc2626; font-weight: 700;">{{ $produkKadaluarsa->tanggal_kadaluarsa ? \Carbon\Carbon::parse($produkKadaluarsa->tanggal_kadaluarsa)->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jumlah Stok Terpengaruh</td>
                    <td class="value">{{ $produkKadaluarsa->jumlah_stok ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status Penanganan</td>
                    <td class="value"><strong>{{ $produkKadaluarsa->status_tindakan ?? 'Perlu Tindakan' }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Catatan</td>
                    <td class="value">{{ $produkKadaluarsa->catatan ?? 'Telah otomatis disinkronkan oleh sistem.' }}</td>
                </tr>
            </table>

            <a href="{{ route('products.expired.index') }}" class="btn-action">Kelola Produk Kadaluarsa</a>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh Sistem Pemantauan Stok Zyh Corner.
        </div>
    </div>
</body>
</html>
