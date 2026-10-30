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
                        <h3 class="stat-value" data-kpi="total-pendapatan">Rp. {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</h3>
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
                        <h3 class="stat-value" data-kpi="online-count">{{ $totalOnlineCount ?? 0 }}</h3>
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
                        <h3 class="stat-value" data-kpi="offline-count">{{ $totalOfflineCount ?? 0 }}</h3>
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
                    <form method="GET" action="{{ route('all-sales.index') }}" id="all-sales-filter-form" data-ajax-container="#all-sales-table-container" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
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
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari pesanan, pelanggan, atau produk..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;"
                                    data-server-search="true">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                        </div>
                    </form>

                    <!-- Table Container with AJAX support -->
                    <div id="all-sales-table-container" class="position-relative">
                        @include('Admin.kelola_penjualan.partials.all_sales_table')
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
