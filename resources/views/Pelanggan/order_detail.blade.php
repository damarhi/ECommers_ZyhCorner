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

<style>
    /* Reset clashing Bootstrap styles back to Zyh Corner custom styling */
    .breadcrumb-wrapper {
        background-color: white !important;
        padding: 20px 0 0 0 !important;
        display: block !important;
    }
    
    .breadcrumb-area {
        background: linear-gradient(135deg, #1b382b 0%, #244837 100%) !important;
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

    .order-top-nav-bar {
        display: flex;
        align-items: center;
        margin-bottom: 14px;
    }

    .details-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
    }

    .details-title-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .details-title-row {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .details-meta-sub {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .details-title {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.3px;
    }

    .order-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.2px;
        line-height: 1.4;
    }
    .order-status-pill.badge-status-completed {
        background-color: #e8f8ed;
        color: #00b207;
        border: 1px solid #b7ebc5;
    }
    .order-status-pill.badge-status-shipping {
        background-color: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }
    .order-status-pill.badge-status-processing {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .order-status-pill.badge-status-pending {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .order-status-pill.badge-status-awaiting {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .order-status-pill.badge-status-canceled {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .dot-separator {
        width: 4px;
        height: 4px;
        background-color: #cbd5e1;
        border-radius: 50%;
    }

    .details-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .green-link {
        color: #00b207 !important;
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.2s;
        font-size: 13.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 6px;
    }

    .green-link:hover {
        color: #008f05 !important;
    }

    .header-actions-group {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .btn-track-order-top,
    .btn-pay-now-top,
    .btn-pay-now-solid {
        background-color: #00b207;
        color: white;
        border: 1.5px solid #00b207;
        border-radius: 50px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(0, 178, 7, 0.18);
        text-decoration: none !important;
    }

    .btn-track-order-top:hover,
    .btn-pay-now-top:hover,
    .btn-pay-now-solid:hover {
        background-color: #008f05;
        border-color: #008f05;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 178, 7, 0.28);
    }

    .btn-cancel-top {
        background: #ffffff;
        color: #dc2626;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid #fca5a5;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    .btn-cancel-top:hover {
        background: #fee2e2;
        border-color: #ef4444;
        color: #b91c1c;
        transform: translateY(-1px);
    }

    .btn-cancel-top.disabled {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
        transform: none !important;
    }

    .btn-return-top {
        background: #fff;
        color: #d97706;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid #f59e0b;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-return-top:hover {
        background: #f59e0b;
        color: white;
    }

    /* Shipping card refined elements */
    .badge-biteship-pill {
        font-size: 10.5px;
        font-weight: 600;
        color: #0284c7;
        margin-left: auto;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: inline-flex;
        align-items: center;
        background: transparent;
        border: none;
        padding: 0;
    }

    .waybill-code {
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.5px;
        background: #f8fafc;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px dashed #cbd5e1;
    }

    .btn-copy-resi {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #00b207;
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-copy-resi:hover {
        background: #00b207;
        color: #ffffff;
        border-color: #00b207;
    }

    .shipping-status-badge {
        font-size: 13px;
        font-weight: 700;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        line-height: 1.3;
        display: inline !important;
    }
    .shipping-status-badge.badge-delivered {
        color: #16a34a !important;
    }
    .shipping-status-badge.badge-transit {
        color: #0284c7 !important;
    }
    .shipping-status-badge.badge-processing {
        color: #d97706 !important;
    }
    .shipping-status-badge.badge-pending {
        color: #64748b !important;
    }
    .shipping-status-badge.badge-canceled {
        color: #dc2626 !important;
    }

    .payment-status-badge {
        font-size: 13px;
        font-weight: 700;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        line-height: 1.3;
        display: inline !important;
        position: static !important;
        top: auto !important;
        left: auto !important;
    }
    .payment-status-badge.badge-paid {
        color: #16a34a !important;
    }
    .payment-status-badge.badge-pending {
        color: #d97706 !important;
    }
    .payment-status-badge.badge-canceled {
        color: #dc2626 !important;
    }

    .btn-detail-address {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 7px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        transition: all 0.2s ease;
        cursor: pointer;
        text-align: center;
    }
    .btn-detail-address:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .summary-calc-row .badge,
    .summary-calc-list .badge,
    .summary-card .badge {
        position: static !important;
        top: auto !important;
        left: auto !important;
    }

    /* Biteship Tracking Modal Styles */
    .biteship-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: none;
        align-items: center !important;
        justify-content: center !important;
        z-index: 999999 !important;
        padding: 16px;
        margin: 0 !important;
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .biteship-modal-backdrop.show {
        display: flex !important;
        opacity: 1 !important;
    }

    .biteship-modal-dialog {
        background: #ffffff !important;
        width: 100% !important;
        max-width: 580px !important;
        max-height: 90vh !important;
        margin: auto !important;
        position: relative !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .biteship-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .biteship-modal-title-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .biteship-modal-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #0284c7;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        padding: 2px 8px;
        border-radius: 50px;
        width: fit-content;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .biteship-modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 2px 0 0;
    }

    .biteship-modal-subtitle {
        font-size: 12.5px;
        color: #64748b;
    }

    .biteship-modal-close {
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #64748b;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .biteship-modal-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .biteship-modal-body {
        padding: 20px 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .tracking-summary-strip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .summary-item .label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }
    .summary-item .value {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
    }

    .badge-track-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 50px;
        font-size: 11.5px;
        font-weight: 600;
        width: fit-content;
    }
    .badge-track-pill.pill-success {
        background: #e8f8ed;
        color: #00b207;
        border: 1px solid #b7ebc5;
    }
    .badge-track-pill.pill-info {
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }
    .badge-track-pill.pill-warning {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .badge-track-pill.pill-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .badge-track-pill.pill-secondary {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .tracking-timeline-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .timeline-heading {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .tracking-timeline {
        position: relative;
        padding-left: 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .tracking-timeline::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 10px;
        width: 2px;
        background: #e2e8f0;
    }

    .timeline-step {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .timeline-marker {
        position: absolute;
        left: -28px;
        top: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        z-index: 2;
        background: white;
    }
    .timeline-marker.marker-success {
        background: #00b207;
        color: white;
        box-shadow: 0 0 0 3px rgba(0, 178, 7, 0.15);
    }
    .timeline-marker.marker-active {
        background: #0284c7;
        color: white;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        animation: pulseMarker 1.8s infinite;
    }
    .timeline-marker.marker-muted {
        background: #ffffff;
        border: 2px solid #cbd5e1;
        color: #94a3b8;
    }
    .timeline-marker.marker-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    @keyframes pulseMarker {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    .timeline-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }
    .timeline-step:not(.active) .timeline-title {
        color: #64748b;
    }

    .timeline-time {
        font-size: 11.5px;
        font-weight: 500;
        color: #64748b;
    }

    .timeline-desc {
        font-size: 12.5px;
        color: #475569;
        line-height: 1.45;
    }

    .biteship-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f8fafc;
    }

    .btn-modal-close {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .btn-modal-wa {
        background: #25d366;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(37, 211, 102, 0.25);
    }
    .btn-modal-wa:hover {
        background: #1ebc59;
        color: white;
        transform: translateY(-1px);
    }
    /* Summary cards grid styling */
    .summary-cards-row {
        margin-left: -7px;
        margin-right: -7px;
        margin-bottom: 24px;
    }
    .summary-cards-row > [class*="col-"] {
        padding-left: 7px;
        padding-right: 7px;
        display: flex;
        flex-direction: column;
    }
    .summary-card-footer {
        border-top: 1px dashed #f1f5f9;
        padding-top: 12px;
        margin-top: auto;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 16px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }

    .summary-card-title {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.6px;
        margin-bottom: 14px;
        text-transform: uppercase;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .summary-card-title i {
        color: #00b207;
        font-size: 14px;
    }

    .summary-card-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .summary-card-address {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .summary-card-contact {
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .summary-card-contact .label {
        color: #64748b;
        font-size: 12.5px;
    }

    /* Summary calculations card specific list */
    .summary-calc-list {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .summary-calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #475569;
        border-bottom: 1px dashed #f1f5f9;
        padding-bottom: 7px;
    }

    .summary-calc-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .summary-card-footer.summary-total-footer {
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        border-radius: 8px;
        padding: 9px 12px;
        margin-top: 10px;
    }

    .summary-calc-row.main-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        width: 100%;
        gap: 6px;
        border: none;
        margin: 0;
        padding: 0;
    }

    .summary-calc-row.main-total span:first-child {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .summary-calc-row.main-total span:last-child {
        color: #00b207;
        font-size: 15.5px;
        font-weight: 800;
        white-space: nowrap;
        letter-spacing: -0.2px;
    }

    /* Visual tracking stepper styling */
    .tracking-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        width: 100%;
    }

    .tracking-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .tracking-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tracking-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stepper-live-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .live-dot-pulse {
        width: 7px;
        height: 7px;
        background-color: #16a34a;
        border-radius: 50%;
        display: inline-block;
        animation: liveDotGlow 1.8s infinite ease-in-out;
    }

    @keyframes liveDotGlow {
        0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.6); transform: scale(0.95); }
        70% { box-shadow: 0 0 0 5px rgba(22, 163, 74, 0); transform: scale(1); }
        100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); transform: scale(0.95); }
    }

    .tracking-est-pill {
        font-size: 12.5px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 5px 14px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
    }

    .tracking-est-pill strong {
        color: #0f172a;
        margin-left: 4px;
    }

    .order-canceled-alert {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        border-radius: 8px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .order-canceled-alert i {
        font-size: 22px;
        flex-shrink: 0;
    }

    /* Option B: Clean Dashed Step Trail with Micro-Checkpoint Dots */
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin: 20px 0 10px;
        position: relative;
        padding: 0;
        width: 100%;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1 1 20%;
        max-width: 20%;
        padding: 0 4px;
    }

    .step-node-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 28px;
    }

    .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #f8fafc;
        border: 1.5px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
        color: #94a3b8;
        position: relative;
        z-index: 2;
        transition: all 0.25s ease;
    }

    .step-item.completed .step-circle {
        background-color: #00b207;
        border-color: #00b207;
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(0, 178, 7, 0.25);
    }

    .step-item.active .step-circle {
        background-color: #ffffff;
        border: 2px solid #00b207;
        color: #00b207;
        box-shadow: 0 0 0 4px rgba(0, 178, 7, 0.12);
    }

    .micro-active-dot {
        width: 10px;
        height: 10px;
        background-color: #00b207;
        border-radius: 50%;
        display: inline-block;
        animation: microPulse 1.8s infinite ease-in-out;
    }

    @keyframes microPulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.3);
            opacity: 0.75;
        }
    }

    .step-trail-line {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 0;
        z-index: 1;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .step-trail-line.trail-solid {
        border-top: 2px solid #00b207;
    }

    .step-trail-line.trail-dashed {
        border-top: 2px dashed #cbd5e1;
    }

    .step-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        text-align: center;
        margin-top: 8px;
    }

    .step-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        line-height: 1.35;
        text-align: center;
        min-height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.25s ease;
    }

    .step-item.active .step-label {
        color: #00b207;
        font-weight: 700;
    }

    .step-item.completed .step-label {
        color: #0f172a;
        font-weight: 600;
    }

    .step-sub-wrap {
        min-height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 3px;
        width: 100%;
    }

    .step-sub-meta {
        font-size: 11.5px;
        font-weight: 500;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        line-height: 1.3;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .step-item.active .step-sub-meta {
        color: #00b207;
        font-weight: 600;
    }

    /* Products Details List Table */
    .products-table-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 24px;
        width: 100%;
    }
    .products-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        flex-wrap: wrap;
        gap: 10px;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .products-table th {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        padding: 14px 24px;
        border-bottom: 1px solid #e2e8f0;
        background-color: #f8fafc;
        letter-spacing: 0.5px;
    }

    .products-table td {
        padding: 18px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .products-table tr:last-child td {
        border-bottom: none;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .product-img-wrap {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
        flex-shrink: 0;
    }

    .product-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
    }

    .product-title {
        font-size: 14.5px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
        display: block;
    }

    .custom-cake-text-pill {
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e8f8ed;
        color: #00b207;
        border: 1px solid #b7ebc5;
        padding: 3px 9px;
        border-radius: 4px;
        font-size: 11.5px;
        font-weight: 600;
    }

    .product-price-col {
        font-size: 14px;
        color: #334155;
        font-weight: 500;
    }

    .qty-badge {
        display: inline-block;
        padding: 3px 9px;
        background: #f1f5f9;
        color: #334155;
        border-radius: 4px;
        font-size: 12.5px;
        font-weight: 600;
    }

    .product-subtotal-col {
        font-size: 14.5px;
        color: #0f172a;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        .details-header-flex {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }
        .details-title-meta {
            flex-wrap: wrap;
            gap: 6px;
        }
        .details-header-actions {
            width: 100%;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }
        .details-header-actions a,
        .details-header-actions button,
        .details-header-actions span {
            flex: 1 1 auto;
            text-align: center;
            justify-content: center;
        }
        .stepper-container {
            flex-direction: column;
            align-items: flex-start;
            padding: 10px 10px;
            gap: 20px;
        }
        .step-item {
            flex-direction: row;
            width: 100%;
            max-width: 100%;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 14px;
            text-align: left;
            padding: 0;
            position: relative;
        }
        .step-node-wrap {
            width: 28px;
            height: auto;
            flex-shrink: 0;
            justify-content: center;
            position: relative;
        }
        .step-trail-line {
            top: 28px;
            left: 13px;
            width: 0;
            height: calc(100% + 20px);
            transform: none;
        }
        .step-trail-line.trail-solid {
            border-top: none;
            border-left: 2px solid #00b207;
        }
        .step-trail-line.trail-dashed {
            border-top: none;
            border-left: 2px dashed #cbd5e1;
        }
        .step-content {
            align-items: flex-start;
            text-align: left;
            margin-top: 2px;
            flex: 1;
            min-width: 0;
        }
        .step-label {
            margin-top: 0;
            min-height: auto;
            text-align: left;
            justify-content: flex-start;
            font-size: 13.5px;
        }
        .step-sub-wrap {
            justify-content: flex-start;
            min-height: auto;
            margin-top: 2px;
        }
        .step-sub-meta {
            justify-content: flex-start;
        }
    }

    /* Status and Alert Banners */
    .return-status-banner {
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .return-status-banner.return-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 5px solid #16a34a;
    }
    .return-status-banner.return-success i {
        font-size: 24px;
        color: #16a34a;
        flex-shrink: 0;
    }
    .return-status-banner.return-success h4 {
        margin: 0;
        font-size: 14.5px;
        color: #14532d;
        font-weight: 700;
    }
    .return-status-banner.return-success p {
        margin: 3px 0 0;
        font-size: 13px;
        color: #166534;
    }
    .return-status-banner.return-expired {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 5px solid #f59e0b;
    }
    .return-status-banner.return-expired i {
        font-size: 24px;
        color: #d97706;
        flex-shrink: 0;
    }
    .return-status-banner.return-expired h4 {
        margin: 0;
        font-size: 14.5px;
        color: #78350f;
        font-weight: 700;
    }
    .return-status-banner.return-expired p {
        margin: 3px 0 0;
        font-size: 13px;
        color: #92400e;
    }
    .btn-return-disabled {
        opacity: 0.65;
        cursor: not-allowed;
        pointer-events: none;
        background: #94a3b8 !important;
        border-color: #94a3b8 !important;
        color: white !important;
    }
    .payment-countdown-banner {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
        border-left: 5px solid #f59e0b;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .payment-countdown-content {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .payment-countdown-content i {
        font-size: 24px;
        color: #d97706;
        animation: clockPulse 2s infinite ease-in-out;
        flex-shrink: 0;
    }
    .payment-countdown-content h4 {
        margin: 0;
        font-size: 14.5px;
        color: #78350f;
        font-weight: 700;
    }
    .payment-countdown-content p {
        margin: 3px 0 0;
        font-size: 13px;
        color: #92400e;
    }
    .countdown-timer-wrapper {
        text-align: right;
        flex-shrink: 0;
    }
    .countdown-timer-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #92400e;
        font-weight: 700;
        display: block;
        margin-bottom: 3px;
    }
    .countdown-timer-clock {
        font-size: 20px;
        font-weight: 800;
        color: #991b1b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: rgba(255, 255, 255, 0.9);
        padding: 5px 12px;
        border-radius: 6px;
        border: 1px solid rgba(245, 158, 11, 0.25);
        display: inline-block;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    @keyframes clockPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    /* Address Modal Minimalist Card Design */
    .modal-address-dialog {
        max-width: 490px;
        border-radius: 16px !important;
        overflow: hidden;
    }
    .modal-address-dialog .biteship-modal-header {
        padding: 18px 22px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .modal-address-dialog .biteship-modal-body.modal-address-body {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }
    .modal-address-dialog .biteship-modal-footer {
        padding: 14px 20px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
    }
    .modal-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .modal-section-card.note-card {
        background: #fffdf5;
        border-color: #fef08a;
    }
    .section-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
    }
    .section-card-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .section-card-title.text-amber {
        color: #92400e;
    }
    .section-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .section-card-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 2px 0;
    }
    .modal-kv-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .modal-kv-label {
        font-size: 10.5px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .modal-kv-value {
        font-size: 13.5px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
        word-break: break-word;
    }
    .modal-kv-box {
        font-size: 13px;
        color: #334155;
        line-height: 1.55;
        padding: 0;
        background: transparent;
        border: none;
    }
    .modal-kv-box.address-box {
        color: #1e293b;
        font-weight: 500;
    }
    .modal-kv-box.notes-box {
        color: #78350f;
        font-size: 12.5px;
        line-height: 1.5;
        background: transparent;
        border: none;
        padding: 0;
    }
    @media (max-width: 576px) {
        .modal-section-card {
            padding: 12px 14px;
        }
        .section-card-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
    /* Products Table Column Hierarchy */
    .products-card-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .products-card-total {
        font-size: 13px;
        color: #64748b;
    }
    .products-card-total strong {
        color: #00b207;
        font-size: 15px;
    }
    .col-product-main {
        width: 50%;
    }
    .col-product-price {
        width: 15%;
        text-align: right;
    }
    .col-product-qty {
        width: 15%;
        text-align: center;
    }
    .col-product-subtotal {
        width: 20%;
        text-align: right;
    }
    .products-table-empty {
        text-align: center;
        padding: 36px 16px;
        color: #64748b;
    }
    .products-table-empty i {
        font-size: 32px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 10px;
    }

    /* Focus States for Accessibility */
    .btn-detail-address:focus-visible,
    .btn-track-order-top:focus-visible,
    .btn-pay-now-top:focus-visible,
    .btn-pay-now-solid:focus-visible,
    .btn-cancel-top:focus-visible,
    .btn-copy-resi:focus-visible,
    .biteship-modal-close:focus-visible {
        outline: 2px solid #00b207;
        outline-offset: 2px;
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
                                <div class="summary-card-address" id="displayBillingAddress">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
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
                    <div class="table-responsive">
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
                </div>
                <div class="modal-kv-box address-box">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
            </div>

            <!-- Card 3: Catatan Pengiriman (Kondisional) -->
            @if(!empty($order->catatan_pesanan))
                <div class="modal-section-card note-card">
                    <div class="section-card-head">
                        <span class="section-card-title text-amber">
                            <i class="fa-regular fa-note-sticky text-amber me-1"></i> Catatan Pengiriman
                        </span>
                    </div>
                    <div class="modal-kv-box notes-box">{{ $order->catatan_pesanan }}</div>
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
    // Biteship Tracking Modal Handlers
    function openBiteshipTrackingModal() {
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

    // Address Detail Modal Handlers
    function openAddressDetailModal() {
        const modal = document.getElementById('orderAddressDetailModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeAddressDetailModal() {
        const modal = document.getElementById('orderAddressDetailModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Upload Proof Modal Handlers
    function openUploadProofModal(isReadOnly = false) {
        const modal = document.getElementById('uploadProofModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            const dropzone = document.getElementById('modalProofDropzone');
            const footerSubmitBtn = document.getElementById('btnSubmitDetailProof');
            const dropzoneTitle = document.getElementById('modalDropzoneTitle');

            if (isReadOnly) {
                if (dropzone) dropzone.style.display = 'none';
                if (footerSubmitBtn) footerSubmitBtn.style.display = 'none';
            } else {
                if (dropzone) dropzone.style.display = 'block';
                if (footerSubmitBtn) footerSubmitBtn.style.display = 'inline-flex';
            }

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUploadProofModal() {
        const modal = document.getElementById('uploadProofModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    let detailModalSelectedFile = null;

    function handleModalProofSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                title: 'Ukuran Terlalu Besar!',
                text: 'Maksimal ukuran berkas bukti transfer adalah 2MB.',
                icon: 'warning',
                confirmButtonColor: '#00b207'
            });
            event.target.value = '';
            return;
        }

        const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowed.includes(file.type)) {
            Swal.fire({
                title: 'Format Tidak Didukung!',
                text: 'Harap pilih berkas gambar berformat JPG, PNG, atau WEBP.',
                icon: 'warning',
                confirmButtonColor: '#00b207'
            });
            event.target.value = '';
            return;
        }

        detailModalSelectedFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('modalPreviewImg');
            const previewFileName = document.getElementById('modalPreviewFileName');
            const previewFileSize = document.getElementById('modalPreviewFileSize');
            const promptEl = document.getElementById('modalDropzonePrompt');
            const previewBox = document.getElementById('modalDropzonePreview');

            if (previewImg) previewImg.src = e.target.result;
            if (previewFileName) previewFileName.textContent = file.name;
            if (previewFileSize) previewFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

            if (promptEl) promptEl.style.display = 'none';
            if (previewBox) previewBox.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function clearModalProof(event) {
        if (event) event.stopPropagation();
        detailModalSelectedFile = null;
        const input = document.getElementById('modalProofInput');
        if (input) input.value = '';

        const promptEl = document.getElementById('modalDropzonePrompt');
        const previewBox = document.getElementById('modalDropzonePreview');
        if (promptEl) promptEl.style.display = 'block';
        if (previewBox) previewBox.style.display = 'none';
    }

    function copyDetailModalAcc(acc) {
        if (!acc) return;
        navigator.clipboard.writeText(acc).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nomor rekening berhasil disalin!',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }

    function submitDetailModalProof() {
        if (!detailModalSelectedFile) {
            Swal.fire({
                title: 'Pilih Berkas!',
                text: 'Silakan pilih foto bukti pembayaran terlebih dahulu sebelum menyimpan.',
                icon: 'info',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        const formData = new FormData();
        formData.append('bukti_pembayaran', detailModalSelectedFile);
        formData.append('_token', "{{ csrf_token() }}");

        const btn = document.getElementById('btnSubmitModalProof');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunggah...`;
        }

        fetch("{{ route('order.upload.proof', $order->id) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }

            if (data.success) {
                closeUploadProofModal();
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Bukti pembayaran berhasil diunggah.',
                    icon: 'success',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal Mengunggah',
                    text: data.message || 'Terjadi kesalahan saat mengunggah bukti pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
            console.error(err);
            Swal.fire({
                title: 'Kesalahan Sistem',
                text: 'Gagal mengunggah berkas. Periksa koneksi internet Anda.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBiteshipTrackingModal();
            closeAddressDetailModal();
        }
    });

    // Auto-open modal if URL has ?track=1
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('track') === '1') {
            openBiteshipTrackingModal();
        }
    });

    // Copy Tracking Waybill / Resi
    function copyTrackingResi(code) {
        if (!code) return;
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
    // Profile/address sync: preserve order-specific address
    document.addEventListener('DOMContentLoaded', function() {
        const orderName = @json($order->customer_name ?? $user->nama);
        const orderEmail = @json($order->email_pembeli ?? $user->email);
        const orderPhone = @json($formattedCustPhone ?? ($user->no_hp ? '(+62) ' . $user->no_hp : ''));
        
        const storedName = orderName;
        const storedEmail = orderEmail;
        const storedPhone = orderPhone;
        
        if (storedName) document.querySelectorAll('#displayBillingName').forEach(el => el.textContent = storedName);
        if (storedEmail) document.querySelectorAll('#displayBillingEmail').forEach(el => el.textContent = storedEmail);
        if (storedPhone) document.querySelectorAll('#displayBillingPhone').forEach(el => el.textContent = storedPhone);
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
                const selectedRadio = document.querySelector('input[name="cancel_reason"]:checked');
                const cancelReason = selectedRadio ? selectedRadio.value : 'Lainnya';

                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const cancelUrl = `{{ route('order.cancel', ':id') }}`.replace(':id', id);
                fetch(cancelUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cancel_reason: cancelReason })
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

    // Pay order now
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

        const payUrl = `{{ route('order.pay', ':id') }}`.replace(':id', orderId);
        const checkPaymentUrl = `{{ route('order.checkPayment', ':id') }}`.replace(':id', orderId);

        fetch(payUrl)
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
                                fetch(checkPaymentUrl).then(() => window.location.reload());
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
                            fetch(checkPaymentUrl)
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
                            fetch(checkPaymentUrl)
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
                            fetch(checkPaymentUrl)
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

    @if ($isPending && isset($expiryTimestamp))
    (function() {
        const expiryTime = {{ $expiryTimestamp }} * 1000;
        const timerElement = document.getElementById('paymentCountdown');

        function updateTimer() {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance <= 0) {
                if (timerElement) {
                    timerElement.innerHTML = "00:00:00";
                    timerElement.style.color = "#ea4b48";
                }
                clearInterval(timerInterval);
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const formattedHours = String(hours).padStart(2, '0');
            const formattedMinutes = String(minutes).padStart(2, '0');
            const formattedSeconds = String(seconds).padStart(2, '0');

            if (timerElement) {
                timerElement.innerHTML = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    })();
    @endif
</script>
@endsection
