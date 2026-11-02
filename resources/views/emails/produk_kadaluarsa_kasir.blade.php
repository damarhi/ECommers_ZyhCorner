@extends('emails.layout')

@section('title', 'Notifikasi Produk Kadaluarsa')

@section('header-style', 'background: linear-gradient(135deg, #ef4444, #dc2626);')
@section('header-title', '⚠️ PERINGATAN PRODUK KADALUARSA')
@section('header-subtitle', 'Dasbor Kasir & Stok Zyh Corner')

@section('content')
    <div class="alert-badge" style="background: #fee2e2; color: #dc2626;">PERHATIAN: PERLU TINDAKAN STOK</div>
    
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

    <a href="{{ route('products.expired.index') }}" class="btn-action" style="width: 220px; background: #dc2626;">Kelola Produk Kadaluarsa</a>
@endsection

@section('footer', 'Email ini dikirim secara otomatis oleh Sistem Pemantauan Stok Zyh Corner.')
