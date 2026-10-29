@extends('Layout.layout_pAdmin')

@section('title', 'Data Seluruh Penjualan - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-format-list-bulleted"></i>
            </span> Data Seluruh Penjualan
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <a href="{{ route('all-sales.report', request()->query()) }}" target="_blank" class="btn btn-gradient-primary btn-sm px-4 d-inline-flex align-items-center">
                <i class="mdi mdi-printer me-1"></i> Cetak Laporan Penjualan
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-coral border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Total Omset Penjualan</p>
                        <h3 class="stat-value">Rp. {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0">Total akumulasi penjualan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-indigo border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-cart-outline"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Penjualan Online</p>
                        <h3 class="stat-value">{{ $totalOnlineCount ?? 0 }}</h3>
                        <p class="stat-change mb-0">Transaksi pesanan selesai online</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-teal border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-store"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Penjualan Kasir / Offline</p>
                        <h3 class="stat-value">{{ $totalOfflineCount ?? 0 }}</h3>
                        <p class="stat-change mb-0">Transaksi langsung kasir offline</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <!-- Toolbar -->
                    <form method="GET" action="{{ route('all-sales.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                    <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter</span>
                                </button>
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Jenis Penjualan</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="jenis_penjualan[]" value="Online" class="form-check-input" {{ is_array(request('jenis_penjualan')) && in_array('Online', request('jenis_penjualan')) ? 'checked' : '' }}> Online
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="jenis_penjualan[]" value="Offline" class="form-check-input" {{ is_array(request('jenis_penjualan')) && in_array('Offline', request('jenis_penjualan')) ? 'checked' : '' }}> Offline
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                                </div>
                            </div>
                            <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari transaksi penjualan..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Pelanggan</th>
                                    <th>Status Pembayaran</th>
                                    <th>Metode Bayar</th>
                                    <th>Status Pesanan</th>
                                    <th>Total</th>
                                    <th class="text-center" style="width: 95px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $order)
                                <tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $order->jenis_penjualan . ' ' . $order->metode_pembayaran . ' ' . $order->status_pembayaran . ' ' . $order->status_pesanan) }}">
                                    <td>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" class="text-primary text-decoration-none font-weight-600" title="Detail Pesanan">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="text-muted">{{ $order->created_at->format('M j, g:i A') }}</td>
                                    <td>
                                        @if($order->jenis_penjualan == 'Online')
                                            <span class="badge badge-soft-info">Online</span>
                                        @else
                                            <span class="badge badge-soft-dark">Offline</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column" style="line-height: 1.35;">
                                            <span class="font-weight-600 text-dark" style="font-size: 13px;">
                                                {{ $order->customer_name }}
                                            </span>
                                            <small class="text-muted" style="font-size: 11.5px;">
                                                {{ $order->email_pembeli ?? '-' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge badge-soft-success">Dibayar</span>
                                        @else
                                            <span class="badge badge-soft-warning">Tertunda</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $method = $order->payment_method ?: ($order->jenis_penjualan === 'Offline' ? 'Cash' : 'Transfer');
                                            $mLower = strtolower($method);
                                        @endphp
                                        <span class="d-inline-flex align-items-center text-dark font-weight-500" style="font-size: 12.5px; gap: 5px;">
                                            @if(str_contains($mLower, 'cash') || str_contains($mLower, 'tunai'))
                                                <i class="mdi mdi-cash text-success" style="font-size: 16px; line-height: 1;"></i>
                                            @elseif(str_contains($mLower, 'transfer') || str_contains($mLower, 'bank'))
                                                <i class="mdi mdi-bank-transfer text-primary" style="font-size: 16px; line-height: 1;"></i>
                                            @elseif(str_contains($mLower, 'wallet') || str_contains($mLower, 'qris') || str_contains($mLower, 'midtrans'))
                                                <i class="mdi mdi-wallet-outline text-info" style="font-size: 16px; line-height: 1;"></i>
                                            @elseif(str_contains($mLower, 'cod'))
                                                <i class="mdi mdi-truck-delivery-outline text-warning" style="font-size: 16px; line-height: 1;"></i>
                                            @else
                                                <i class="mdi mdi-credit-card-outline text-secondary" style="font-size: 16px; line-height: 1;"></i>
                                            @endif
                                            <span>{{ $method }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->order_status == 'menunggu pembayaran')
                                            <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                        @elseif($order->order_status == 'menunggu konfirmasi')
                                            <span class="badge badge-pulse-amber">Menunggu Konfirmasi</span>
                                        @elseif($order->order_status == 'dikemas')
                                            <span class="badge badge-soft-info">Dikemas</span>
                                        @elseif($order->order_status == 'dikirim')
                                            <span class="badge badge-soft-primary">Dikirim</span>
                                        @elseif($order->order_status == 'selesai')
                                            <span class="badge badge-soft-success">Selesai</span>
                                        @elseif($order->order_status == 'batal')
                                            <span class="badge badge-soft-danger">Dibatalkan</span>
                                        @else
                                            <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-600 text-dark">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 6px;" title="Detail Penjualan" aria-label="Detail Penjualan">
                                                <i class="mdi mdi-eye" style="font-size: 16px;"></i>
                                            </button>
                                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-inverse-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 6px;" title="Cetak Invoice" aria-label="Cetak Invoice">
                                                <i class="mdi mdi-printer" style="font-size: 16px;"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-cart-outline text-muted mb-2" style="font-size: 64px;"></i>
                                            <h5 class="text-muted font-weight-bold">Belum Ada Data Penjualan</h5>
                                            <p class="text-muted mb-0" style="font-size: 14px;">Oops, belum ada data penjualan yang dapat ditampilkan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modals (placed outside the table to prevent HTML rendering issues) -->
                    @foreach($sales as $order)
                        @include('Admin.kelola_penjualan.order_detail_modal', ['order' => $order])
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                        <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                            {{ $sales->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                            {{ $sales->total() }} Hasil
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
