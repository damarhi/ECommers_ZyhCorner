@extends('Layout.layout_pelanggan')

@section('title', 'Return Detail - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <a href="{{ route('order.history') }}">Order History</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Return Detail</span>
@endsection

@section('styles')
<!-- Load PurpleAdmin CSS and Icons -->
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">

<style>
    /* Reset clashing Bootstrap styles back to Zyh Corner custom styling */
    .breadcrumb-wrapper {
        background-color: white !important;
        padding: 20px 0 0 0 !important;
        display: block !important;
    }
    
    .breadcrumb-area {
        background: linear-gradient(rgba(45, 78, 55, 0.7), rgba(45, 78, 55, 0.7)), url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=2042&auto=format&fit=crop') center/cover !important;
        height: 120px !important;
        display: flex !important;
        align-items: center !important;
        color: white !important;
        border-radius: 8px !important;
        margin: 0 auto !important;
        max-width: 1200px !important;
        position: relative !important;
        overflow: hidden !important;
        padding: 0 !important;
    }

    .breadcrumb {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        font-size: 14px !important;
        padding-left: 40px !important;
        z-index: 1 !important;
        background: transparent !important;
        margin-bottom: 0 !important;
        border: none !important;
        border-radius: 0 !important;
    }

    .breadcrumb a {
        color: #e0e0e0 !important;
        text-decoration: none !important;
    }
    
    .breadcrumb span {
        color: #00b207 !important;
    }

    .breadcrumb i {
        color: #999 !important;
    }

    body {
        background-color: #ffffff !important;
        font-family: 'Poppins', sans-serif !important;
        color: #1a1a1a !important;
    }
    
    header, nav.navigation, footer.footer, .cart-sidebar {
        font-family: 'Poppins', sans-serif !important;
    }
    
    header a, nav.navigation a, footer.footer a, .cart-sidebar a {
        text-decoration: none !important;
    }
    
    nav.navigation a {
        color: #cccccc !important;
    }
    nav.navigation a:hover, nav.navigation a.active {
        color: white !important;
    }
    
    footer.footer {
        background-color: #1a1a1a !important;
        color: white !important;
        padding-top: 70px !important;
        margin-top: 0 !important;
        height: auto !important;
    }
    
    footer.footer h3 {
        color: white !important;
        font-size: 18px !important;
        font-weight: 500 !important;
    }
    
    footer.footer a {
        color: #999 !important;
    }
    footer.footer a:hover {
        color: white !important;
    }
    
    .search-bar button {
        background-color: #00b207 !important;
        color: white !important;
    }
    
    .search-bar input {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Dashboard layout wrapper */
    .dashboard-wrapper {
        max-width: 1200px;
        margin: 50px auto 80px;
        padding: 0 15px;
    }

    /* Custom Navigation Sidebar Card */
    .sidebar-navigation {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 24px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .sidebar-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        padding: 0 24px 15px;
        margin-bottom: 15px;
        border-bottom: 1px solid #f2f2f2;
    }

    .nav-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-menu-item a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 24px;
        color: #666;
        font-size: 15px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .nav-menu-item a:hover {
        background-color: #f8f9fa;
        color: #00b207;
    }

    .nav-menu-item.active {
        background-color: #edf2ee;
    }

    .nav-menu-item.active a {
        color: #1a1a1a;
        font-weight: 600;
    }

    .nav-menu-item a i {
        font-size: 18px;
        width: 20px;
        text-align: center;
        color: #555;
    }
    
    .nav-menu-item.active a i {
        color: #00b207;
    }

    /* Page Header */
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .detail-header-left h2 {
        font-size: 22px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 4px 0;
    }

    .detail-header-left .subtitle {
        font-size: 13px;
        color: #999;
    }

    .btn-back-link {
        color: #00b207;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.3s;
    }

    .btn-back-link:hover {
        color: #009906 !important;
    }

    /* Return Status Banner */
    .return-status-banner {
        border-radius: 8px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .return-status-banner.status-new {
        background: #fff8f0;
        border: 1px solid #ffe0b2;
    }

    .return-status-banner.status-processed {
        background: #f0f7ff;
        border: 1px solid #bbdefb;
    }

    .return-status-banner.status-accepted {
        background: #f0fdf0;
        border: 1px solid #c8e6c9;
    }

    .return-status-banner.status-rejected {
        background: #fdf0ea;
        border: 1px solid #ffccbc;
    }

    .return-status-banner.status-done {
        background: #f0fdf0;
        border: 1px solid #c8e6c9;
    }

    .banner-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .banner-icon.status-new {
        background: #ff8a00;
        color: white;
    }

    .banner-icon.status-processed {
        background: #2196f3;
        color: white;
    }

    .banner-icon.status-accepted {
        background: #00b207;
        color: white;
    }

    .banner-icon.status-rejected {
        background: #ea4b48;
        color: white;
    }

    .banner-icon.status-done {
        background: #00b207;
        color: white;
    }

    .banner-text {
        display: flex;
        flex-direction: column;
    }

    .banner-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .banner-desc {
        font-size: 13px;
        color: #666;
    }

    /* Info Cards Grid */
    .info-cards-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .info-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .info-card-title {
        font-size: 13px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f2f2f2;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
    }

    .info-label {
        color: #666;
    }

    .info-value {
        font-weight: 500;
        color: #1a1a1a;
        text-align: right;
    }

    .info-value.highlight {
        color: #00b207;
        font-weight: 600;
    }

    .return-reason-box {
        background: #fafafa;
        border-radius: 6px;
        padding: 14px;
        font-size: 14px;
        color: #333;
        line-height: 1.6;
        margin-top: 8px;
    }

    /* Products Table */
    .products-section {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        margin-bottom: 24px;
    }

    .products-section-header {
        font-size: 13px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 1px solid #f2f2f2;
    }

    .product-item {
        display: flex;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid #f8f8f8;
        gap: 16px;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-thumb {
        width: 55px;
        height: 55px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e6e6e6;
        background: #f9f9f9;
        flex-shrink: 0;
    }

    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 2px;
    }

    .product-price-qty {
        font-size: 13px;
        color: #999;
    }

    .product-subtotal {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
        text-align: right;
        min-width: 120px;
    }

    .total-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 16px 20px;
        border-top: 2px solid #f2f2f2;
        gap: 30px;
    }

    .total-label {
        font-size: 14px;
        font-weight: 600;
        color: #666;
    }

    .total-value {
        font-size: 18px;
        font-weight: 700;
        color: #00b207;
    }

    /* Timeline tracker */
    .timeline-section {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .timeline-title {
        font-size: 13px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f2f2f2;
    }

    .timeline-list {
        position: relative;
        padding-left: 30px;
    }

    .timeline-list::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: #e6e6e6;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 24px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -25px;
        top: 3px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #e6e6e6;
        background: white;
    }

    .timeline-dot.active {
        border-color: #00b207;
        background: #00b207;
    }

    .timeline-dot.warning {
        border-color: #ff8a00;
        background: #ff8a00;
    }

    .timeline-dot.danger {
        border-color: #ea4b48;
        background: #ea4b48;
    }

    .timeline-label {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 2px;
    }

    .timeline-date {
        font-size: 12px;
        color: #999;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        
        .info-cards-grid {
            grid-template-columns: 1fr;
        }

        .detail-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .product-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .product-subtotal {
            text-align: left;
            min-width: auto;
            width: 100%;
            padding-top: 5px;
            border-top: 1px dashed #f2f2f2;
        }

        .total-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
    <div class="row">
        
        <!-- Left Side: Navigation Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="sidebar-navigation">
                <div class="sidebar-title">Navigation</div>
                <ul class="nav-menu-list">
                    <li class="nav-menu-item">
                        <a href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-table-cells-large"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-menu-item active">
                        <a href="{{ route('order.history') }}">
                            <i class="fa-solid fa-clock-rotate-left"></i> Order History
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="{{ route('userChart') }}">
                            <i class="fa-solid fa-bag-shopping"></i> Shopping Cart
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="{{ route('settings') }}">
                            <i class="fa-solid fa-gear"></i> Settings
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="#" onclick="event.preventDefault(); confirmLogout();">
                            <i class="fa-solid fa-right-from-bracket"></i> Log-out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Right Side: Return Detail Panel -->
        <div class="col-lg-9 col-md-8">
            
            @php
                $returStatusClass = strtolower(str_replace(' ', '-', $retur->status));
                $statusMessages = [
                    'new' => ['title' => 'Pengajuan pengembalian baru', 'desc' => 'Tim kami sedang meninjau permintaan pengembalian Anda.'],
                    'processed' => ['title' => 'Sedang diproses', 'desc' => 'Tim kami sedang memproses pengembalian Anda.'],
                    'accepted' => ['title' => 'Pengembalian diterima', 'desc' => 'Permintaan pengembalian Anda telah disetujui.'],
                    'rejected' => ['title' => 'Pengembalian ditolak', 'desc' => 'Maaf, permintaan pengembalian Anda tidak dapat diproses.'],
                    'done' => ['title' => 'Pengembalian selesai', 'desc' => 'Proses pengembalian telah selesai.'],
                ];
                $bannerInfo = $statusMessages[$returStatusClass] ?? ['title' => $retur->status, 'desc' => 'Status pengembalian Anda.'];
            @endphp

            <!-- Page Header -->
            <div class="detail-header">
                <div class="detail-header-left">
                    <h2>Return Detail</h2>
                    <div class="subtitle">Order {{ $retur->order_id }} &bull; {{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}</div>
                </div>
                <a href="{{ route('order.history', ['status' => 'return']) }}" class="btn-back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Returns
                </a>
            </div>

            <!-- Return Status Banner -->
            <div class="return-status-banner status-{{ $returStatusClass }}">
                <div class="banner-icon status-{{ $returStatusClass }}">
                    @if($returStatusClass === 'new')
                        <i class="fa-solid fa-clock"></i>
                    @elseif($returStatusClass === 'processed')
                        <i class="fa-solid fa-spinner"></i>
                    @elseif($returStatusClass === 'accepted')
                        <i class="fa-solid fa-check"></i>
                    @elseif($returStatusClass === 'rejected')
                        <i class="fa-solid fa-xmark"></i>
                    @elseif($returStatusClass === 'done')
                        <i class="fa-solid fa-circle-check"></i>
                    @else
                        <i class="fa-solid fa-rotate-left"></i>
                    @endif
                </div>
                <div class="banner-text">
                    <div class="banner-title">{{ $bannerInfo['title'] }}</div>
                    <div class="banner-desc">{{ $bannerInfo['desc'] }}</div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="info-cards-grid">
                <!-- Return Info -->
                <div class="info-card">
                    <div class="info-card-title">Return Information</div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span style="
                                font-size: 12px;
                                font-weight: 600;
                                padding: 3px 12px;
                                border-radius: 50px;
                                @if($returStatusClass === 'new') background: #fff5eb; color: #ff8a00;
                                @elseif($returStatusClass === 'processed') background: #eaf7fb; color: #2196f3;
                                @elseif($returStatusClass === 'accepted') background: #edf8ee; color: #00b207;
                                @elseif($returStatusClass === 'rejected') background: #fdf0ea; color: #ea4b48;
                                @elseif($returStatusClass === 'done') background: #edf8ee; color: #00b207;
                                @endif
                            ">{{ $retur->status }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Return Date</span>
                        <span class="info-value">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Product</span>
                        <span class="info-value">{{ $retur->product_name }}</span>
                    </div>
                    <div style="margin-top: 12px;">
                        <span class="info-label" style="font-size: 13px;">Reason:</span>
                        <div class="return-reason-box">{{ $retur->reason }}</div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="info-card">
                    <div class="info-card-title">Order Information</div>
                    @if($order)
                        <div class="info-row">
                            <span class="info-label">Order ID</span>
                            <span class="info-value" style="font-weight: 600;">{{ $order->order_number }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Order Date</span>
                            <span class="info-value">{{ $order->created_at->format('d M, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Customer</span>
                            <span class="info-value">{{ $order->customer_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Order Status</span>
                            <span class="info-value highlight">{{ ucfirst($order->order_status) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total</span>
                            <span class="info-value" style="font-weight: 700; color: #00b207;">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                        <div style="margin-top: 12px;">
                            <a href="{{ route('order.detail', $order->id) }}" style="color: #00b207; font-size: 13px; font-weight: 600; text-decoration: none;">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 4px;"></i>View Full Order
                            </a>
                        </div>
                    @else
                        <div style="color: #999; font-size: 14px; padding: 20px 0; text-align: center;">
                            <i class="fa-solid fa-circle-info" style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                            Order data not available
                        </div>
                    @endif
                </div>
            </div>

            <!-- Products in this order -->
            @if($order && count($items) > 0)
            <div class="products-section">
                <div class="products-section-header">Products in Order</div>
                @foreach($items as $item)
                    <div class="product-item">
                        <div class="product-thumb">
                            @if($item['product']->gambar)
                                <img src="{{ asset('storage/' . $item['product']->gambar) }}" alt="{{ $item['product']->nama_produk }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Cake">
                            @endif
                        </div>
                        <div class="product-info">
                            <div class="product-name">{{ $item['product']->nama_produk }}</div>
                            <div class="product-price-qty">Rp. {{ number_format($item['price'], 0, ',', '.') }} × {{ $item['qty'] }}</div>
                        </div>
                        <div class="product-subtotal">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                    </div>
                @endforeach
                <div class="total-row">
                    <span class="total-label">Total</span>
                    <span class="total-value">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
            @endif

            <!-- Return Timeline -->
            <div class="timeline-section">
                <div class="timeline-title">Return Progress</div>
                <div class="timeline-list">
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-label">Return submitted</div>
                        <div class="timeline-date">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}</div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot {{ in_array($returStatusClass, ['processed', 'accepted', 'done']) ? 'active' : (in_array($returStatusClass, ['rejected']) ? 'danger' : '') }}"></div>
                        <div class="timeline-label">
                            @if($returStatusClass === 'rejected')
                                Return reviewed — Rejected
                            @elseif(in_array($returStatusClass, ['processed', 'accepted', 'done']))
                                Return reviewed
                            @else
                                Under review
                            @endif
                        </div>
                        <div class="timeline-date">
                            @if(in_array($returStatusClass, ['processed', 'accepted', 'rejected', 'done']))
                                {{ $retur->updated_at->format('d M, Y') }}
                            @else
                                Pending
                            @endif
                        </div>
                    </div>

                    @if($returStatusClass !== 'rejected')
                    <div class="timeline-item">
                        <div class="timeline-dot {{ in_array($returStatusClass, ['accepted', 'done']) ? 'active' : '' }}"></div>
                        <div class="timeline-label">Return accepted</div>
                        <div class="timeline-date">
                            @if(in_array($returStatusClass, ['accepted', 'done']))
                                {{ $retur->updated_at->format('d M, Y') }}
                            @else
                                Pending
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot {{ $returStatusClass === 'done' ? 'active' : '' }}"></div>
                        <div class="timeline-label">Return completed</div>
                        <div class="timeline-date">
                            @if($returStatusClass === 'done')
                                {{ $retur->updated_at->format('d M, Y') }}
                            @else
                                Pending
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
        
    </div>
</div>

<script>
    // Confirm Logout
    function confirmLogout() {
        Swal.fire({
            title: 'Logout?',
            text: 'Apakah Anda yakin ingin keluar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ea4b48',
            cancelButtonColor: '#c3cdd6',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm')?.submit();
            }
        });
    }
</script>
@endsection
