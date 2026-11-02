@extends('emails.layout')

@section('title', 'Notifikasi Retur Produk')

@section('header-style', 'background: linear-gradient(135deg, #f59e0b, #d97706);')
@section('header-title', '↩️ Pengajuan Retur Produk Baru')
@section('header-subtitle', 'Dasbor Kasir Zyh Corner')

@section('content')
    <div class="alert-badge" style="background: #fffbe6; color: #d97706;">Status: MENUNGGU VERIFIKASI</div>
    
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

    <a href="{{ url('admin/returs') }}" class="btn-action" style="width: 200px; background: #d97706;">Kelola Retur Produk</a>
@endsection

@section('footer', 'Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner.')
