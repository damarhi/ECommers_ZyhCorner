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
            <button type="button" class="btn btn-outline-secondary btn-sm px-4 me-2 bg-white">
                Ekspor
            </button>
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
                            <div class="input-group w-100 w-sm-auto" style="max-width: 250px; height: 32px;">
                                <div class="input-group-prepend" style="height: 32px;">
                                    <span class="input-group-text bg-transparent border-right-0" style="padding: 0 10px; height: 32px;"><i class="mdi mdi-magnify text-muted"></i></span>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-left-0" placeholder="Cari..." style="height: 32px; padding-left: 0;">
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table" style="border-bottom: 1px solid #e4e4e4;">
                            <thead>
                                <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Tanggal</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Jenis</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pelanggan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Email/Username</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status Pembayaran</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $order)
                                <tr>
                                    <td style="padding: 15px 10px; font-weight: 500; font-size: 14px;">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" class="text-primary text-decoration-none font-weight-bold" title="Detail Pesanan">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $order->created_at->format('M j, g:i A') }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">
                                        @if($order->jenis_penjualan == 'Online')
                                            <span class="badge badge-soft-info">Online</span>
                                        @else
                                            <span class="badge badge-soft-dark">Offline</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $order->customer_name }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $order->email_pembeli ?? '-' }}</td>
                                    <td style="padding: 15px 10px;">
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge badge-soft-success">Dibayar</span>
                                        @else
                                            <span class="badge badge-soft-warning">Tertunda</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        @if($order->order_status == 'menunggu pembayaran')
                                            <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                        @elseif($order->order_status == 'dikemas')
                                            <span class="badge badge-soft-info">Dikemas</span>
                                        @elseif($order->order_status == 'dikirim')
                                            <span class="badge badge-soft-primary">Dikirim</span>
                                        @elseif($order->order_status == 'selesai')
                                            <span class="badge badge-soft-success">Selesai</span>
                                        @else
                                            <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
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
                        @include('Admin.order_detail_modal', ['order' => $order])
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
