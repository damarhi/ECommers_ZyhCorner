<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi Pesanan Baru</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #00b207, #008a05); color: #ffffff; padding: 25px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .body-content { padding: 30px; }
        .order-badge { display: inline-block; background: #e6f7e8; color: #00b207; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-table td.label { font-weight: 600; color: #666666; width: 40%; }
        .info-table td.value { color: #1a1a1a; font-weight: 500; }
        .items-header { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 13px; }
        .items-table th { background: #f8faf9; color: #555555; text-align: left; padding: 10px; border-bottom: 2px solid #e0e0e0; }
        .items-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .btn-action { display: block; width: 200px; margin: 25px auto 0; padding: 12px 20px; background: #00b207; color: #ffffff; text-align: center; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 14px; }
        .footer { background: #f8faf9; padding: 15px 30px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <h1>🛒 Pesanan Baru Masuk!</h1>
            <p>Dasbor Kasir Zyh Corner</p>
        </div>
        <div class="body-content">
            <div class="order-badge">Status: {{ strtoupper($pesanan->payment_status === 'Paid' ? 'DIBAYAR (PAID)' : $pesanan->payment_method) }}</div>
            
            <p style="font-size: 14px; line-height: 1.5; margin-top: 0;">Halo Tim Kasir, terdapat pesanan baru yang siap diproses:</p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nomor Pesanan</td>
                    <td class="value"><strong>{{ $pesanan->order_number }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Nama Pembeli</td>
                    <td class="value">{{ $pesanan->customer_name }}</td>
                </tr>
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td class="value">{{ $pesanan->payment_method }}</td>
                </tr>
                <tr>
                    <td class="label">Status Pembayaran</td>
                    <td class="value"><span style="color: {{ $pesanan->payment_status === 'Paid' ? '#00b207' : '#d97706' }}; font-weight: 700;">{{ $pesanan->payment_status }}</span></td>
                </tr>
                <tr>
                    <td class="label">Total Pembayaran</td>
                    <td class="value" style="font-size: 16px; color: #00b207;"><strong>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Alamat Pengiriman</td>
                    <td class="value">{{ $pesanan->alamat_pengiriman ?? '-' }}</td>
                </tr>
            </table>

            @if(is_array($pesanan->items) && count($pesanan->items) > 0)
                <div class="items-header">Rincian Produk:</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th style="text-align: center;">Jumlah</th>
                            <th style="text-align: right;">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesanan->items as $item)
                            <tr>
                                <td>{{ $item['name'] ?? 'Produk' }}</td>
                                <td style="text-align: center;">{{ $item['qty'] ?? 1 }}</td>
                                <td style="text-align: right;">Rp {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <a href="{{ url('admin/orders') }}" class="btn-action">Buka Dasbor Kasir</a>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner.
        </div>
    </div>
</body>
</html>
