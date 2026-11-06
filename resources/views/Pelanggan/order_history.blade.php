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
    <link rel="stylesheet" href="{{ asset('css/css_order_history.css') }}?v={{ file_exists(public_path('css/css_order_history.css')) ? filemtime(public_path('css/css_order_history.css')) : time() }}">
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

            <!-- Left Side: Navigation Sidebar Component -->
            <x-customer-sidebar active="order.history" />

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
                                            @php
                                                $isHistRejected = $order->is_payment_proof_rejected;
                                            @endphp
                                            @if ($order->is_bank_transfer && $order->payment_status !== 'Paid' && !in_array(strtolower($order->order_status ?? ''), ['dikemas', 'dikirim', 'selesai', 'batal', 'cancelled']))
                                                <button type="button"
                                                    onclick="openHistoryProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($order->total, 0, ',', '.') }}', '{{ $order->payment_proof_url }}', {{ $order->has_payment_proof ? 1 : 0 }}, '{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }}', false, {{ $isHistRejected ? 1 : 0 }}, '{{ addslashes($order->payment_proof_rejection_reason ?? '') }}')"
                                                    class="btn-pay-now-solid" style="background: {{ $isHistRejected ? '#dc2626 !important' : '#0284c7 !important' }}; border-color: {{ $isHistRejected ? '#dc2626 !important' : '#0284c7 !important' }}; color: #ffffff !important;">
                                                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ $isHistRejected ? 'Upload Ulang Bukti' : ($order->has_payment_proof ? 'Ganti Bukti' : 'Upload Bukti') }}
                                                </button>
                                            @elseif ($order->is_bank_transfer && $order->has_payment_proof)
                                                <button type="button"
                                                    onclick="openHistoryProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($order->total, 0, ',', '.') }}', '{{ $order->payment_proof_url }}', 1, '{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }}', true)"
                                                    class="btn-pay-now-solid" style="background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #475569 !important;">
                                                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> Lihat Bukti
                                                </button>
                                            @elseif ($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
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

    <!-- Biteship Tracking Modal Component -->
    <x-biteship-tracking-modal />

    <!-- Upload Proof Modal for Order History -->
    <div class="biteship-modal-backdrop" id="historyProofModal" style="display: none;" onclick="if(event.target === this) closeHistoryProofModal();">
        <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="historyUploadTitle" style="max-width: 520px;">
            <div class="biteship-modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <div class="biteship-modal-title-group">
                    <h3 id="historyUploadTitle" class="biteship-modal-title" style="font-size: 16px; font-weight: 700; color: #1e293b;">
                        <i class="fa-solid fa-receipt text-primary me-2"></i>Upload Bukti Pembayaran
                    </h3>
                    <div class="biteship-modal-subtitle" style="font-size: 12px; color: #64748b;">
                        Pesanan <strong id="histModalOrderNum">-</strong> • Tagihan: <strong style="color: #00b207;">Rp. <span id="histModalOrderTotal">0</span></strong>
                    </div>
                </div>
                <button type="button" class="biteship-modal-close" onclick="closeHistoryProofModal()" aria-label="Tutup modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="biteship-modal-body" style="padding: 20px;">
                <!-- Bank Info Mini Box -->
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 11px; font-weight: 700; background: #1E2753; color: #fff; padding: 2px 6px; border-radius: 4px;">{{ $storeBank['name'] ?? 'BCA' }}</span>
                        <span style="font-size: 11.5px; color: #475569; font-weight: 600;">a/n {{ $storeBank['account_holder'] ?? 'ZYH CORNER PASTRY' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-family: monospace; font-size: 16px; font-weight: 700; color: #0f172a;">{{ $storeBank['account_number'] ?? '8295-0192-38' }}</span>
                        <button type="button" onclick="copyHistoryModalAcc('{{ $storeBank['account_number'] ?? '8295-0192-38' }}')" class="btn btn-sm btn-outline-secondary" style="font-size: 11px; padding: 2px 8px;">
                            <i class="fa-regular fa-copy me-1"></i> Salin
                        </button>
                    </div>
                </div>
                <!-- Rejection alert container (hidden by default) -->
                <div id="histRejectionAlertBox" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 12px; font-weight: 700; color: #991b1b; margin-bottom: 2px;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Bukti Pembayaran Sebelumnya Ditolak Kasir:
                    </div>
                    <div id="histRejectionAlertText" style="font-size: 11.5px; color: #b91c1c;">
                        -
                    </div>
                </div>

                <!-- Existing proof container (hidden by default) -->
                <div id="histCurrentProofBox" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                            <img id="histCurrentProofImg" src="" alt="Bukti Transfer" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #86efac;">
                        </a>
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #166534;"><i class="fa-solid fa-circle-check me-1"></i> Bukti Telah Diunggah</div>
                            <div id="histCurrentProofTime" style="font-size: 11px; color: #15803d;">-</div>
                        </div>
                    </div>
                    <a id="histCurrentProofLinkBtn" href="#" target="_blank" class="btn btn-sm" style="font-size: 11.5px; color: #15803d; text-decoration: underline; font-weight: 600;">Lihat Foto</a>
                </div>

                <!-- Dropzone -->
                <div class="proof-dropzone" id="histModalProofDropzone" onclick="document.getElementById('histModalProofInput').click()" style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 18px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;">
                    <input type="file" id="histModalProofInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;" onchange="handleHistoryProofSelect(event)">
                    <div id="histModalDropzonePrompt">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 28px; color: #94a3b8; margin-bottom: 6px;"></i>
                        <div id="histModalDropzoneTitle" style="font-size: 13px; font-weight: 600; color: #334155;">
                            Klik atau Drag Foto Bukti Transfer ke Sini
                        </div>
                        <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Format JPG, PNG, WEBP (Maksimal 2MB)</div>
                    </div>
                    <div id="histModalDropzonePreview" style="display: none; align-items: center; gap: 12px; text-align: left;">
                        <img id="histModalPreviewImg" src="" alt="Pratinjau" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                        <div style="flex-grow: 1; overflow: hidden;">
                            <div id="histModalPreviewFileName" style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">nama_file.jpg</div>
                            <div id="histModalPreviewFileSize" style="font-size: 11px; color: #64748b;">120 KB</div>
                        </div>
                        <button type="button" onclick="clearHistoryProof(event)" style="background: none; border: none; color: #ef4444; font-size: 16px; cursor: pointer; padding: 4px;" title="Hapus Berkas">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="biteship-modal-footer" style="border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeHistoryProofModal()" style="font-size: 13px; padding: 7px 16px; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitHistProof" onclick="submitHistoryProof()" style="font-size: 13px; padding: 7px 18px; border-radius: 6px; background: #00b207; border-color: #00b207;">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Simpan & Kirim Bukti
                </button>
            </div>
        </div>
    </div>

    <script>
        window.orderHistoryConfig = {
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('js/js_order_history.js') }}?v={{ file_exists(public_path('js/js_order_history.js')) ? filemtime(public_path('js/js_order_history.js')) : time() }}"></script>
@endsection
