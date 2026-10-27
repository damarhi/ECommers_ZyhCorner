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
                        'new' => [
                            'title' => 'Pengajuan pengembalian baru',
                            'desc' => 'Tim kami sedang meninjau permintaan pengembalian Anda.',
                        ],
                        'processed' => [
                            'title' => 'Sedang diproses',
                            'desc' => 'Tim kami sedang memproses pengembalian Anda.',
                        ],
                        'accepted' => [
                            'title' => 'Pengembalian diterima',
                            'desc' => 'Permintaan pengembalian Anda telah disetujui.',
                        ],
                        'rejected' => [
                            'title' => 'Pengembalian ditolak',
                            'desc' => 'Maaf, permintaan pengembalian Anda tidak dapat diproses.',
                        ],
                        'done' => ['title' => 'Pengembalian selesai', 'desc' => 'Proses pengembalian telah selesai.'],
                    ];
                    $bannerInfo = $statusMessages[$returStatusClass] ?? [
                        'title' => $retur->status,
                        'desc' => 'Status pengembalian Anda.',
                    ];
                @endphp

                <!-- Page Header -->
                <div class="detail-header">
                    <div class="detail-header-left">
                        <h2>Return Detail</h2>
                        <div class="subtitle">Order {{ $retur->order_id }} &bull;
                            {{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}
                        </div>
                    </div>
                    <a href="{{ route('order.history', ['status' => 'return']) }}" class="btn-back-link">
                        <i class="fa-solid fa-arrow-left"></i> Back to Returns
                    </a>
                </div>

                <!-- Return Status Banner -->
                <div class="return-status-banner status-{{ $returStatusClass }}">
                    <div class="banner-icon status-{{ $returStatusClass }}">
                        @if ($returStatusClass === 'new')
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
                                <span
                                    style="
                                font-size: 12px;
                                font-weight: 600;
                                padding: 3px 12px;
                                border-radius: 50px;
                                @if ($returStatusClass === 'new') background: #fff5eb; color: #ff8a00;
                                @elseif($returStatusClass === 'processed') background: #eaf7fb; color: #2196f3;
                                @elseif($returStatusClass === 'accepted') background: #edf8ee; color: #00b207;
                                @elseif($returStatusClass === 'rejected') background: #fdf0ea; color: #ea4b48;
                                @elseif($returStatusClass === 'done') background: #edf8ee; color: #00b207; @endif
                            ">{{ $retur->status }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Return Date</span>
                            <span
                                class="info-value">{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : '-' }}</span>
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
                        @if ($order)
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
                                <span class="info-value" style="font-weight: 700; color: #00b207;">Rp.
                                    {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                            <div style="margin-top: 12px;">
                                <a href="{{ route('order.detail', $order->id) }}"
                                    style="color: #00b207; font-size: 13px; font-weight: 600; text-decoration: none;">
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 4px;"></i>View
                                    Full Order
                                </a>
                            </div>
                        @else
                            <div style="color: #999; font-size: 14px; padding: 20px 0; text-align: center;">
                                <i class="fa-solid fa-circle-info"
                                    style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                                Order data not available
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Products in this order -->
                @if ($order && count($items) > 0)
                    <div class="products-section">
                        <div class="products-section-header">Products in Order</div>
                        @foreach ($items as $item)
                            <div class="product-item">
                                <div class="product-thumb">
                                    @if ($item['product']->gambar)
                                        <img src="{{ asset('storage/' . $item['product']->gambar) }}"
                                            alt="{{ $item['product']->nama_produk }}">
                                    @else
                                        <img src="{{ asset('image/logo_circle.png') }}"
                                            alt="{{ $item['product']->nama_produk }}">
                                    @endif
                                </div>
                                <div class="product-info">
                                    <div class="product-name">{{ $item['product']->nama_produk }}</div>
                                    <div class="product-price-qty">Rp. {{ number_format($item['price'], 0, ',', '.') }} ×
                                        {{ $item['qty'] }}</div>
                                </div>
                                <div class="product-subtotal">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
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
                            <div class="timeline-date">
                                {{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div
                                class="timeline-dot {{ in_array($returStatusClass, ['processed', 'accepted', 'done']) ? 'active' : (in_array($returStatusClass, ['rejected']) ? 'danger' : '') }}">
                            </div>
                            <div class="timeline-label">
                                @if ($returStatusClass === 'rejected')
                                    Return reviewed — Rejected
                                @elseif(in_array($returStatusClass, ['processed', 'accepted', 'done']))
                                    Return reviewed
                                @else
                                    Under review
                                @endif
                            </div>
                            <div class="timeline-date">
                                @if (in_array($returStatusClass, ['processed', 'accepted', 'rejected', 'done']))
                                    {{ $retur->updated_at->format('d M, Y') }}
                                @else
                                    Pending
                                @endif
                            </div>
                        </div>

                        @if ($returStatusClass !== 'rejected')
                            <div class="timeline-item">
                                <div
                                    class="timeline-dot {{ in_array($returStatusClass, ['accepted', 'done']) ? 'active' : '' }}">
                                </div>
                                <div class="timeline-label">Return accepted</div>
                                <div class="timeline-date">
                                    @if (in_array($returStatusClass, ['accepted', 'done']))
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
                                    @if ($returStatusClass === 'done')
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


@endsection
