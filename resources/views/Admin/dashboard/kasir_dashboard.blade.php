@extends('Layout.layout_pAdmin')

@section('title', 'Dashboard Shift Kasir - Zyh Corner')

@section('content')
    <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-cash-register"></i>
            </span>
            <span class="text-nowrap">Dashboard Shift Kasir</span>
        </h3>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-gradient-primary d-inline-flex align-items-center px-3">
                <i class="mdi mdi-point-of-sale me-1"></i> Buka Kasir POS
            </a>
            <a href="{{ route('orders.index', ['order_status' => 'menunggu konfirmasi']) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center px-3">
                <i class="mdi mdi-shopping-outline me-1"></i> Cek Pesanan Baru
            </a>
        </div>
    </div>

    <!-- Summary Shift KPI Cards -->
    <div class="row">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-coral border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-cart-check"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Menunggu Konfirmasi</p>
                        <h3 class="stat-value">{{ number_format($perluKonfirmasi ?? 0) }}</h3>
                        <p class="stat-change mb-0">{{ number_format($pesananPerluDikemas ?? 0) }} pesanan sedang dikemas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-teal border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Setoran POS Hari Ini</p>
                        <h3 class="stat-value">Rp {{ number_format($setoranPosHariIni ?? 0, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0">{{ number_format($transaksiPosHariIni ?? 0) }} transaksi kasir hari ini ({{ now()->translatedFormat('d M Y') }})</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-indigo border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-bell-ring-outline"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Perhatian Operasional</p>
                        <h3 class="stat-value">{{ number_format(($returBaruCount ?? 0) + ($expiredPerluTindakan ?? 0)) }}</h3>
                        <p class="stat-change mb-0">{{ $returBaruCount ?? 0 }} retur baru &bull; {{ $expiredPerluTindakan ?? 0 }} expired etalase &bull; {{ $unreadMessagesCount ?? 0 }} pesan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Low Stock Showcase Panel -->
        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card border-0 shadow-sm w-100" style="border-radius: 8px;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1 font-weight-bold">Pantauan Stok Etalase Menipis</h4>
                            <p class="text-muted mb-0" style="font-size: 12.5px;">Produk showcase dengan sisa stok &le; 3 pcs</p>
                        </div>
                        <a href="{{ route('products.expired.index') }}" class="btn btn-inverse-warning btn-sm px-2.5 py-1" title="Cek Produk Kadaluarsa">
                            <i class="mdi mdi-calendar-remove me-1"></i> Cek Expired
                        </a>
                    </div>

                    <div class="table-responsive flex-grow-1">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Sisa Stok</th>
                                    <th class="text-end">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $prod)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-dark text-truncate" style="max-width: 190px;">{{ $prod->nama_produk }}</div>
                                            <small class="text-muted">{{ $prod->kategori->nama_kategori ?? 'Tanpa Kategori' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($prod->stok <= 0)
                                                <span class="badge badge-soft-danger">Habis (0)</span>
                                            @else
                                                <span class="badge badge-soft-warning">{{ $prod->stok }} pcs</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('pos.index') }}" class="btn btn-inverse-primary btn-sm px-2 py-1" title="Tambah Stok via POS">
                                                <i class="mdi mdi-plus-box-outline"></i> POS
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-check-circle-outline text-success d-block mb-2" style="font-size: 32px;"></i>
                                            Seluruh stok produk showcase dalam kondisi aman.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Orders Table -->
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card border-0 shadow-sm w-100" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1 font-weight-bold">Transaksi Terbaru</h4>
                            <p class="text-muted mb-0" style="font-size: 12.5px;">5 pesanan online &amp; POS terakhir yang masuk</p>
                        </div>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-gradient-primary">
                            Semua Pesanan <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Saluran</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th class="text-center">Struk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="font-weight-600 text-dark">{{ $order->order_number }}</td>
                                        <td>
                                            <span class="font-weight-500 text-dark d-block text-truncate" style="max-width: 130px;">{{ $order->customer_name }}</span>
                                            <small class="text-muted" style="font-size: 11px;">{{ $order->created_at->format('H:i') }} WIB</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->jenis_penjualan === 'Online' ? 'badge-soft-info' : 'badge-soft-dark' }}">
                                                {{ $order->jenis_penjualan }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(in_array(strtolower($order->order_status ?? ''), ['menunggu konfirmasi', 'unconfirmed']))
                                                <span class="badge badge-soft-warning badge-pulse-amber">Perlu Konfirmasi</span>
                                            @elseif($order->order_status === 'dikemas')
                                                <span class="badge badge-soft-info">Dikemas</span>
                                            @elseif($order->order_status === 'dikirim')
                                                <span class="badge badge-soft-primary">Dikirim</span>
                                            @elseif($order->order_status === 'selesai')
                                                <span class="badge badge-soft-success">Selesai</span>
                                            @elseif($order->order_status === 'batal')
                                                <span class="badge badge-soft-danger">Batal</span>
                                            @else
                                                <span class="badge badge-soft-warning">{{ $order->order_status }}</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-600 text-dark">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-inverse-primary btn-sm px-2 py-1" title="Cetak Struk">
                                                <i class="mdi mdi-printer" style="font-size: 14px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-receipt text-muted mb-2" style="font-size: 32px; display: block;"></i>
                                            Belum ada transaksi terbaru saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
