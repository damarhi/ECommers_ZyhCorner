@extends('Layout.layout_pelanggan')

@section('title', 'Order Details - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <a href="{{ route('order.history') }}">Order History</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Order Details</span>
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

    /* Order Details right panel container */
    .details-container {
        background: white;
        padding: 0;
    }

    .details-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .details-title-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
    }

    .details-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    .dot-separator {
        width: 4px;
        height: 4px;
        background-color: #ccc;
        border-radius: 50%;
    }

    .details-header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .green-link {
        color: #00b207 !important;
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.3s;
        font-size: 14px;
        cursor: pointer;
    }

    .green-link:hover {
        color: #009906 !important;
    }

    .btn-cancel-top {
        background: #00b207;
        color: white;
        padding: 10px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
        box-shadow: 0 4px 10px rgba(0, 178, 7, 0.15);
    }

    .btn-cancel-top:hover {
        background: #009906;
    }

    .btn-cancel-top.disabled {
        background: #e6e6e6 !important;
        color: #999 !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
    }

    .btn-return-top {
        background: transparent;
        color: #ff8a00;
        padding: 10px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        border: 2px solid #ff8a00;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none !important;
        display: inline-block;
    }

    .btn-return-top:hover {
        background: #ff8a00;
        color: white;
    }

    /* Summary cards grid styling */
    .summary-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 24px;
        height: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }

    .summary-card-title {
        font-size: 12px;
        font-weight: 600;
        color: #999;
        letter-spacing: 1px;
        margin-bottom: 15px;
        text-transform: uppercase;
        border-bottom: 1px solid #f2f2f2;
        padding-bottom: 8px;
    }

    .summary-card-name {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 6px;
    }

    .summary-card-address {
        font-size: 13.5px;
        color: #666;
        line-height: 1.5;
        margin-bottom: 12px;
        min-height: 38px;
    }

    .summary-card-contact {
        font-size: 13px;
        color: #333;
        margin-bottom: 4px;
    }
    
    .summary-card-contact span {
        color: #666;
    }

    /* Summary calculations card specific list */
    .summary-calc-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .summary-calc-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #666;
        border-bottom: 1px dashed #f2f2f2;
        padding-bottom: 6px;
    }

    .summary-calc-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .summary-calc-row.main-total {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        padding-top: 5px;
    }

    .summary-calc-row.main-total span:last-child {
        color: #00b207;
        font-size: 16px;
        font-weight: 700;
    }

    /* Visual tracking stepper styling */
    .tracking-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 24px;
        margin: 30px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }

    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 25px 0 10px;
        position: relative;
        padding: 0 40px;
    }
    
    .stepper-line-bg {
        position: absolute;
        top: 24px;
        left: 100px;
        right: 100px;
        height: 4px;
        background-color: #e6e6e6;
        z-index: 1;
    }
    
    .stepper-line-active {
        position: absolute;
        top: 24px;
        left: 100px;
        height: 4px;
        background-color: #00b207;
        z-index: 2;
        transition: width 0.3s;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3;
        width: 120px;
    }

    .step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: white;
        border: 2px solid #e6e6e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        color: #bbb;
        transition: all 0.3s;
    }

    .step-item.active .step-circle {
        border-color: #00b207;
        color: #00b207;
        background-color: white;
    }

    .step-item.completed .step-circle {
        background-color: #00b207;
        border-color: #00b207;
        color: white;
    }

    .step-label {
        margin-top: 10px;
        font-size: 13px;
        font-weight: 500;
        color: #999;
        transition: color 0.3s;
        text-align: center;
    }

    .step-item.active .step-label,
    .step-item.completed .step-label {
        color: #00b207;
        font-weight: 600;
    }

    /* Products Details List Table */
    .products-table-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
        overflow: hidden;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .products-table th {
        font-size: 12px;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        padding: 16px 24px;
        border-bottom: 1px solid #e6e6e6;
        background-color: #fafafa;
    }

    .products-table td {
        padding: 20px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f2f2f2;
    }

    .products-table tr:last-child td {
        border-bottom: none;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .product-img-wrap {
        width: 65px;
        height: 65px;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #e6e6e6;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3px;
        flex-shrink: 0;
    }

    .product-img-wrap img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .product-title {
        font-size: 14.5px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .product-price-col {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 500;
    }

    .product-qty-col {
        font-size: 14px;
        color: #666;
    }

    .product-subtotal-col {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 600;
    }
    
    .products-table-card {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        .details-header-flex {
            flex-direction: column;
            align-items: flex-start;
        }
        .details-header-actions {
            width: 100%;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }
        .details-header-actions a, .details-header-actions button {
            flex: 1;
            text-align: center;
            padding: 8px 12px;
            font-size: 13px;
        }
        .stepper-container {
            flex-direction: column;
            align-items: flex-start;
            padding: 10px 20px;
            gap: 30px;
        }
        .stepper-line-bg {
            top: 20px;
            bottom: 20px;
            left: 42px;
            width: 4px;
            height: auto;
            right: auto;
        }
        .stepper-line-active {
            top: 20px;
            left: 42px;
            width: 4px !important;
            height: calc(({{ $connectorWidth }} / 100) * (100% - 40px)) !important;
            right: auto;
        }
        .step-item {
            flex-direction: row;
            width: 100%;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
            text-align: left;
        }
        .step-label {
            margin-top: 0;
            text-align: left;
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
        
        <!-- Right Side: Order Details Panel -->
        <div class="col-lg-9 col-md-8">
            <div class="details-container">
                
                @php
                    $isPending = Str::contains(strtolower($order->order_status), ['menunggu', 'pending']);
                @endphp
                
                <div class="details-header-flex">
                    <div class="details-title-meta">
                        <h2 class="details-title">Order Details</h2>
                        <div class="dot-separator"></div>
                        <div>{{ $order->created_at->format('M d, Y') }}</div>
                        <div class="dot-separator"></div>
                        <div>{{ count($items) }} Products</div>
                    </div>
                    
                    <div class="details-header-actions">
                        <a href="{{ route('order.history') }}" class="green-link">Back to List</a>
                        
                        @if($isPending)
                            <button onclick="cancelOrderDetail('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-top">Cancel Order</button>
                        @elseif(Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']))
                            <a href="{{ route('order.return', $order->id) }}" class="btn-return-top"><i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>Return Order</a>
                        @else
                            <button class="btn-cancel-top disabled" disabled>Cancel Order</button>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <!-- Address Card -->
                    <div class="col-md-4 mb-4">
                        <div class="summary-card">
                            <div class="summary-card-title">Address</div>
                            <div class="summary-card-name" id="displayBillingName">{{ $user->nama }}</div>
                            <div class="summary-card-address" id="displayBillingAddress">pekanbaru, Rumbai, Umban Sari</div>
                            <div class="summary-card-contact">
                                <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
                            </div>
                            <div class="summary-card-contact">
                                <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estimated Arrival Card -->
                    <div class="col-md-4 mb-4">
                        <div class="summary-card">
                            <div class="summary-card-title">Estimated Arrival</div>
                            @if(Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']))
                                <div class="summary-card-name" style="color: #00b207;">Delivered</div>
                                <div class="summary-card-address">Your order has been safely delivered to your address.</div>
                            @elseif(Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed']))
                                <div class="summary-card-name" style="color: #ea4b48;">Canceled</div>
                                <div class="summary-card-address">This order was canceled and is not scheduled for arrival.</div>
                            @else
                                <div class="summary-card-name">{{ $estimatedArrival }}</div>
                                <div class="summary-card-address">Order is currently scheduled to arrive during this window.</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Order ID & Pricing Card -->
                    <div class="col-md-4 mb-4">
                        <div class="summary-card">
                            <div class="summary-card-title">Summary</div>
                            <div class="summary-calc-list">
                                <div class="summary-calc-row">
                                    <span>Order ID:</span>
                                    <span style="font-weight: 600; color: #1a1a1a;">{{ $order->order_number }}</span>
                                </div>
                                <div class="summary-calc-row">
                                    <span>Payment Method:</span>
                                    <span style="font-weight: 500;">{{ Str::contains(strtolower($order->order_status), ['selesai', 'completed']) ? 'Paypal' : 'COD' }}</span>
                                </div>
                                <div class="summary-calc-row" style="margin-top: 5px;">
                                    <span>Subtotal:</span>
                                    <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="summary-calc-row">
                                    <span>Discount:</span>
                                    <span>0%</span>
                                </div>
                                <div class="summary-calc-row">
                                    <span>Shipping:</span>
                                    <span>Free</span>
                                </div>
                                <div class="summary-calc-row main-total">
                                    <span>Total:</span>
                                    <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tracking Status Card -->
                <div class="tracking-card">
                    @php
                        $statusStep = 1;
                        $isCanceled = Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed']);
                        
                        if (!$isCanceled) {
                            if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered'])) {
                                $statusStep = 4;
                            } elseif (Str::contains(strtolower($order->order_status), ['otw', 'way', 'kirim', 'sedang dikirim'])) {
                                $statusStep = 3;
                            } elseif (Str::contains(strtolower($order->order_status), ['proses', 'processing', 'ready'])) {
                                $statusStep = 2;
                            }
                        }
                        
                        // Connector line width percentage
                        $connectorWidth = 0;
                        if ($statusStep == 2) $connectorWidth = 33.3;
                        elseif ($statusStep == 3) $connectorWidth = 66.6;
                        elseif ($statusStep == 4) $connectorWidth = 100;
                    @endphp
                    
                    @if($isCanceled)
                        <div style="background-color: #fdf0ea; color: #ea4b48; border-radius: 6px; padding: 15px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 14px;">
                            <i class="fa-solid fa-circle-xmark" style="font-size: 18px;"></i>
                            <span>This order has been Canceled</span>
                        </div>
                    @else
                        <div class="stepper-container">
                            <div class="stepper-line-bg"></div>
                            <div class="stepper-line-active" style="width: calc(({{ $connectorWidth }} / 100) * (100% - 200px));"></div>
                            
                            <!-- Step 1: Order received -->
                            <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}">
                                <div class="step-circle">
                                    @if($statusStep > 1)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        01
                                    @endif
                                </div>
                                <div class="step-label">Order received</div>
                            </div>
                            
                            <!-- Step 2: Processing -->
                            <div class="step-item {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}">
                                <div class="step-circle">
                                    @if($statusStep > 2)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        02
                                    @endif
                                </div>
                                <div class="step-label">Processing</div>
                            </div>
                            
                            <!-- Step 3: On the way -->
                            <div class="step-item {{ $statusStep >= 3 ? ($statusStep > 3 ? 'completed' : 'active') : '' }}">
                                <div class="step-circle">
                                    @if($statusStep > 3)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        03
                                    @endif
                                </div>
                                <div class="step-label">On the way</div>
                            </div>
                            
                            <!-- Step 4: Delivered -->
                            <div class="step-item {{ $statusStep >= 4 ? 'completed' : '' }}">
                                <div class="step-circle">
                                    @if($statusStep >= 4)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        04
                                    @endif
                                </div>
                                <div class="step-label">Delivered</div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Products Details List Card -->
                <div class="products-table-card">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Product</th>
                                <th style="width: 15%;">Price</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 20%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrap">
                                                @if($item['product']->foto_1)
                                                    <img src="{{ asset('storage/' . $item['product']->foto_1) }}" alt="{{ $item['product']->nama_produk }}">
                                                @else
                                                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Placeholder">
                                                @endif
                                            </div>
                                            <span class="product-title">{{ $item['product']->nama_produk }}</span>
                                        </div>
                                    </td>
                                    <td class="product-price-col">Rp. {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="product-qty-col">x{{ $item['qty'] }}</td>
                                    <td class="product-subtotal-col">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <!-- Mock products table matching the screenshot if no products in DB -->
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrap">
                                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Mock Product">
                                            </div>
                                            <span class="product-title">Red Capsicum</span>
                                        </div>
                                    </td>
                                    <td class="product-price-col">Rp. 64.000</td>
                                    <td class="product-qty-col">x2</td>
                                    <td class="product-subtotal-col">Rp. 128.000</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrap">
                                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Mock Product">
                                            </div>
                                            <span class="product-title">Green Capsicum</span>
                                        </div>
                                    </td>
                                    <td class="product-price-col">Rp. 64.000</td>
                                    <td class="product-qty-col">x2</td>
                                    <td class="product-subtotal-col">Rp. 128.000</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrap">
                                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Mock Product">
                                            </div>
                                            <span class="product-title">Green Chili</span>
                                        </div>
                                    </td>
                                    <td class="product-price-col">Rp. 64.000</td>
                                    <td class="product-qty-col">x2</td>
                                    <td class="product-subtotal-col">Rp. 128.000</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<script>
    // Load profile/address sync from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const storedName = localStorage.getItem('zyh_cust_name') || "{{ $user->nama }}";
        const storedAddress = localStorage.getItem('zyh_cust_address') || "pekanbaru, Rumbai, Umban Sari";
        const storedEmail = localStorage.getItem('zyh_cust_email') || "{{ $user->email }}";
        const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) {{ $user->no_hp }}";
        
        document.querySelectorAll('#displayBillingName').forEach(el => el.textContent = storedName);
        document.querySelectorAll('#displayBillingAddress').forEach(el => el.textContent = storedAddress);
        document.querySelectorAll('#displayBillingEmail').forEach(el => el.textContent = storedEmail);
        document.querySelectorAll('#displayBillingPhone').forEach(el => el.textContent = storedPhone);
    });

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
    function cancelOrderDetail(id, orderNumber) {
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
                
                fetch(`/order-history/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            title: 'Dibatalkan!',
                            text: data.message || 'Pesanan Anda telah berhasil dibatalkan.',
                            icon: 'success',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        }).then(() => {
                            window.location.href = "{{ route('order.history') }}";
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Gagal membatalkan pesanan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem.',
                        icon: 'error',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    });
                });
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
</script>
@endsection
