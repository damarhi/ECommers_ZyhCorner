@extends('Layout.layout_pelanggan')

@section('title', 'Order History - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Account</a>
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
        .btn-tracking-solid {
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
        .btn-tracking-solid:hover {
            background-color: #008f05 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 178, 7, 0.2);
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
                                    
                                    $displayImg = asset('image/logo_circle.png');
                                    if ($firstItem && !empty($firstItem['img'])) {
                                        $imgVal = $firstItem['img'];
                                        if (str_starts_with($imgVal, 'http://') || str_starts_with($imgVal, 'https://')) {
                                            $displayImg = $imgVal;
                                        } elseif (str_starts_with($imgVal, 'storage/') || str_starts_with($imgVal, 'image/')) {
                                            $displayImg = asset($imgVal);
                                        } else {
                                            $displayImg = asset('storage/' . $imgVal);
                                        }
                                    }

                                    $trackingPayload = [
                                        'order_id' => $order->id,
                                        'order_number' => $order->order_number,
                                        'resi_code' => $order->resi_code,
                                        'courier_name' => $order->formatted_courier_name,
                                        'courier_icon' => $order->courier_icon,
                                        'recipient_name' => $order->customer_name ?: $user->nama,
                                        'status_key' => $order->status_class,
                                        'status_label' => $order->status_label,
                                        'status_step' => $order->status_step,
                                        'is_canceled' => $order->is_canceled,
                                        'is_completed' => $order->is_completed,
                                        'is_shipping' => $order->is_shipping,
                                        'is_processing' => $order->is_processing,
                                        'is_pending' => $order->is_pending,
                                        'is_awaiting_confirmation' => $order->is_awaiting_confirmation,
                                        'can_be_canceled' => $order->can_be_canceled,
                                        'created_at_formatted' => $order->created_at ? $order->created_at->format('d M Y, H:i') : '-',
                                        'updated_at_formatted' => $order->updated_at ? $order->updated_at->format('d M Y, H:i') : '-',
                                        'detail_url' => route('order.detail', $order->id),
                                        'wa_url' => 'https://wa.me/' . ($storeSettings['wa_number'] ?? ($storePhone ?? \App\Models\StoreSetting::getWaNumber())) . '?text=' . urlencode('Halo ' . ($storeSettings['store_name'] ?? ($storeName ?? 'Zyh Corner')) . ', saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $order->resi_code . ')'),
                                    ];
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
                                            @if ($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                                                <button type="button"
                                                    onclick="payOrderNow('{{ $order->id }}')"
                                                    class="btn-pay-now-solid"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
                                            @endif
                                            @if ($order->can_be_canceled)
                                                <button type="button"
                                                    onclick="cancelOrder('{{ $order->id }}', '{{ $order->order_number }}')"
                                                    class="btn-cancel-outline">Cancel Order</button>
                                            @endif
                                            @if ($order->is_completed)
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
                                                @if (!($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid'))
                                                <div class="order-status-text status-{{ $order->status_class }}">
                                                    {{ $order->status_label }}
                                                </div>
                                                @endif
                                                <button type="button"
                                                    onclick="openOrderHistoryTracking({{ json_encode($trackingPayload) }})"
                                                    data-tracking-order="{{ $order->order_number }}"
                                                    data-tracking-payload="{{ json_encode($trackingPayload) }}"
                                                    class="btn-tracking-solid"
                                                    title="Lacak paket dengan Biteship">
                                                    <i class="fa-solid fa-location-crosshairs me-1"></i> Tracking Order
                                                </button>
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

    <!-- Biteship Tracking Modal for Order History -->
    <div class="biteship-modal-backdrop" id="biteshipTrackingModal" onclick="if(event.target === this) closeBiteshipTrackingModal();">
        <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTrackingTitle">
            <div class="biteship-modal-header">
                <div class="biteship-modal-title-group">
                    <span class="visually-hidden">Powered by Biteship</span>
                    <h3 id="modalTrackingTitle" class="biteship-modal-title">Pelacakan Pengiriman</h3>
                    <div class="biteship-modal-subtitle">
                        Pesanan <strong id="modalOrderNumberDisplay">-</strong> &bull; Resi: <strong id="modalResiCodeDisplay">-</strong>
                    </div>
                </div>
                <button type="button" class="biteship-modal-close" onclick="closeBiteshipTrackingModal()" aria-label="Tutup modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="biteship-modal-body">
                <!-- Order & Courier Info Strip -->
                <div class="tracking-summary-strip">
                    <div class="summary-item">
                        <span class="label">Kurir Pengiriman</span>
                        <span class="value">
                            <i class="fa-solid fa-motorcycle text-success me-1" id="modalCourierIcon"></i>
                            <span id="modalCourierName">Kurir Toko / Pengiriman Standar</span>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Nomor Resi</span>
                        <span class="value" style="display: flex; align-items: center; gap: 6px;">
                            <span id="modalResiCodeText" style="font-family: monospace; font-size: 12.5px;">-</span>
                            <button type="button" class="btn-copy-resi" id="modalCopyResiBtn" onclick="copyModalResi()" title="Salin resi">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Penerima</span>
                        <span class="value" id="modalRecipientName">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Status Pengiriman</span>
                        <span class="value" id="modalStatusBadgeContainer">-</span>
                    </div>
                </div>

                <!-- Checkpoint Timeline -->
                <div class="tracking-timeline-container">
                    <h4 class="timeline-heading"><i class="fa-solid fa-route me-2 text-success"></i> Status Perjalanan Paket</h4>
                    <div class="tracking-timeline" id="modalTimelineContainer">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>

            <div class="biteship-modal-footer">
                <div class="biteship-modal-footer-left">
                    <button type="button" class="btn-modal-close" onclick="closeBiteshipTrackingModal()">Tutup</button>
                    <a href="#" id="modalDetailLink" class="btn-modal-detail">
                        <i class="fa-solid fa-file-invoice me-1"></i> Rincian Pesanan
                    </a>
                </div>
                <div class="biteship-modal-footer-right">
                    <a href="#" id="modalWaLink" target="_blank" class="btn-modal-wa">
                        <i class="fa-brands fa-whatsapp me-1"></i> Hubungi CS Toko
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTrackingData = null;

        function openOrderHistoryTracking(data) {
            if (!data) return;
            currentTrackingData = data;

            // Header & Meta
            document.getElementById('modalOrderNumberDisplay').textContent = data.order_number;
            document.getElementById('modalResiCodeDisplay').textContent = data.resi_code;
            document.getElementById('modalResiCodeText').textContent = data.resi_code;

            // Courier & Recipient
            const iconEl = document.getElementById('modalCourierIcon');
            iconEl.className = 'fa-solid ' + (data.courier_icon || 'fa-motorcycle') + ' text-success me-1';
            document.getElementById('modalCourierName').textContent = data.courier_name || 'Kurir Toko / Pengiriman Standar';
            document.getElementById('modalRecipientName').textContent = data.recipient_name || '-';

            // Status Badge
            const badgeContainer = document.getElementById('modalStatusBadgeContainer');
            let badgeHtml = '';
            if (data.is_completed) {
                badgeHtml = '<span class="badge-track-pill pill-success"><i class="fa-solid fa-circle-check me-1"></i> Sampai di Tujuan</span>';
            } else if (data.is_canceled) {
                badgeHtml = '<span class="badge-track-pill pill-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Pengiriman Batal</span>';
            } else if (data.is_shipping) {
                badgeHtml = '<span class="badge-track-pill pill-info"><i class="fa-solid fa-truck-fast me-1"></i> Sedang Dikirim</span>';
            } else if (data.is_processing) {
                badgeHtml = '<span class="badge-track-pill pill-warning"><i class="fa-solid fa-box me-1"></i> Sedang Dikemas</span>';
            } else if (data.is_awaiting_confirmation) {
                badgeHtml = '<span class="badge-track-pill pill-amber"><i class="fa-solid fa-clock-rotate-left me-1"></i> Menunggu Konfirmasi</span>';
            } else {
                badgeHtml = '<span class="badge-track-pill pill-secondary"><i class="fa-regular fa-clock me-1"></i> Menunggu Pembayaran</span>';
            }
            badgeContainer.innerHTML = badgeHtml;

            // Links
            document.getElementById('modalDetailLink').href = data.detail_url;
            document.getElementById('modalWaLink').href = data.wa_url;

            // Dynamic Checkpoint Stepper
            const timeline = document.getElementById('modalTimelineContainer');
            let timelineHtml = '';

            if (data.is_canceled) {
                timelineHtml = `
                    <div class="timeline-step active">
                        <div class="timeline-marker marker-success"><i class="fa-solid fa-check"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Pesanan Dikonfirmasi</div>
                            <div class="timeline-time">${data.created_at_formatted} WIB</div>
                            <div class="timeline-desc">Pesanan sempat terdaftar di sistem Zyh Corner.</div>
                        </div>
                    </div>
                    <div class="timeline-step active">
                        <div class="timeline-marker marker-danger"><i class="fa-solid fa-ban"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title text-danger">Pengiriman Dibatalkan</div>
                            <div class="timeline-time">${data.updated_at_formatted} WIB</div>
                            <div class="timeline-desc">Proses logistik pengiriman dihentikan karena pesanan telah dibatalkan.</div>
                        </div>
                    </div>
                `;
            } else {
                const step = parseInt(data.status_step || 1);
                const step1Title = data.is_awaiting_confirmation 
                    ? 'Menunggu Konfirmasi Toko'
                    : (data.is_pending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi &amp; Sedang Disiapkan');
                const step1Desc = data.is_awaiting_confirmation
                    ? 'Pesanan telah diterima dan sedang menunggu konfirmasi staf kasir/dapur Zyh Corner. Anda masih dapat membatalkan pesanan ini jika diperlukan.'
                    : (data.is_pending ? 'Menunggu penyelesaian pembayaran sebelum diteruskan ke dapur.' : 'Dapur Zyh Corner telah mengonfirmasi pesanan dan menyiapkan hidangan dengan higienis.');
                const step1Marker = (data.is_awaiting_confirmation || data.is_pending) ? 'marker-amber' : 'marker-success';
                const step1Icon = (data.is_awaiting_confirmation || data.is_pending) ? 'fa-clock-rotate-left' : 'fa-check';

                timelineHtml = `
                    <div class="timeline-step active">
                        <div class="timeline-marker ${step1Marker}"><i class="fa-solid ${step1Icon}"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title">${step1Title}</div>
                            <div class="timeline-time">${data.created_at_formatted} WIB</div>
                            <div class="timeline-desc">${step1Desc}</div>
                        </div>
                    </div>
                    <div class="timeline-step ${step >= 2 ? 'active' : ''}">
                        <div class="timeline-marker ${step >= 2 ? (step === 2 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                            <i class="fa-solid ${step > 2 ? 'fa-check' : 'fa-box'}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Kurir Dialokasikan (Driver Assigned)</div>
                            <div class="timeline-time">${step >= 2 ? 'Kurir telah menerima pesanan' : 'Menunggu kurir siap'}</div>
                            <div class="timeline-desc">Sistem logistik Biteship menghubungkan pesanan dengan armada pengiriman terdekat.</div>
                        </div>
                    </div>
                    <div class="timeline-step ${step >= 3 ? 'active' : ''}">
                        <div class="timeline-marker ${step >= 3 ? (step === 3 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                            <i class="fa-solid ${step > 3 ? 'fa-check' : 'fa-truck-fast'}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Pesanan Dalam Perjalanan (In Transit)</div>
                            <div class="timeline-time">${step >= 3 ? 'Kurir sedang menuju alamat tujuan' : 'Menunggu penjemputan'}</div>
                            <div class="timeline-desc">Paket telah di-pickup dan sedang dalam rute pengantaran ke alamat pembeli.</div>
                        </div>
                    </div>
                    <div class="timeline-step ${step >= 4 ? 'active' : ''}">
                        <div class="timeline-marker ${step >= 4 ? 'marker-success' : 'marker-muted'}">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Pesanan Telah Tiba di Tujuan (Delivered)</div>
                            <div class="timeline-time">${step >= 4 ? data.updated_at_formatted + ' WIB' : 'Estimasi tiba hari ini'}</div>
                            <div class="timeline-desc">Paket telah diterima dengan baik di alamat tujuan. Terima kasih telah berbelanja di Zyh Corner!</div>
                        </div>
                    </div>
                `;
            }
            timeline.innerHTML = timelineHtml;

            // Display Modal
            const modal = document.getElementById('biteshipTrackingModal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeBiteshipTrackingModal() {
            const modal = document.getElementById('biteshipTrackingModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function copyModalResi() {
            if (!currentTrackingData || !currentTrackingData.resi_code) return;
            const code = currentTrackingData.resi_code;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => {
                    showResiToast(code);
                }).catch(() => fallbackCopyResi(code));
            } else {
                fallbackCopyResi(code);
            }
        }

        function fallbackCopyResi(code) {
            const temp = document.createElement('input');
            temp.value = code;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            showResiToast(code);
        }

        function showResiToast(code) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'No. Resi Disalin!',
                    text: code,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBiteshipTrackingModal();
            }
        });

        // Global trackOrder connector
        window.trackOrder = function(orderNumber) {
            const btn = document.querySelector(`[data-tracking-order="${orderNumber}"]`);
            if (btn) {
                btn.click();
            }
        };

        // Cancel Reason and Confirmation
        function selectCancelReason(el) {
            document.querySelectorAll('.cancel-reason-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');
            const radio = el.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        function cancelOrder(id, orderNumber) {
            Swal.fire({
                title: 'ALASAN PEMBATALAN PESANAN',
                html: `
                    <div style="text-align: left; margin-top: 10px;">
                        <label class="cancel-reason-option selected" onclick="selectCancelReason(this)">
                            <input type="radio" name="cancel_reason" value="Salah memilih alamat pengiriman" checked>
                            <span>Salah memilih alamat pengiriman</span>
                        </label>
                        <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                            <input type="radio" name="cancel_reason" value="Salah memilih produk atau varian kue">
                            <span>Salah memilih produk atau varian kue</span>
                        </label>
                        <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                            <input type="radio" name="cancel_reason" value="Ingin mengganti metode pembayaran">
                            <span>Ingin mengganti metode pembayaran</span>
                        </label>
                        <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                            <input type="radio" name="cancel_reason" value="Ingin mengubah atau menambah pesanan">
                            <span>Ingin mengubah atau menambah pesanan</span>
                        </label>
                        <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                            <input type="radio" name="cancel_reason" value="Lainnya">
                            <span>Lainnya (Alasan pribadi)</span>
                        </label>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi Batal',
                cancelButtonText: 'Kembali',
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
                            .cancel-reason-popup { border-radius: 12px !important; padding: 30px 20px !important; max-width: 520px !important; }
                            .cancel-reason-title { font-size: 16px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; text-align: left !important; color: #1a1a1a !important; padding: 0 10px !important; }
                            .cancel-reason-option { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border: 1.5px solid #e6e6e6; border-radius: 8px; cursor: pointer; transition: all 0.25s ease; background: white; margin-bottom: 10px; font-size: 14px; color: #333; }
                            .cancel-reason-option:hover { border-color: #00b207; background-color: #f8fff8; }
                            .cancel-reason-option.selected { border-color: #00b207; background-color: #f0fdf0; }
                            .cancel-reason-option input[type="radio"] { appearance: none; -webkit-appearance: none; width: 20px; height: 20px; border: 2px solid #d0d0d0; border-radius: 50%; position: relative; cursor: pointer; flex-shrink: 0; margin: 0; transition: all 0.25s ease; }
                            .cancel-reason-option input[type="radio"]:checked { border-color: #00b207; }
                            .cancel-reason-option input[type="radio"]:checked::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background-color: #00b207; border-radius: 50%; }
                            .cancel-reason-option span { flex: 1; }
                            .cancel-reason-cancel-btn { border: 2px solid #00b207 !important; color: #00b207 !important; background: white !important; border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
                            .cancel-reason-cancel-btn:hover { background: #f0fdf0 !important; }
                            .cancel-reason-confirm-btn { border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
                        `;
                        document.head.appendChild(style);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedRadio = document.querySelector('input[name="cancel_reason"]:checked');
                    const cancelReason = selectedRadio ? selectedRadio.value : 'Lainnya';

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Harap tunggu.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    fetch(`/order-history/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ cancel_reason: cancelReason })
                    })
                    .then(res => res.json())
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
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal membatalkan pesanan.',
                                icon: 'error',
                                confirmButtonColor: '#00b207'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.close();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Koneksi ke server gagal saat membatalkan pesanan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                        console.error(err);
                    });
                }
            });
        }

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
                        if (data.snap_token.startsWith('MOCK-SNAP-')) {
                            Swal.fire({
                                title: 'Simulasi Pembayaran (Mock Mode)',
                                text: 'Sistem berjalan dalam mode simulasi pengujian. Apakah Anda ingin mensimulasikan pembayaran lunas?',
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonColor: '#00b207',
                                confirmButtonText: 'Ya, Tandai Lunas',
                                cancelButtonText: 'Batal'
                            }).then((mockRes) => {
                                if (mockRes.isConfirmed) {
                                    fetch(`/order-history/${orderId}/check-payment`).then(() => window.location.reload());
                                }
                            });
                            return;
                        }
                        if (typeof snap === 'undefined' || !snap || typeof snap.pay !== 'function') {
                            Swal.fire({
                                title: 'Midtrans Belum Siap',
                                text: 'SDK Midtrans Snap belum termuat sempurna. Pastikan variabel MIDTRANS_CLIENT_KEY telah diisi di file .env dan koneksi internet aktif.',
                                icon: 'warning',
                                confirmButtonColor: '#00b207'
                            });
                            return;
                        }
                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                fetch(`/order-history/${orderId}/check-payment`)
                                    .then(() => {
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: 'Pembayaran berhasil dikonfirmasi.',
                                            icon: 'success',
                                            confirmButtonColor: '#00b207'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    });
                            },
                            onPending: function(result) {
                                fetch(`/order-history/${orderId}/check-payment`)
                                    .then(res => res.json())
                                    .then(resData => {
                                        if (resData.success && resData.status === 'Paid') {
                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Pembayaran berhasil dikonfirmasi.',
                                                icon: 'success',
                                                confirmButtonColor: '#00b207'
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Tertunda',
                                                text: 'Pembayaran sedang diproses atau tertunda.',
                                                icon: 'info',
                                                confirmButtonColor: '#00b207'
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        }
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
                                fetch(`/order-history/${orderId}/check-payment`)
                                    .then(res => res.json())
                                    .then(resData => {
                                        if (resData.success && resData.status === 'Paid') {
                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Pembayaran berhasil dikonfirmasi.',
                                                icon: 'success',
                                                confirmButtonColor: '#00b207'
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        }
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
