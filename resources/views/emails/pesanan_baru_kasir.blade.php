@extends('emails.layout')

@section('title', 'Notifikasi Pesanan Baru')

@section('header-style', 'background: linear-gradient(135deg, #00b207, #008a05);')
@section('header-title', '🛒 Pesanan Baru Masuk!')
@section('header-subtitle', 'Dasbor Kasir Zyh Corner')

@section('content')
    <div class="order-badge" style="background: #e6f7e8; color: #00b207;">Status: {{ strtoupper($pesanan->payment_status === 'Paid' ? 'DIBAYAR (PAID)' : $pesanan->payment_method) }}</div>
    
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

    <a href="{{ url('admin/orders') }}" class="btn-action" style="width: 200px; background: #00b207;">Buka Dasbor Kasir</a>
@endsection

@section('footer', 'Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner.')
