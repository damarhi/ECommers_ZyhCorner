@extends('Layout.layout_pelanggan')

@php
    $isCanceled = $order->is_canceled;
    $statusStep = $order->status_step;
    $connectorWidth = match($statusStep) {
        2 => 25,
        3 => 50,
        4 => 75,
        5 => 100,
        default => 0,
    };
    $rawCustPhone = (string)($user->no_hp ?? '');
    $cleanCustPhone = preg_replace('/^(\+?62|0)/', '', $rawCustPhone);
    $formattedCustPhone = $cleanCustPhone ? '(+62) ' . $cleanCustPhone : '-';

    $sm = strtolower($order->shipping_method ?? '');
    $courierIcon = 'fa-motorcycle';
    if (\Illuminate\Support\Str::contains($sm, ['jne', 'sicepat', 'j&t', 'antar', 'reguler'])) {
        $courierIcon = 'fa-truck';
    } elseif (\Illuminate\Support\Str::contains($sm, ['ambil', 'pickup', 'pick up'])) {
        $courierIcon = 'fa-shop';
    }
@endphp

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

<link rel="stylesheet" href="{{ asset('css/css_order_detail.css') }}?v={{ file_exists(public_path('css/css_order_detail.css')) ? filemtime(public_path('css/css_order_detail.css')) : time() }}">

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
        <x-customer-sidebar active="order_detail" />
        
        <!-- Right Side: Order Details Panel -->
        <div class="col-lg-9 col-md-8">
            <div class="details-container">
                
                @php
                    $isCanceled = $order->is_canceled;
                    $isCompleted = $order->is_completed;
                    $isShipping = $order->is_shipping;
                    $isProcessing = $order->is_processing;
                    $isAwaitingConfirmation = $order->is_awaiting_confirmation;
                    $isPending = $order->is_pending;
                    $canBeCanceled = $order->can_be_canceled;
                    $statusStep = $order->status_step;

                    $pillClass = 'badge-status-processing';
                    $pillIcon = 'fa-box-open';
                    $pillLabel = 'Sedang Diproses';

                    if ($isCompleted) {
                        $pillClass = 'badge-status-completed';
                        $pillIcon = 'fa-circle-check';
                        $pillLabel = 'Pesanan Selesai';
                    } elseif ($isShipping) {
                        $pillClass = 'badge-status-shipping';
                        $pillIcon = 'fa-truck-fast';
                        $pillLabel = 'Sedang Dikirim';
                    } elseif ($isProcessing) {
                        $pillClass = 'badge-status-processing';
                        $pillIcon = 'fa-box-open';
                        $pillLabel = 'Sedang Diproses';
                    } elseif ($isAwaitingConfirmation) {
                        $pillClass = 'badge-status-awaiting';
                        $pillIcon = 'fa-clock-rotate-left';
                        $pillLabel = 'Menunggu Konfirmasi';
                    } elseif ($isPending) {
                        $pillClass = 'badge-status-pending';
                        $pillIcon = 'fa-hourglass-half';
                        $pillLabel = 'Menunggu Pembayaran';
                    } elseif ($isCanceled) {
                        $pillClass = 'badge-status-canceled';
                        $pillIcon = 'fa-circle-xmark';
                        $pillLabel = 'Pesanan Dibatalkan';
                    }

                    $connectorWidth = match($statusStep) {
                        2 => 25,
                        3 => 50,
                        4 => 75,
                        5 => 100,
                        default => 0,
                    };
                @endphp
                
                <!-- Order Top Navigation -->
                <div class="order-top-nav-bar">
                    <a href="{{ route('order.history') }}" class="green-link">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>

                <div class="details-header-flex">
                    <div class="details-title-meta">
                        <div class="details-title-row">
                            <h2 class="details-title">Pesanan {{ $order->order_number }}</h2>
                            <span class="order-status-pill {{ $pillClass }}">
                                <i class="fa-solid {{ $pillIcon }}"></i> {{ $pillLabel }}
                            </span>
                        </div>
                        <div class="details-meta-sub">
                            <span><i class="fa-regular fa-calendar-days me-1"></i> {{ $order->created_at->format('d M Y, H:i') }}</span>
                            <span class="dot-separator"></span>
                            <span><i class="fa-solid fa-basket-shopping me-1"></i> {{ count($items) }} Produk</span>
                        </div>
                    </div>
                    
                    <div class="details-header-actions">
                        <div class="header-actions-group">
                            @php
                                $isOrderProcessingOrDone = in_array(strtolower($order->order_status ?? ''), ['dikemas', 'dikirim', 'selesai', 'batal', 'cancelled']);
                                $isRejected = $order->is_payment_proof_rejected;
                            @endphp
                            @if($order->is_bank_transfer && $order->payment_status !== 'Paid' && !$isOrderProcessingOrDone)
                                <button type="button" onclick="openUploadProofModal()" class="btn-track-order-top btn-pay-now-top btn-pay-now-solid" style="background: {{ $isRejected ? '#dc2626' : '#0284c7' }}; border-color: {{ $isRejected ? '#dc2626' : '#0284c7' }};">
                                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ $isRejected ? 'Upload Ulang Bukti' : ($order->has_payment_proof ? 'Ganti Bukti Bayar' : 'Upload Bukti Bayar') }}
                                </button>
                            @elseif($order->is_bank_transfer && $order->has_payment_proof)
                                <button type="button" onclick="openUploadProofModal(true)" class="btn-track-order-top btn-pay-now-top btn-pay-now-solid" style="background: #f1f5f9; border-color: #cbd5e1; color: #475569;">
                                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> Lihat Bukti Bayar
                                </button>
                            @elseif($isPending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                                <button type="button" onclick="payOrderNow('{{ $order->id }}')" class="btn-track-order-top btn-pay-now-top btn-pay-now-solid">
                                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                                </button>
                            @endif
                            <button type="button" onclick="openBiteshipTrackingModal()" class="btn-track-order-top">
                                <i class="fa-solid fa-location-crosshairs me-1"></i> Lacak Pesanan
                            </button>

                            @if($isCompleted)
                                @php
                                    $existingReturRecord = \App\Models\Retur::where('order_id', $order->order_number)->first();
                                    $alreadyReturned = !is_null($existingReturRecord);
                                    $returnExpired = $order->updated_at && \Carbon\Carbon::parse($order->updated_at)->addDay()->isPast();
                                @endphp
                                @if (!$alreadyReturned && !$returnExpired)
                                    <a href="{{ route('order.return', $order->id) }}" class="btn-return-top"><i class="fa-solid fa-rotate-left me-1"></i>Ajukan Retur</a>
                                @elseif ($alreadyReturned)
                                    <span class="btn-return-top btn-return-disabled">
                                        <i class="fa-solid fa-check me-1"></i>Sudah Diretur
                                    </span>
                                @else
                                    <span class="btn-return-top btn-return-disabled">
                                        <i class="fa-solid fa-clock me-1"></i>Batas Retur Lewat
                                    </span>
                                @endif
                            @endif

                            @if($canBeCanceled)
                                <button onclick="cancelOrderDetail('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-top">
                                    <i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan
                                </button>
                            @elseif(!$isCanceled)
                                <button class="btn-cancel-top disabled" disabled title="Pesanan tidak dapat dibatalkan"><i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if($isRejected)
                    <div class="return-status-banner mb-4" style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <i class="fa-solid fa-triangle-exclamation text-danger mt-1" style="font-size: 20px;"></i>
                                <div>
                                    <div style="font-size: 14px; font-weight: 700; color: #991b1b; margin-bottom: 3px;">
                                        Bukti Pembayaran Ditolak oleh Kasir
                                    </div>
                                    <div style="font-size: 13px; color: #b91c1c; margin-bottom: 6px;">
                                        Alasan: <strong>{{ $order->payment_proof_rejection_reason ?? 'Bukti transfer tidak valid atau belum sesuai.' }}</strong>
                                    </div>
                                    <div style="font-size: 12px; color: #7f1d1d;">
                                        Silakan periksa kembali transfer Anda dan unggah berkas bukti pembayaran baru.
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="openUploadProofModal()" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1.5" style="border-radius: 6px; padding: 6px 14px; font-size: 12.5px; white-space: nowrap; align-self: center;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Ulang Bukti
                            </button>
                        </div>
                    </div>
                @endif

                @if(isset($alreadyReturned) && $alreadyReturned)
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fa-solid fa-circle-check"></i>
                            <div>
                                <h4>Pengajuan Retur Telah Diterima</h4>
                                <p>Pengembalian untuk pesanan ini telah diajukan (Status: <strong>{{ $existingReturRecord->status }}</strong>). Anda dapat memantau status retur di menu Riwayat Retur.</p>
                            </div>
                        </div>
                        <a href="{{ route('return.detail', $existingReturRecord->id) }}" class="btn btn-sm btn-outline-success" style="border-radius: 6px; font-weight: 600; white-space: nowrap;">Lihat Detail Retur</a>
                    </div>
                @elseif(isset($returnExpired) && $returnExpired && !$alreadyReturned && $isCompleted)
                    <div class="return-status-banner return-expired">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            <h4>Batas Waktu Retur Berakhir</h4>
                            <p>Batas waktu pengajuan retur (24 jam setelah pesanan selesai) telah berakhir.</p>
                        </div>
                    </div>
                @endif

                @if ($isPending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                    <div class="payment-countdown-banner">
                        <div class="payment-countdown-content">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <div>
                                <h4>Selesaikan Pembayaran Anda</h4>
                                <p>Silakan lakukan pembayaran sebelum waktu habis agar pesanan tidak otomatis dibatalkan.</p>
                            </div>
                        </div>
                        <div class="countdown-timer-wrapper">
                            <span class="countdown-timer-label">Sisa Waktu</span>
                            <span id="paymentCountdown" class="countdown-timer-clock">00:00:00</span>
                        </div>
                    </div>
                @endif

                <!-- Three Information Summary Cards -->
                <div class="row summary-cards-row mb-4">
                    <!-- Address Card -->
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="summary-card">
                            <div>
                                <div class="summary-card-title">
                                    <i class="fa-solid fa-location-dot"></i> Alamat Pengiriman
                                </div>
                                <div class="summary-card-name" id="displayBillingName">{{ $order->customer_name ?? $user->nama }}</div>
                                <div class="summary-card-address" id="displayBillingAddress">{{ $order->resolved_address['street'] ?? $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
                            </div>
                            <div class="summary-card-footer">
                                <button type="button" class="btn-detail-address" onclick="openAddressDetailModal()">
                                    <i class="fa-regular fa-id-card me-1"></i> Lihat Detail Alamat
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Estimated Arrival & Shipping Card -->
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="summary-card">
                            <div>
                                <div class="summary-card-title">
                                    <span><i class="fa-solid fa-truck-fast"></i> Info Pengiriman</span>
                                </div>
                                
                                @php
                                    $resiCode = $order->resi_code;
                                @endphp

                                <div class="summary-calc-list">
                                    <div class="summary-calc-row">
                                        <span>Kurir:</span>
                                        <span style="font-weight: 600; color: #0f172a; text-align: right;">
                                            {{ $order->shipping_method ?? 'Kurir Toko / Pengiriman Standar' }}
                                        </span>
                                    </div>

                                    <div class="summary-calc-row">
                                        <span>No. Resi:</span>
                                        <span style="display: flex; align-items: center; gap: 6px;">
                                            <span class="waybill-code" id="orderResiCode">{{ $resiCode }}</span>
                                            <button type="button" class="btn-copy-resi" onclick="copyTrackingResi('{{ $resiCode }}')" title="Salin nomor resi">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>

                                    <div class="summary-calc-row">
                                        <span>Status Kirim:</span>
                                        <div>
                                            @if($isCompleted)
                                                <span class="shipping-status-badge badge-delivered">Sampai di Tujuan</span>
                                            @elseif($isCanceled)
                                                <span class="shipping-status-badge badge-canceled">Batal Kirim</span>
                                            @elseif($isShipping)
                                                <span class="shipping-status-badge badge-transit">Dalam Perjalanan</span>
                                            @elseif($isProcessing)
                                                <span class="shipping-status-badge badge-processing">Sedang Dikemas</span>
                                            @elseif($isAwaitingConfirmation)
                                                <span class="shipping-status-badge badge-processing" style="color: #d97706 !important;">Menunggu Konfirmasi</span>
                                            @else
                                                <span class="shipping-status-badge badge-pending">Menunggu Proses</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="summary-calc-row">
                                        <span>Perkiraan Tiba:</span>
                                        <span style="font-weight: 600; color: #0f172a; text-align: right;">
                                            @if($isCompleted)
                                                <span class="shipping-status-badge badge-delivered">Diterima</span>
                                            @elseif($isCanceled)
                                                <span class="shipping-status-badge badge-canceled">Batal</span>
                                            @else
                                                {{ $estimatedArrival }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order ID & Pricing Card -->
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="summary-card">
                            <div>
                                <div class="summary-card-title">
                                    <i class="fa-solid fa-receipt"></i> Rincian Pembayaran
                                </div>
                                <div class="summary-calc-list">
                                    <div class="summary-calc-row">
                                        <span>ID Pesanan:</span>
                                        <span style="font-weight: 700; color: #0f172a;">{{ $order->order_number }}</span>
                                    </div>
                                    <div class="summary-calc-row">
                                        <span>Metode Bayar:</span>
                                        <span style="font-weight: 600; color: #0f172a;">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                                    </div>
                                    <div class="summary-calc-row">
                                        <span>Status Bayar:</span>
                                        @if(strtoupper($order->payment_status) === 'PAID')
                                            <span class="payment-status-badge badge-paid">Lunas</span>
                                        @elseif(strtoupper($order->payment_status) === 'CANCELLED')
                                            <span class="payment-status-badge badge-canceled">Batal</span>
                                        @else
                                            <span class="payment-status-badge badge-pending">Menunggu</span>
                                        @endif
                                    </div>
                                    @if($order->is_bank_transfer)
                                        <div class="summary-calc-row">
                                            <span>Bukti Transfer:</span>
                                            @if($order->has_payment_proof)
                                                <a href="javascript:void(0)" onclick="openUploadProofModal({{ ($order->payment_status === 'Paid' || $isOrderProcessingOrDone) ? 'true' : 'false' }})" style="font-weight: 600; color: #0284c7; text-decoration: underline; font-size: 12.5px;">
                                                    <i class="fa-solid fa-file-circle-check me-1"></i> Terkirim (Lihat)
                                                </a>
                                            @elseif($isRejected)
                                                <a href="javascript:void(0)" onclick="openUploadProofModal(false)" style="font-weight: 600; color: #dc2626; text-decoration: underline; font-size: 12.5px;" title="{{ $order->payment_proof_rejection_reason }}">
                                                    <i class="fa-solid fa-circle-exclamation me-1"></i> Ditolak (Upload Ulang)
                                                </a>
                                            @elseif(!$isOrderProcessingOrDone && $order->payment_status !== 'Paid')
                                                <a href="javascript:void(0)" onclick="openUploadProofModal(false)" style="font-weight: 600; color: #f59e0b; text-decoration: underline; font-size: 12.5px;">
                                                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> Belum Diupload
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size: 12.5px;">-</span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="summary-calc-row">
                                        <span>Ongkos Kirim:</span>
                                        @if(($shippingCost ?? 0) > 0)
                                            <span style="color: #0f172a; font-weight: 600;">Rp. {{ number_format($shippingCost, 0, ',', '.') }}</span>
                                        @else
                                            <span style="color: #00b207; font-weight: 600;">Gratis</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="summary-card-footer summary-total-footer">
                                <div class="summary-calc-row main-total">
                                    <span>Total Tagihan:</span>
                                    <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Tracking Status Card -->
                <div class="tracking-card mb-4" role="region" aria-label="Status Progres Pengiriman">
                    <div class="tracking-card-header">
                        <div class="tracking-title-wrap">
                            <div class="tracking-title">
                                <i class="fa-solid fa-route text-success me-1"></i> Status Progres Pengiriman
                            </div>
                            @if(!$isCanceled)
                                <span class="stepper-live-chip">
                                    <span class="live-dot-pulse"></span>
                                    {{ $pillLabel }}
                                </span>
                            @endif
                        </div>
                        <div class="tracking-est-pill">
                            <i class="fa-regular fa-clock me-1 text-muted"></i> Estimasi Tiba: <strong>{{ $estimatedArrival }}</strong>
                        </div>
                    </div>
                    
                    @if($isCanceled)
                        <div class="order-canceled-alert">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <div>
                                <strong style="display: block; font-size: 14px;">Pesanan Dibatalkan</strong>
                                <span style="font-size: 13px; font-weight: 400; opacity: 0.9;">Pesanan ini telah dibatalkan dan pengiriman dihentikan.</span>
                            </div>
                        </div>
                    @else
                        @php
                            $isCod = strtoupper($order->payment_method ?? '') === 'COD';
                            $isPaid = strtoupper($order->payment_status ?? '') === 'PAID';
                        @endphp
                        <div class="stepper-container" role="progressbar" aria-valuenow="{{ $statusStep }}" aria-valuemin="1" aria-valuemax="5">
                            <!-- Step 1: Pesanan Dibuat -->
                            <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 1 ? 'step' : 'false' }}">
                                <div class="step-node-wrap">
                                    <div class="step-circle">
                                        @if($statusStep > 1)
                                            <i class="fa-solid fa-check"></i>
                                        @elseif($statusStep === 1)
                                            <span class="micro-active-dot"></span>
                                        @else
                                            <i class="fa-solid fa-receipt"></i>
                                        @endif
                                    </div>
                                    <div class="step-trail-line {{ $statusStep >= 2 ? 'trail-solid' : 'trail-dashed' }}"></div>
                                </div>
                                <div class="step-content">
                                    <div class="step-label">Pesanan Dibuat</div>
                                    <div class="step-sub-wrap">
                                        <span class="step-sub-meta">
                                            <i class="fa-regular fa-calendar-check me-1"></i>{{ $order->created_at ? $order->created_at->format('d M, H:i') : 'Tercatat' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Verifikasi Pembayaran -->
                            <div class="step-item {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 2 ? 'step' : 'false' }}">
                                <div class="step-node-wrap">
                                    <div class="step-circle">
                                        @if($statusStep > 2)
                                            <i class="fa-solid fa-check"></i>
                                        @elseif($statusStep === 2)
                                            <span class="micro-active-dot"></span>
                                        @else
                                            <i class="fa-solid fa-credit-card"></i>
                                        @endif
                                    </div>
                                    <div class="step-trail-line {{ $statusStep >= 3 ? 'trail-solid' : 'trail-dashed' }}"></div>
                                </div>
                                <div class="step-content">
                                    <div class="step-label">Verifikasi Pembayaran</div>
                                    <div class="step-sub-wrap">
                                        @if($isCod)
                                            <span class="step-sub-meta"><i class="fa-solid fa-hand-holding-dollar me-1"></i>Bayar saat diterima</span>
                                        @elseif($isPaid)
                                            <span class="step-sub-meta text-success"><i class="fa-solid fa-circle-check me-1"></i>Lunas</span>
                                        @else
                                            <span class="step-sub-meta"><i class="fa-regular fa-clock me-1"></i>Menunggu</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 3: Dikonfirmasi & Dikemas -->
                            <div class="step-item {{ $statusStep >= 3 ? ($statusStep > 3 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 3 ? 'step' : 'false' }}">
                                <div class="step-node-wrap">
                                    <div class="step-circle">
                                        @if($statusStep > 3)
                                            <i class="fa-solid fa-check"></i>
                                        @elseif($statusStep === 3)
                                            <span class="micro-active-dot"></span>
                                        @else
                                            <i class="fa-solid fa-box-open"></i>
                                        @endif
                                    </div>
                                    <div class="step-trail-line {{ $statusStep >= 4 ? 'trail-solid' : 'trail-dashed' }}"></div>
                                </div>
                                <div class="step-content">
                                    <div class="step-label">Dikonfirmasi &amp; Dikemas</div>
                                    <div class="step-sub-wrap">
                                        @if($statusStep >= 3)
                                            <span class="step-sub-meta {{ $statusStep === 3 ? 'text-success' : '' }}"><i class="fa-solid fa-kitchen-set me-1"></i>Dapur Zyh Corner</span>
                                        @elseif($statusStep == 2)
                                            <span class="step-sub-meta"><i class="fa-solid fa-clock-rotate-left me-1"></i>Menunggu Konfirmasi</span>
                                        @else
                                            <span class="step-sub-meta">Persiapan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 4: Sedang Dikirim -->
                            <div class="step-item {{ $statusStep >= 4 ? ($statusStep > 4 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 4 ? 'step' : 'false' }}">
                                <div class="step-node-wrap">
                                    <div class="step-circle">
                                        @if($statusStep > 4)
                                            <i class="fa-solid fa-check"></i>
                                        @elseif($statusStep === 4)
                                            <span class="micro-active-dot"></span>
                                        @else
                                            <i class="fa-solid fa-truck-fast"></i>
                                        @endif
                                    </div>
                                    <div class="step-trail-line {{ $statusStep >= 5 ? 'trail-solid' : 'trail-dashed' }}"></div>
                                </div>
                                <div class="step-content">
                                    <div class="step-label">Sedang Dikirim</div>
                                    <div class="step-sub-wrap">
                                        @if($statusStep >= 4)
                                            <span class="step-sub-meta {{ $statusStep === 4 ? 'text-success' : '' }}"><i class="fa-solid fa-truck-arrow-right me-1"></i>{{ Str::limit($order->shipping_method ?? 'Kurir Pengantaran', 18) }}</span>
                                        @else
                                            <span class="step-sub-meta"><i class="fa-solid fa-truck me-1"></i>{{ Str::limit($order->shipping_method ?? 'Kurir Pengantaran', 18) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 5: Pesanan Selesai -->
                            <div class="step-item {{ $statusStep >= 5 ? 'completed' : '' }}" aria-current="{{ $statusStep >= 5 ? 'step' : 'false' }}">
                                <div class="step-node-wrap">
                                    <div class="step-circle">
                                        @if($statusStep >= 5)
                                            <i class="fa-solid fa-check"></i>
                                        @else
                                            <i class="fa-solid fa-house-chimney"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="step-content">
                                    <div class="step-label">Pesanan Selesai</div>
                                    <div class="step-sub-wrap">
                                        @if($statusStep >= 5)
                                            <span class="step-sub-meta text-success"><i class="fa-solid fa-circle-check me-1"></i>Tiba di Tujuan</span>
                                        @else
                                            <span class="step-sub-meta"><i class="fa-solid fa-location-dot me-1"></i>Tujuan Alamat</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Products Details List Card -->
                <div class="products-table-card">
                    <div class="products-card-header">
                        <div class="products-card-title">
                            <i class="fa-solid fa-bag-shopping text-success"></i> Rincian Produk Dipesan ({{ count($items) }})
                        </div>
                        <div class="products-card-total">
                            Total Pesanan: <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="table-responsive desktop-products-table-wrap">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th class="col-product-main">Produk</th>
                                    <th class="col-product-price">Harga Satuan</th>
                                    <th class="col-product-qty">Jumlah</th>
                                    <th class="col-product-subtotal">Subtotal</th>
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
                                                        <img src="{{ asset('image/logo_circle.png') }}" alt="{{ $item['product']->nama_produk }}">
                                                    @endif
                                                </div>
                                                <div class="product-info-wrap">
                                                    <span class="product-title">{{ $item['product']->nama_produk }}</span>
                                                    @if(!empty($item['custom_text']))
                                                        <div class="custom-cake-text-pill">
                                                            <i class="fa-solid fa-pen-nib me-1"></i> Tulisan Kue: "{{ $item['custom_text'] }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="product-price-col" style="text-align: right;">Rp. {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="product-qty-col" style="text-align: center;">
                                            <span class="qty-badge">x{{ $item['qty'] }}</span>
                                        </td>
                                        <td class="product-subtotal-col" style="text-align: right;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="products-table-empty">
                                            <i class="fa-solid fa-box-open"></i>
                                            Tidak ada rincian produk untuk pesanan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Product Items List (Hidden on desktop, visible on mobile <= 768px) -->
                    <div class="mobile-products-list">
                        @forelse($items as $item)
                            <div class="mobile-product-card">
                                <div class="mobile-product-card-top">
                                    <div class="mobile-product-img-wrap">
                                        @if($item['product']->foto_1)
                                            <img src="{{ asset('storage/' . $item['product']->foto_1) }}" alt="{{ $item['product']->nama_produk }}">
                                        @else
                                            <img src="{{ asset('image/logo_circle.png') }}" alt="{{ $item['product']->nama_produk }}">
                                        @endif
                                    </div>
                                    <div class="mobile-product-details">
                                        <div class="mobile-product-title">{{ $item['product']->nama_produk }}</div>
                                        @if(!empty($item['custom_text']))
                                            <div class="custom-cake-text-pill mobile-custom-text-pill">
                                                <i class="fa-solid fa-pen-nib me-1"></i> Tulisan Kue: "{{ $item['custom_text'] }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mobile-product-card-bottom">
                                    <div class="mobile-product-qty-price">
                                        <span class="mobile-qty-pill">x{{ $item['qty'] }}</span>
                                        <span class="mobile-unit-price">@ Rp. {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="mobile-product-subtotal-wrap">
                                        <span class="mobile-subtotal-label">Subtotal:</span>
                                        <span class="mobile-subtotal-val">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="products-table-empty">
                                <i class="fa-solid fa-box-open"></i>
                                Tidak ada rincian produk untuk pesanan ini.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
        
    </div>
</div>

<!-- Delivery Address Detail Modal -->
<div class="biteship-modal-backdrop" id="orderAddressDetailModal" onclick="if(event.target === this) closeAddressDetailModal();">
    <div class="biteship-modal-dialog modal-address-dialog" role="dialog" aria-modal="true" aria-labelledby="modalAddressTitle">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <h3 id="modalAddressTitle" class="biteship-modal-title" style="font-size: 15.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-map-location-dot text-success"></i> Informasi Alamat Pengiriman
                </h3>
                <div class="biteship-modal-subtitle">Pesanan <strong>{{ $order->order_number }}</strong></div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeAddressDetailModal()" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="biteship-modal-body modal-address-body">
            <!-- Card 1: Penerima & Kontak -->
            <div class="modal-section-card">
                <div class="section-card-head">
                    <span class="section-card-title">
                        <i class="fa-regular fa-user text-success me-1"></i> Kontak Penerima
                    </span>
                </div>
                <div class="section-card-grid">
                    <div class="modal-kv-item">
                        <span class="modal-kv-label">Nama Penerima</span>
                        <span class="modal-kv-value font-semibold">{{ $order->customer_name ?? $user->nama }}</span>
                    </div>
                    <div class="modal-kv-item">
                        <span class="modal-kv-label">Nomor Telepon</span>
                        <span id="displayBillingPhoneModal" class="modal-kv-value">
                            <i class="fa-solid fa-phone text-muted me-1" style="font-size: 11px;"></i>{{ $formattedCustPhone }}
                        </span>
                    </div>
                </div>
                <div class="section-card-divider"></div>
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Email Pembeli</span>
                    <span id="displayBillingEmailModal" class="modal-kv-value" style="font-weight: 500; font-size: 13px;">
                        <i class="fa-regular fa-envelope text-muted me-1" style="font-size: 11px;"></i>{{ $order->email_pembeli ?? $user->email }}
                    </span>
                </div>
            </div>

            <!-- Card 2: Alamat Pengiriman -->
            <div class="modal-section-card">
                <div class="section-card-head">
                    <span class="section-card-title">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i> Alamat Lengkap
                    </span>
                    @if(!empty($order->resolved_address['gps']))
                        <span class="badge" style="background: #e8f5e9; color: #2e7d32; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                            📍 GPS Terverifikasi
                        </span>
                    @endif
                </div>
                <div class="modal-kv-box address-box">
                    <div style="font-weight: 600; color: #1e293b;">
                        {{ $order->resolved_address['street'] ?? $order->alamat_pengiriman ?? $user->alamat ?? '-' }}
                    </div>
                    @if(!empty($order->resolved_address['detected_place']) && !str_contains($order->resolved_address['street'] ?? '', $order->resolved_address['detected_place']))
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                            <strong>Landmark:</strong> {{ $order->resolved_address['detected_place'] }}
                        </div>
                    @endif
                    @if(!empty($order->resolved_address['area']) || !empty($order->resolved_address['postal_code']))
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            {{ $order->resolved_address['area'] ?? '' }} ({{ $order->resolved_address['postal_code'] ?? '' }})
                        </div>
                    @endif
                    @if(!empty($order->resolved_address['gps']))
                        <div style="margin-top: 6px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 11px; font-family: monospace; color: #64748b;">📍 {{ $order->resolved_address['gps'] }}</span>
                            <a href="{{ $order->resolved_address['maps_url'] ?? 'https://maps.google.com/?q=' . urlencode($order->resolved_address['gps']) }}" target="_blank" rel="noopener noreferrer" style="font-size: 11px; color: #2563eb; text-decoration: underline;">Buka Peta ↗</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 3: Catatan Pengiriman (Kondisional) -->
            @php
                $cleanNotes = $order->clean_notes;
            @endphp
            @if(!empty($cleanNotes))
                <div class="modal-section-card note-card">
                    <div class="section-card-head">
                        <span class="section-card-title text-amber">
                            <i class="fa-regular fa-note-sticky text-amber me-1"></i> Catatan Pengiriman
                        </span>
                    </div>
                    <div class="modal-kv-box notes-box">{{ $cleanNotes }}</div>
                </div>
            @endif
        </div>

        <div class="biteship-modal-footer">
            <button type="button" class="btn-modal-close" onclick="closeAddressDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- Biteship Tracking Modal -->
<div class="biteship-modal-backdrop" id="biteshipTrackingModal" onclick="if(event.target === this) closeBiteshipTrackingModal();">
    <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTrackingTitle">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <span class="visually-hidden">Powered by Biteship</span>
                <h3 id="modalTrackingTitle" class="biteship-modal-title">Pelacakan Pengiriman</h3>
                <div class="biteship-modal-subtitle">Pesanan <strong>{{ $order->order_number }}</strong> • Resi: <strong id="modalResiCodeDisplay">{{ $resiCode }}</strong></div>
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
                    <span class="value"><i class="fa-solid {{ $courierIcon }} text-success me-1"></i> {{ $order->shipping_method ?? 'Kurir Toko / Pengiriman Standar' }}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Nomor Resi</span>
                    <span class="value" style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-family: monospace; font-size: 12.5px;">{{ $resiCode }}</span>
                        <button type="button" class="btn-copy-resi" onclick="copyTrackingResi('{{ $resiCode }}')" title="Salin resi">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="label">Penerima</span>
                    <span class="value">{{ $order->customer_name ?? $user->nama }}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Status Pengiriman</span>
                    <span class="value">
                        @if($isCompleted)
                            <span class="badge-track-pill pill-success"><i class="fa-solid fa-circle-check me-1"></i> Sampai di Tujuan</span>
                        @elseif($isCanceled)
                            <span class="badge-track-pill pill-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Pengiriman Batal</span>
                        @elseif($isShipping)
                            <span class="badge-track-pill pill-info"><i class="fa-solid fa-truck-fast me-1"></i> Sedang Dikirim</span>
                        @elseif($isProcessing)
                            <span class="badge-track-pill pill-warning"><i class="fa-solid fa-box me-1"></i> Sedang Dikemas</span>
                        @else
                            <span class="badge-track-pill pill-secondary"><i class="fa-regular fa-clock me-1"></i> Menunggu Proses</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Checkpoint Timeline -->
            <div class="tracking-timeline-container">
                <h4 class="timeline-heading"><i class="fa-solid fa-route me-2 text-success"></i> Status Perjalanan Paket</h4>
                
                <div class="tracking-timeline">
                    @if($isCanceled)
                        <!-- Step 1: Pesanan Dibuat -->
                        <div class="timeline-step active">
                            <div class="timeline-marker marker-success">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Pesanan Dikonfirmasi</div>
                                <div class="timeline-time">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
                                <div class="timeline-desc">Pesanan sempat terdaftar di sistem Zyh Corner.</div>
                            </div>
                        </div>

                        <!-- Step 2: Dibatalkan -->
                        <div class="timeline-step active">
                            <div class="timeline-marker marker-danger">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title text-danger">Pengiriman Dibatalkan</div>
                                <div class="timeline-time">{{ $order->updated_at ? $order->updated_at->format('d M Y, H:i') . ' WIB' : 'Hari ini' }}</div>
                                <div class="timeline-desc">Proses pengiriman dihentikan karena pesanan dibatalkan.</div>
                            </div>
                        </div>
                    @else
                        <!-- Step 1: Pesanan Dikonfirmasi & Sedang Disiapkan (Paling Atas) -->
                        <div class="timeline-step active">
                            <div class="timeline-marker marker-success">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">{{ $isAwaitingConfirmation ? 'Menunggu Konfirmasi Toko' : ($isPending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi & Sedang Disiapkan') }}</div>
                                <div class="timeline-time">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
                                <div class="timeline-desc">{{ $isAwaitingConfirmation ? 'Pesanan telah diterima dan sedang menunggu konfirmasi staf kasir/dapur Zyh Corner. Anda masih dapat membatalkan pesanan ini jika diperlukan.' : ($isPending ? 'Menunggu penyelesaian pembayaran sebelum diteruskan ke dapur.' : 'Dapur Zyh Corner telah mengonfirmasi pesanan dan menyiapkan hidangan dengan higienis.') }}</div>
                            </div>
                        </div>

                        <!-- Step 2: Kurir Dialokasikan (Driver Assigned) -->
                        <div class="timeline-step {{ $statusStep >= 2 ? 'active' : '' }}">
                            <div class="timeline-marker {{ $statusStep >= 2 ? 'marker-success' : 'marker-muted' }}">
                                <i class="fa-solid {{ $statusStep >= 2 ? 'fa-check' : 'fa-user-check' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Kurir Dialokasikan (Driver Assigned)</div>
                                <div class="timeline-time">{{ $statusStep >= 2 ? $order->created_at->addMinutes(15)->format('d M Y, H:i') . ' WIB' : 'Menunggu pesanan siap' }}</div>
                                <div class="timeline-desc">Sistem logistik Biteship menetapkan kurir terdekat untuk penjemputan paket.</div>
                            </div>
                        </div>

                        <!-- Step 3: Paket Diambil oleh Kurir (Picked Up) -->
                        <div class="timeline-step {{ $statusStep >= 3 ? 'active' : '' }}">
                            <div class="timeline-marker {{ $statusStep >= 3 ? 'marker-success' : ($statusStep == 2 ? 'marker-active' : 'marker-muted') }}">
                                <i class="fa-solid {{ $statusStep >= 3 ? 'fa-check' : 'fa-box' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Paket Telah Diambil oleh Kurir</div>
                                <div class="timeline-time">{{ $statusStep >= 3 ? $order->created_at->addMinutes(30)->format('d M Y, H:i') . ' WIB' : 'Menunggu penjemputan' }}</div>
                                <div class="timeline-desc">Driver telah mengambil paket dari outlet Zyh Corner dalam kondisi aman dan tersegel.</div>
                            </div>
                        </div>

                        <!-- Step 4: Dalam Perjalanan (In Transit) -->
                        <div class="timeline-step {{ $statusStep >= 3 ? 'active' : '' }}">
                            <div class="timeline-marker {{ $statusStep >= 4 ? 'marker-success' : ($statusStep == 3 ? 'marker-active' : 'marker-muted') }}">
                                <i class="fa-solid {{ $statusStep >= 4 ? 'fa-check' : 'fa-motorcycle' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Paket Sedang Diantar ke Alamat Tujuan</div>
                                <div class="timeline-time">{{ $statusStep >= 3 ? $order->created_at->addMinutes(45)->format('d M Y, H:i') . ' WIB' : 'Menunggu kurir membawa paket' }}</div>
                                <div class="timeline-desc">Kurir Biteship sedang dalam perjalanan mengantarkan paket menuju alamat pelanggan.</div>
                            </div>
                        </div>

                        <!-- Step 5: Paket Sampai di Tujuan / Pesanan Diterima (Paling Bawah) -->
                        <div class="timeline-step {{ $isCompleted ? 'active' : '' }}">
                            <div class="timeline-marker {{ $isCompleted ? 'marker-success' : 'marker-muted' }}">
                                <i class="fa-solid {{ $isCompleted ? 'fa-check' : 'fa-house-chimney' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Paket Sampai di Tujuan (Pesanan Diterima)</div>
                                <div class="timeline-time">{{ $isCompleted ? ($order->updated_at ? $order->updated_at->format('d M Y, H:i') . ' WIB' : 'Hari ini') : 'Estimasi: ' . $estimatedArrival }}</div>
                                <div class="timeline-desc">Pesanan telah sampai di lokasi tujuan dan diterima dengan baik oleh pelanggan.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="biteship-modal-footer">
            <button type="button" class="btn-modal-close" onclick="closeBiteshipTrackingModal()">Tutup</button>
            <a href="https://wa.me/{{ $storeSettings['wa_number'] ?? ($storePhone ?? \App\Models\StoreSetting::getWaNumber()) }}?text={{ urlencode('Halo ' . ($storeSettings['store_name'] ?? ($storeName ?? 'Zyh Corner')) . ', saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $resiCode . ')') }}" target="_blank" class="btn-modal-wa">
                <i class="fa-brands fa-whatsapp me-1"></i> Hubungi CS Toko
            </a>
        </div>
    </div>
</div>

<!-- Upload Proof Modal for Customer Order Detail -->
<div class="biteship-modal-backdrop" id="uploadProofModal" onclick="if(event.target === this) closeUploadProofModal();">
    <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalUploadTitle" style="max-width: 520px;">
        <div class="biteship-modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
            <div class="biteship-modal-title-group">
                <h3 id="modalUploadTitle" class="biteship-modal-title" style="font-size: 16px; font-weight: 700; color: #1e293b;">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>Upload Bukti Pembayaran
                </h3>
                <div class="biteship-modal-subtitle" style="font-size: 12px; color: #64748b;">
                    Pesanan <strong>{{ $order->order_number }}</strong> • Total Tagihan: <strong style="color: #00b207;">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                </div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeUploadProofModal()" aria-label="Tutup modal">
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
                    <button type="button" onclick="copyDetailModalAcc('{{ $storeBank['account_number'] ?? '8295-0192-38' }}')" class="btn btn-sm btn-outline-secondary" style="font-size: 11px; padding: 2px 8px;">
                        <i class="fa-regular fa-copy me-1"></i> Salin
                    </button>
                </div>
            </div>
            @if($isRejected)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 12px; font-weight: 700; color: #991b1b; margin-bottom: 2px;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Bukti Sebelumnya Ditolak Kasir:
                    </div>
                    <div style="font-size: 11.5px; color: #b91c1c;">
                        {{ $order->payment_proof_rejection_reason ?? 'Bukti pembayaran sebelumnya tidak valid.' }}
                    </div>
                </div>
            @endif

            @if($order->has_payment_proof)
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="{{ $order->payment_proof_url }}" target="_blank">
                            <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #86efac;">
                        </a>
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #166534;"><i class="fa-solid fa-circle-check me-1"></i> Bukti Telah Diunggah</div>
                            <div style="font-size: 11px; color: #15803d;">{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }} WIB</div>
                        </div>
                    </div>
                    <a href="{{ $order->payment_proof_url }}" target="_blank" class="btn btn-sm" style="font-size: 11.5px; color: #15803d; text-decoration: underline; font-weight: 600;">Lihat Foto</a>
                </div>
            @endif

            <!-- Dropzone -->
            <div class="proof-dropzone" id="modalProofDropzone" onclick="document.getElementById('modalProofInput').click()" style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 18px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;">
                <input type="file" id="modalProofInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;" onchange="handleModalProofSelect(event)">
                <div id="modalDropzonePrompt">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 28px; color: #94a3b8; margin-bottom: 6px;"></i>
                    <div style="font-size: 13px; font-weight: 600; color: #334155;">
                        @if($isRejected)
                            Klik atau Drag Foto Bukti Transfer Baru ke Sini
                        @elseif($order->has_payment_proof)
                            Pilih foto baru untuk mengganti bukti
                        @else
                            Klik atau Drag Foto Bukti Transfer ke Sini
                        @endif
                    </div>
                </div>
                <div id="modalDropzonePreview" style="display: none; align-items: center; gap: 12px; text-align: left;">
                    <img id="modalPreviewImg" src="" alt="Pratinjau" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <div style="flex-grow: 1; overflow: hidden;">
                        <div id="modalPreviewFileName" style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">nama_file.jpg</div>
                        <div id="modalPreviewFileSize" style="font-size: 11px; color: #64748b;">120 KB</div>
                    </div>
                    <button type="button" onclick="clearModalProof(event)" style="background: none; border: none; color: #ef4444; font-size: 16px; cursor: pointer; padding: 4px;" title="Hapus Berkas">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="biteship-modal-footer" style="border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeUploadProofModal()" style="font-size: 13px; padding: 7px 16px; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Batal</button>
            <button type="button" class="btn btn-primary" id="btnSubmitModalProof" onclick="submitDetailModalProof()" style="font-size: 13px; padding: 7px 18px; border-radius: 6px; background: {{ $isRejected ? '#dc2626' : '#00b207' }}; border-color: {{ $isRejected ? '#dc2626' : '#00b207' }};">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> {{ $isRejected ? 'Simpan & Kirim Ulang Bukti' : 'Simpan & Kirim Bukti' }}
            </button>
        </div>
    </div>
</div>
<script>
    window.orderDetailConfig = {
        csrfToken: "{{ csrf_token() }}",
        uploadProofUrl: "{{ route('order.upload.proof', $order->id) }}",
        cancelUrlTemplate: "{{ route('order.cancel', ':id') }}",
        orderHistoryUrl: "{{ route('order.history') }}",
        payUrlTemplate: "{{ route('order.pay', ':id') }}",
        checkPaymentUrlTemplate: "{{ route('order.checkPayment', ':id') }}",
        orderName: @json($order->customer_name ?? $user->nama),
        orderEmail: @json($order->email_pembeli ?? $user->email),
        orderPhone: @json($formattedCustPhone ?? ($user->no_hp ? '(+62) ' . $user->no_hp : '')),
        isPending: @json((bool) $isPending),
        expiryTimestamp: @json(isset($expiryTimestamp) ? $expiryTimestamp : null)
    };

    // Auto-open modal if URL has ?track=1
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('track') === '1') {
            openBiteshipTrackingModal();
        }
    });
</script>
<script src="{{ asset('js/js_order_detail.js') }}?v={{ file_exists(public_path('js/js_order_detail.js')) ? filemtime(public_path('js/js_order_detail.js')) : time() }}"></script>
@endsection
