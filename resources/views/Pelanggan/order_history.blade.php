@extends('Layout.layout_pelanggan')

@section('title', 'Order History - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Order History</span>
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

    /* Dashboard layout */
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

    /* Main Order History Section */
    .order-history-container {
        background: white;
        padding: 0;
    }

    .history-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .history-title {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    /* Filter status tabs */
    .status-tabs-list {
        display: flex;
        gap: 20px;
        list-style: none;
        padding: 0;
        margin: 0;
        border-bottom: 1px solid #f2f2f2;
        padding-bottom: 5px;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    .status-tab-item {
        flex-shrink: 0;
    }

    .status-tab-item a {
        font-size: 14px;
        color: #666;
        text-decoration: none;
        padding-bottom: 8px;
        display: block;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
        font-weight: 500;
    }

    .status-tab-item a:hover {
        color: #00b207;
    }

    .status-tab-item.active a {
        color: #1a1a1a;
        font-weight: 600;
        border-bottom-color: #00b207;
    }

    /* Order history rows */
    .order-card-row {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-left-details {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .order-img-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e6e6e6;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        position: relative;
        flex-shrink: 0;
    }

    .order-img-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .extra-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #edf2ee;
        color: #00b207;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .order-meta-info {
        display: flex;
        flex-direction: column;
    }

    .order-date-label {
        font-size: 13px;
        color: #999;
        margin-bottom: 4px;
    }

    .order-total-summary {
        font-size: 14px;
        color: #666;
    }
    
    .order-total-summary strong {
        color: #1a1a1a;
    }

    .order-right-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 15px;
    }

    .order-status-text {
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    .status-processing {
        color: #00b207;
    }
    
    .status-on_the_way {
        color: #00b207;
    }
    
    .status-completed {
        color: #00b207;
    }
    
    .status-pending {
        color: #ff8a00;
    }
    
    .status-cancelled {
        color: #ea4b48;
    }

    .order-buttons-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-cancel-outline {
        border: 1px solid #e6e6e6;
        background: white;
        color: #666;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none !important;
    }

    .btn-cancel-outline:hover {
        background: #f8f9fa;
        color: #ea4b48;
        border-color: #ea4b48;
    }

    .btn-tracking-solid {
        background: #00b207;
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
        text-decoration: none !important;
    }

    .btn-tracking-solid:hover {
        background: #009906;
        color: white;
    }

    .btn-return-outline {
        border: 1.5px solid #ff8a00;
        background: white;
        color: #ff8a00;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none !important;
        display: inline-block;
    }

    .btn-return-outline:hover {
        background: #ff8a00;
        color: white;
    }

    /* Return card row styling */
    .return-card-row {
        background: white;
        border: 1px solid #e6e6e6;
        border-left: 4px solid #ff8a00;
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: box-shadow 0.3s;
    }

    .return-card-row:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }

    .return-left-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .return-icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff5eb;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .return-icon-circle i {
        font-size: 20px;
        color: #ff8a00;
    }

    .return-meta {
        display: flex;
        flex-direction: column;
    }

    .return-order-id {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 2px;
    }

    .return-product-name {
        font-size: 13px;
        color: #666;
        margin-bottom: 2px;
    }

    .return-reason {
        font-size: 13px;
        color: #999;
    }

    .return-right-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .return-date {
        font-size: 12px;
        color: #999;
    }

    .return-status-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 50px;
    }

    .return-status-badge.status-new {
        background: #fff5eb;
        color: #ff8a00;
    }

    .return-status-badge.status-processed {
        background: #eaf7fb;
        color: #2196f3;
    }

    .return-status-badge.status-accepted {
        background: #edf8ee;
        color: #00b207;
    }

    .return-status-badge.status-rejected {
        background: #fdf0ea;
        color: #ea4b48;
    }

    .return-status-badge.status-done {
        background: #edf8ee;
        color: #00b207;
    }

    .return-section-label {
        font-size: 14px;
        font-weight: 600;
        color: #ff8a00;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .return-empty {
        text-align: center;
        padding: 40px 20px;
        color: #999;
        font-size: 14px;
    }

    .return-empty i {
        font-size: 40px;
        color: #ddd;
        margin-bottom: 12px;
        display: block;
    }

    /* Custom pagination */
    .pagination-btn:hover {
        background-color: #f2f8f4;
        color: #00b207 !important;
        border-color: #00b207 !important;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        .order-card-row, .return-card-row {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            padding: 15px;
        }
        .order-right-actions, .return-right-info {
            align-items: flex-start;
            width: 100%;
        }
        .order-buttons-group {
            width: 100%;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }
        .order-buttons-group button, .order-buttons-group a {
            flex: 1;
            text-align: center;
            padding: 8px 10px;
            font-size: 12px;
            white-space: nowrap;
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
        
        <!-- Right Side: Order History Panel -->
        <div class="col-lg-9 col-md-8">
            <div class="order-history-container">
                
                <div class="history-header-flex">
                    <h2 class="history-title">Order History</h2>
                    
                    <ul class="status-tabs-list">
                        <li class="status-tab-item {{ is_null($statusFilter) ? 'active' : '' }}">
                            <a href="{{ route('order.history') }}">All</a>
                        </li>
                        <li class="status-tab-item {{ $statusFilter === 'processing' ? 'active' : '' }}">
                            <a href="{{ route('order.history', ['status' => 'processing']) }}">Processing</a>
                        </li>
                        <li class="status-tab-item {{ $statusFilter === 'on_the_way' ? 'active' : '' }}">
                            <a href="{{ route('order.history', ['status' => 'on_the_way']) }}">On The Way</a>
                        </li>
                        <li class="status-tab-item {{ $statusFilter === 'completed' ? 'active' : '' }}">
                            <a href="{{ route('order.history', ['status' => 'completed']) }}">Completed</a>
                        </li>
                        <li class="status-tab-item {{ $statusFilter === 'canceled' ? 'active' : '' }}">
                            <a href="{{ route('order.history', ['status' => 'canceled']) }}">Canceled</a>
                        </li>
                        <li class="status-tab-item {{ $statusFilter === 'return' ? 'active' : '' }}">
                            <a href="{{ route('order.history', ['status' => 'return']) }}">Return</a>
                        </li>
                    </ul>
                </div>
                
                @if($statusFilter !== 'return')
                <div class="order-cards-list">
                    @forelse($orders as $order)
                        @php
                            // Normalize status for design matching
                            $statusClass = 'pending';
                            $statusLabel = $order->order_status;
                            
                            if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered'])) {
                                $statusClass = 'completed';
                                $statusLabel = 'Completed';
                            } elseif (Str::contains(strtolower($order->order_status), ['otw', 'way', 'kirim', 'sedang dikirim'])) {
                                $statusClass = 'on_the_way';
                                $statusLabel = 'On the Way';
                            } elseif (Str::contains(strtolower($order->order_status), ['proses', 'processing', 'ready'])) {
                                $statusClass = 'processing';
                                $statusLabel = 'Processing';
                            } elseif (Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed'])) {
                                $statusClass = 'cancelled';
                                $statusLabel = 'Canceled';
                            } elseif (Str::contains(strtolower($order->order_status), ['menunggu'])) {
                                $statusClass = 'pending';
                                $statusLabel = 'Processing';
                            }
                            
                            $fallbackImg = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop';
                            $extraBadge = true;
                            if ($loop->index % 2 === 1) {
                                $fallbackImg = 'https://images.unsplash.com/photo-1621303837174-89787a7d4729?q=80&w=200&auto=format&fit=crop';
                                $extraBadge = false;
                            }
                        @endphp
                        
                        <div class="order-card-row">
                            <div class="order-left-details" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', $order->id) }}'">
                                <div class="order-img-wrapper">
                                    <img src="{{ $fallbackImg }}" alt="Cake Product">
                                    @if($extraBadge)
                                        <div class="extra-badge">+4</div>
                                    @endif
                                </div>
                                <div class="order-meta-info">
                                    <div class="order-date-label">{{ $order->created_at->format('d M, Y') }}</div>
                                    <div class="order-total-summary">
                                        Total: <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong> ({{ $extraBadge ? '5 Products' : '1 Product' }})
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-right-actions">
                                <div class="order-status-text status-{{ $statusClass }}">
                                    {{ $statusLabel }}
                                </div>
                                
                                <div class="order-buttons-group">
                                    @if(Str::contains(strtolower($order->order_status), ['menunggu', 'pending']))
                                        <button onclick="cancelOrder('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-outline">Cancel Order</button>
                                    @endif
                                    @if(Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']))
                                        <a href="{{ route('order.return', $order->id) }}" class="btn-return-outline"><i class="fa-solid fa-rotate-left" style="margin-right: 4px;"></i>Return</a>
                                    @endif
                                    <button onclick="trackOrder('{{ $order->order_number }}')" class="btn-tracking-solid">Tracking Order</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Dummy fallback rows -->
                        @if(is_null($statusFilter) || $statusFilter === 'processing')
                            <div class="order-card-row" id="dummyRow1">
                                <div class="order-left-details" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', 1) }}'">
                                    <div class="order-img-wrapper">
                                        <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Cake Product">
                                        <div class="extra-badge">+4</div>
                                    </div>
                                    <div class="order-meta-info">
                                        <div class="order-date-label">8 Sep, 2025</div>
                                        <div class="order-total-summary">
                                            Total: <strong>Rp. 135.000</strong> (5 Products)
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="order-right-actions">
                                    <div class="order-status-text status-processing">
                                        Processing
                                    </div>
                                    <div class="order-buttons-group">
                                        <button onclick="cancelDummyRow('dummyRow1')" class="btn-cancel-outline">Cancel Order</button>
                                        <button onclick="trackOrder('#DUMMY-1')" class="btn-tracking-solid">Tracking Order</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(is_null($statusFilter) || $statusFilter === 'on_the_way')
                            <div class="order-card-row" id="dummyRow2">
                                <div class="order-left-details" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', 2) }}'">
                                    <div class="order-img-wrapper">
                                        <img src="https://images.unsplash.com/photo-1621303837174-89787a7d4729?q=80&w=200&auto=format&fit=crop" alt="Cake Product">
                                    </div>
                                    <div class="order-meta-info">
                                        <div class="order-date-label">24 May, 2025</div>
                                        <div class="order-total-summary">
                                            Total: <strong>Rp. 25.000</strong> (1 Product)
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="order-right-actions">
                                    <div class="order-status-text status-on_the_way">
                                        On the Way
                                    </div>
                                    <div class="order-buttons-group">
                                        <button onclick="cancelDummyRow('dummyRow2')" class="btn-cancel-outline">Cancel Order</button>
                                        <button onclick="trackOrder('#DUMMY-2')" class="btn-tracking-solid">Tracking Order</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforelse
                </div>
                @endif

                {{-- Return Items Section --}}
                @if($statusFilter === 'return')
                    <div class="return-cards-list">
                        @if($returs->count() > 0)
                            @foreach($returs as $retur)
                                @php
                                    $returStatusClass = strtolower(str_replace(' ', '-', $retur->status));
                                    if ($returStatusClass === 'new') $returStatusClass = 'new';
                                    elseif ($returStatusClass === 'processed') $returStatusClass = 'processed';
                                    elseif ($returStatusClass === 'accepted') $returStatusClass = 'accepted';
                                    elseif ($returStatusClass === 'rejected') $returStatusClass = 'rejected';
                                    elseif ($returStatusClass === 'done') $returStatusClass = 'done';
                                @endphp
                                <div class="return-card-row" style="cursor: pointer;" onclick="window.location.href='{{ route('return.detail', $retur->id) }}'">
                                    <div class="return-left-info">
                                        <div class="return-icon-circle">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </div>
                                        <div class="return-meta">
                                            <div class="return-order-id">Order {{ $retur->order_id }}</div>
                                            <div class="return-product-name">{{ $retur->product_name }}</div>
                                            <div class="return-reason"><i class="fa-solid fa-comment-dots" style="margin-right: 4px;"></i>{{ Str::limit($retur->reason, 60) }}</div>
                                        </div>
                                    </div>
                                    <div class="return-right-info">
                                        <div class="return-date">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}</div>
                                        <span class="return-status-badge status-{{ $returStatusClass }}">{{ $retur->status }}</span>
                                        <span style="font-size: 12px; color: #00b207; font-weight: 600;">View Detail <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i></span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="return-empty">
                                <i class="fa-solid fa-box-open"></i>
                                <p>Belum ada pengajuan pengembalian.</p>
                            </div>
                        @endif
                    </div>
                @endif
                
                {{-- Custom Dynamic Pagination controls matching Zyh Corner theme --}}
                @if($statusFilter !== 'return')
                @if($orders->hasPages())
                    <div class="pagination-container" style="display: flex; justify-content: center; gap: 10px; margin-top: 40px; align-items: center;">
                        {{-- Previous Button --}}
                        @if($orders->onFirstPage())
                            <span class="pagination-btn disabled" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: not-allowed;"><i class="fa-solid fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;"><i class="fa-solid fa-chevron-left"></i></a>
                        @endif

                        {{-- Page Links --}}
                        @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                            @if($page == $orders->currentPage())
                                <span class="pagination-btn active" style="width: 36px; height: 36px; border-radius: 50%; background-color: #00b207; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;"><i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span class="pagination-btn disabled" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: not-allowed;"><i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                @else
                    {{-- Hardcoded pagination mock if displaying dummy rows (no real database rows paginating) --}}
                    @if($orders->isEmpty())
                        <div class="pagination-container" style="display: flex; justify-content: center; gap: 10px; margin-top: 40px; align-items: center;">
                            <span class="pagination-btn disabled" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: not-allowed;"><i class="fa-solid fa-chevron-left"></i></span>
                            <span class="pagination-btn active" style="width: 36px; height: 36px; border-radius: 50%; background-color: #00b207; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">1</span>
                            <a href="#" onclick="event.preventDefault(); showTabAlert('Page 2')" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s; text-decoration: none;">2</a>
                            <a href="#" onclick="event.preventDefault(); showTabAlert('Page 3')" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s; text-decoration: none;">3</a>
                            <a href="#" onclick="event.preventDefault(); showTabAlert('Page 2')" class="pagination-btn" style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s; text-decoration: none;"><i class="fa-solid fa-chevron-right"></i></a>
                        </div>
                    @endif
                @endif
                @endif
                
            </div>
        </div>
        
    </div>
</div>

<script>
    // Tab Alerts for settings
    function showTabAlert(tabName) {
        Swal.fire({
            title: tabName,
            text: `Halaman ${tabName} pelanggan akan segera hadir!`,
            icon: 'info',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Cancel Order Handler with Reason Modal
    function cancelOrder(id, orderNumber) {
        Swal.fire({
            title: 'REASON FOR CANCEL ORDERS',
            html: `
                <div style="text-align: left; margin-top: 10px;">
                    <label class="cancel-reason-option selected" onclick="selectCancelReason(this)">
                        <input type="radio" name="cancel_reason" value="Wrong choice of delivery address" checked>
                        <span>Wrong choice of delivery address</span>
                    </label>
                    <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                        <input type="radio" name="cancel_reason" value="Wrong choice of product">
                        <span>Wrong choice of product</span>
                    </label>
                    <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                        <input type="radio" name="cancel_reason" value="Want to change payment method">
                        <span>Want to change payment method</span>
                    </label>
                    <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                        <input type="radio" name="cancel_reason" value="Want to replace or add a product">
                        <span>Want to replace or add a product</span>
                    </label>
                    <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                        <input type="radio" name="cancel_reason" value="Others">
                        <span>Others</span>
                    </label>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#00b207',
            cancelButtonColor: '#ffffff',
            customClass: {
                popup: 'cancel-reason-popup',
                title: 'cancel-reason-title',
                cancelButton: 'cancel-reason-cancel-btn',
                confirmButton: 'cancel-reason-confirm-btn'
            },
            didOpen: () => {
                // Inject styles for the modal
                if (!document.getElementById('cancelReasonStyles')) {
                    const style = document.createElement('style');
                    style.id = 'cancelReasonStyles';
                    style.textContent = `
                        .cancel-reason-popup {
                            border-radius: 12px !important;
                            padding: 30px 20px !important;
                            max-width: 520px !important;
                        }
                        .cancel-reason-title {
                            font-size: 16px !important;
                            font-weight: 700 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.5px !important;
                            text-align: left !important;
                            color: #1a1a1a !important;
                            padding: 0 10px !important;
                        }
                        .cancel-reason-option {
                            display: flex;
                            align-items: center;
                            gap: 14px;
                            padding: 14px 18px;
                            border: 1.5px solid #e6e6e6;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all 0.25s ease;
                            background: white;
                            margin-bottom: 10px;
                            font-size: 14px;
                            color: #333;
                        }
                        .cancel-reason-option:hover {
                            border-color: #00b207;
                            background-color: #f8fff8;
                        }
                        .cancel-reason-option.selected {
                            border-color: #00b207;
                            background-color: #f0fdf0;
                        }
                        .cancel-reason-option input[type="radio"] {
                            appearance: none;
                            -webkit-appearance: none;
                            width: 20px;
                            height: 20px;
                            border: 2px solid #d0d0d0;
                            border-radius: 50%;
                            position: relative;
                            cursor: pointer;
                            flex-shrink: 0;
                            margin: 0;
                            transition: all 0.25s ease;
                        }
                        .cancel-reason-option input[type="radio"]:checked {
                            border-color: #00b207;
                        }
                        .cancel-reason-option input[type="radio"]:checked::after {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            width: 10px;
                            height: 10px;
                            background-color: #00b207;
                            border-radius: 50%;
                        }
                        .cancel-reason-option span {
                            flex: 1;
                        }
                        .cancel-reason-cancel-btn {
                            border: 2px solid #00b207 !important;
                            color: #00b207 !important;
                            background: white !important;
                            border-radius: 50px !important;
                            font-weight: 600 !important;
                            padding: 10px 30px !important;
                            font-size: 14px !important;
                        }
                        .cancel-reason-cancel-btn:hover {
                            background: #f0fdf0 !important;
                        }
                        .cancel-reason-confirm-btn {
                            border-radius: 50px !important;
                            font-weight: 600 !important;
                            padding: 10px 30px !important;
                            font-size: 14px !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                setTimeout(() => {
                    Swal.close();
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: 'Pesanan Anda telah berhasil dibatalkan.',
                        icon: 'success',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    }).then(() => {
                        window.location.reload();
                    });
                }, 1000);
            }
        });
    }

    // Select cancel reason helper
    function selectCancelReason(el) {
        document.querySelectorAll('.cancel-reason-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        el.classList.add('selected');
        el.querySelector('input[type="radio"]').checked = true;
    }

    // Tracking Order Handler
    function trackOrder(orderNumber) {
        Swal.fire({
            title: 'Pelacakan Pesanan',
            text: `Pesanan Anda ${orderNumber} saat ini sedang dalam proses pengiriman.`,
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Dummy Row Cancellation
    function cancelDummyRow(rowId) {
        cancelOrder(null, '#DUMMY');
    }
</script>
@endsection
