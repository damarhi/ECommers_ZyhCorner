<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <!-- Use Outfit/Inter font for modern aesthetics -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
</head>
<body>

    <div class="invoice-container">
        <!-- Control buttons (Hidden when printing) -->
        <div class="no-print-header no-print">
            <button onclick="window.close();" class="btn btn-light">
                Kembali
            </button>
            <button onclick="window.print();" class="btn btn-primary">
                Cetak Invoice
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <h1 class="store-logo">ZyhCorner</h1>
                <div class="store-info">
                    <strong>Premium Dessert, Cake and Cookies</strong><br>
                    Jl. Pemda, Pangkalan Kerinci<br>
                    Hubungi: +62 812-3456-7890 | zyhcorner@gmail.com
                </div>
            </div>
            <div>
                <h2 class="invoice-title">Invoice</h2>
                <div class="invoice-meta">
                    <div><span class="invoice-meta-label">No. Order:</span> {{ $order->order_number }}</div>
                    <div><span class="invoice-meta-label">Tanggal:</span> {{ $order->created_at->format('d M Y') }}</div>
                    <div><span class="invoice-meta-label">Status Bayar:</span> 
                        @if($order->payment_status == 'Paid')
                            <span class="badge badge-success">Paid</span>
                        @else
                            <span class="badge badge-secondary">Pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="details-box">
                <h3>Ditagihkan Kepada</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p><span class="label">Email</span>: {{ $order->email_pembeli ?? '-' }}</p>
                <p><span class="label">Alamat Kirim</span>: {{ $order->alamat_pengiriman ?? '-' }}</p>
            </div>
            <div class="details-box">
                <h3>Informasi Pengiriman & Pembayaran</h3>
                <p><span class="label">Metode Bayar</span>: {{ $order->payment_method ?? '-' }}</p>
                <p><span class="label">Metode Kirim</span>: {{ $order->shipping_method ?? '-' }}</p>
                <p><span class="label">Jenis Penjualan</span>: {{ $order->jenis_penjualan }}</p>
                @if($order->catatan_pesanan)
                    <p><span class="label">Catatan</span>: {{ $order->catatan_pesanan }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Produk</th>
                    <th style="text-align: right; width: 150px;">Harga Satuan</th>
                    <th style="text-align: center; width: 80px;">Qty</th>
                    <th style="text-align: right; width: 150px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td style="font-weight: 500;">{{ $item['name'] }}</td>
                    <td class="num">Rp. {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="center">{{ $item['qty'] }}</td>
                    <td class="num" style="font-weight: 600;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #888; padding: 30px;">Tidak ada item pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary -->
        <div class="invoice-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="lbl">Subtotal</span>
                    <span class="val">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span class="lbl">Total Bayar</span>
                    <span class="val">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Terima kasih telah berbelanja di <strong>ZyhCorner</strong>!</p>
            <p style="font-size: 11px; color: #aaa; margin-top: 5px;">Ini adalah dokumen resmi yang diterbitkan secara otomatis oleh sistem.</p>
        </div>
    </div>

    <!-- Automatically trigger printer dialog on page load -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
