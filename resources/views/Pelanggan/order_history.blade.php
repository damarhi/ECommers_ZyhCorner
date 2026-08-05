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
        .btn-pay-now-solid {
            background-color: #00b207 !important;
            color: white !important;
            border: none !important;
            border-radius: 4px !important;
            padding: 8px 16px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: 0.3s !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        .btn-pay-now-solid:hover {
            background-color: #008f05 !important;
        }
    </style>
@endsection

@section('content')
    @php
        $isProduction = config('services.midtrans.is_production', false);
        $snapUrl = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
        $clientKey = config('services.midtrans.client_key');
    @endphp
    <script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>

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

                    @if ($statusFilter !== 'return')
                        <div class="order-cards-list">
                            @forelse($orders as $order)
                                @php
                                    // Normalize status for design matching
                                    $statusClass = 'pending';
                                    $statusLabel = $order->order_status;

                                    if (
                                        Str::contains(strtolower($order->order_status), [
                                            'selesai',
                                            'completed',
                                            'delivered',
                                        ])
                                    ) {
                                        $statusClass = 'completed';
                                        $statusLabel = 'Completed';
                                    } elseif (
                                        Str::contains(strtolower($order->order_status), [
                                            'otw',
                                            'way',
                                            'kirim',
                                            'sedang dikirim',
                                        ])
                                    ) {
                                        $statusClass = 'on_the_way';
                                        $statusLabel = 'On the Way';
                                    } elseif (
                                        Str::contains(strtolower($order->order_status), [
                                            'proses',
                                            'processing',
                                            'ready',
                                        ])
                                    ) {
                                        $statusClass = 'processing';
                                        $statusLabel = 'Processing';
                                    } elseif (
                                        Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed'])
                                    ) {
                                        $statusClass = 'cancelled';
                                        $statusLabel = 'Canceled';
                                    } elseif (Str::contains(strtolower($order->order_status), ['menunggu'])) {
                                        $statusClass = 'pending';
                                        $statusLabel = 'Processing';
                                    }

                                    $firstItem = null;
                                    $itemCount = 0;
                                    $totalQty = 0;
                                    $orderItems = $order->items;
                                    
                                    if (is_array($orderItems) && count($orderItems) > 0) {
                                        $firstItem = $orderItems[0];
                                        $itemCount = count($orderItems);
                                        foreach ($orderItems as $item) {
                                            $totalQty += isset($item['qty']) ? intval($item['qty']) : 1;
                                        }
                                    }
                                    
                                    $displayImg = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop';
                                    if ($firstItem && isset($firstItem['img'])) {
                                        $displayImg = $firstItem['img'];
                                    }
                                @endphp

                                <div class="order-card-row">
                                    <div class="order-left-details" style="cursor: pointer;"
                                        onclick="window.location.href='{{ route('order.detail', $order->id) }}'">
                                        <div class="order-img-wrapper">
                                            <img src="{{ $displayImg }}" alt="Product Image">
                                            @if ($itemCount > 1)
                                                <div class="extra-badge">+{{ $itemCount - 1 }}</div>
                                            @endif
                                        </div>
                                        <div class="order-meta-info">
                                            <div class="order-date-label">{{ $order->created_at->format('d M, Y') }}</div>
                                            <div class="order-total-summary">
                                                Total: <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                                                @if ($totalQty > 1)
                                                    ({{ $totalQty }} Products)
                                                @else
                                                    (1 Product)
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="order-right-actions">
                                        <div class="order-buttons-group">
                                            @if (Str::contains(strtolower($order->order_status), ['menunggu', 'pending']))
                                                @if (strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                                                    <button
                                                        onclick="payOrderNow('{{ $order->id }}')"
                                                        class="btn-pay-now-solid"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
                                                @endif
                                                <button
                                                    onclick="cancelOrder('{{ $order->id }}', '{{ $order->order_number }}')"
                                                    class="btn-cancel-outline">Cancel Order</button>
                                            @endif
                                            @if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']))
                                                @php
                                                    $alreadyReturned = \App\Models\Retur::where('order_id', $order->order_number)->exists();
                                                    $returnExpired = $order->updated_at && \Carbon\Carbon::parse($order->updated_at)->addDay()->isPast();
                                                @endphp
                                                @if (!$alreadyReturned && !$returnExpired)
                                                    <a href="{{ route('order.return', $order->id) }}"
                                                        class="btn-return-outline"><i class="fa-solid fa-rotate-left"
                                                            style="margin-right: 4px;"></i>Return</a>
                                                @elseif ($alreadyReturned)
                                                    <span class="btn-return-outline" style="opacity: 0.5; pointer-events: none; cursor: not-allowed;">
                                                        <i class="fa-solid fa-check" style="margin-right: 4px;"></i>Sudah Diretur
                                                    </span>
                                                @else
                                                    <span class="btn-return-outline" style="opacity: 0.5; pointer-events: none; cursor: not-allowed;">
                                                        <i class="fa-solid fa-clock" style="margin-right: 4px;"></i>Retur Kadaluarsa
                                                    </span>
                                                @endif
                                            @endif
                                            <div class="tracking-action-wrapper">
                                                @if (!(Str::contains(strtolower($order->order_status), ['menunggu', 'pending']) && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid'))
                                                <div class="order-status-text status-{{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </div>
                                                @endif
                                                <button onclick="trackOrder('{{ $order->order_number }}')"
                                                    class="btn-tracking-solid">Tracking Order</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-orders-message" style="text-align: center; padding: 60px 20px; color: #666; width: 100%; background: #fff; border-radius: 8px; border: 1px solid #f0f0f0;">
                                    <i class="fa-solid fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                    <span>Belum ada riwayat pesanan untuk kategori ini.</span>
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- Return Items Section --}}
                    @if ($statusFilter === 'return')
                        <div class="return-cards-list">
                            @if ($returs->count() > 0)
                                @foreach ($returs as $retur)
                                    @php
                                        $returStatusClass = strtolower(str_replace(' ', '-', $retur->status));
                                        if ($returStatusClass === 'new') {
                                            $returStatusClass = 'new';
                                        } elseif ($returStatusClass === 'processed') {
                                            $returStatusClass = 'processed';
                                        } elseif ($returStatusClass === 'accepted') {
                                            $returStatusClass = 'accepted';
                                        } elseif ($returStatusClass === 'rejected') {
                                            $returStatusClass = 'rejected';
                                        } elseif ($returStatusClass === 'done') {
                                            $returStatusClass = 'done';
                                        }
                                    @endphp
                                    <div class="return-card-row" style="cursor: pointer;"
                                        onclick="window.location.href='{{ route('return.detail', $retur->id) }}'">
                                        <div class="return-left-info">
                                            <div class="return-icon-circle">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </div>
                                            <div class="return-meta">
                                                <div class="return-order-id">Order {{ $retur->order_id }}</div>
                                                <div class="return-product-name">{{ $retur->product_name }}</div>
                                                <div class="return-reason"><i class="fa-solid fa-comment-dots"
                                                        style="margin-right: 4px;"></i>{{ Str::limit($retur->reason, 60) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="return-right-info">
                                            <div class="return-date">
                                                {{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d M, Y') : $retur->created_at->format('d M, Y') }}
                                            </div>
                                            <span
                                                class="return-status-badge status-{{ $returStatusClass }}">{{ $retur->status }}</span>
                                            <span style="font-size: 12px; color: #00b207; font-weight: 600;">View Detail <i
                                                    class="fa-solid fa-chevron-right" style="font-size: 10px;"></i></span>
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
                    @if ($statusFilter !== 'return')
                        @if ($orders->hasPages())
                            <div class="pagination-container"
                                style="display: flex; justify-content: center; gap: 10px; margin-top: 40px; align-items: center;">
                                {{-- Previous Button --}}
                                @if ($orders->onFirstPage())
                                    <span class="pagination-btn disabled"
                                        style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: not-allowed;"><i
                                            class="fa-solid fa-chevron-left"></i></span>
                                @else
                                    <a href="{{ $orders->previousPageUrl() }}" class="pagination-btn"
                                        style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;"><i
                                            class="fa-solid fa-chevron-left"></i></a>
                                @endif

                                {{-- Page Links --}}
                                @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                    @if ($page == $orders->currentPage())
                                        <span class="pagination-btn active"
                                            style="width: 36px; height: 36px; border-radius: 50%; background-color: #00b207; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="pagination-btn"
                                            style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($orders->hasMorePages())
                                    <a href="{{ $orders->nextPageUrl() }}" class="pagination-btn"
                                        style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #666; transition: 0.3s;"><i
                                            class="fa-solid fa-chevron-right"></i></a>
                                @else
                                    <span class="pagination-btn disabled"
                                        style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: not-allowed;"><i
                                            class="fa-solid fa-chevron-right"></i></span>
                                @endif
                            </div>
                        @endif
                    @endif

                </div>
            </div>

        </div>
    </div>


    <script>
        function payOrderNow(orderId) {
            Swal.fire({
                title: 'Mendapatkan Token Pembayaran...',
                text: 'Harap tunggu.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/order-history/${orderId}/pay`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Pembayaran berhasil dikonfirmasi.',
                                    icon: 'success',
                                    confirmButtonColor: '#00b207'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            onPending: function(result) {
                                Swal.fire({
                                    title: 'Tertunda',
                                    text: 'Pembayaran sedang diproses atau tertunda.',
                                    icon: 'info',
                                    confirmButtonColor: '#00b207'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            onError: function(result) {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: 'Pembayaran gagal dilakukan.',
                                    icon: 'error',
                                    confirmButtonColor: '#00b207'
                                });
                            },
                            onClose: function() {
                                Swal.fire({
                                    title: 'Dibatalkan',
                                    text: 'Anda menutup pop-up pembayaran.',
                                    icon: 'warning',
                                    confirmButtonColor: '#00b207'
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Gagal memulai pembayaran.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Koneksi ke server gagal.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                    console.error(err);
                });
        }
    </script>
@endsection
