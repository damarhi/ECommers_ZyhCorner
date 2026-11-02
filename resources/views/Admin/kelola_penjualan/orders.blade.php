@extends('Layout.layout_pAdmin')

@section('title', 'Pesanan Online - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-format-list-bulleted"></i>
            </span>
            <span class="text-nowrap">Pesanan Online</span>
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <a href="{{ route('pos.index') }}" class="btn btn-gradient-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; font-weight: 500;" title="Transaksi manual kasir offline">
                <i class="mdi mdi-cash-register me-1" style="font-size: 16px;"></i> Buka Kasir POS
            </a>
        </div>
    </div>



    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <!-- Toolbar -->
                    @php
                        $paymentCount = is_array(request('payment_status')) ? count(request('payment_status')) : (request('payment_status') ? 1 : 0);
                        $orderStatusCount = is_array(request('order_status')) ? count(request('order_status')) : (request('order_status') ? 1 : 0);
                        $activeFilterCount = $paymentCount + $orderStatusCount;
                        $hasActiveFilters = $activeFilterCount > 0 || request('search');
                    @endphp
                    <form method="GET" action="{{ route('orders.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 w-100 w-md-auto flex-wrap">
                            <div class="dropdown">
                                <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
                                    <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
                                        @if($activeFilterCount > 0)
                                            <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
                                        @endif
                                    </span>
                                </button>
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 240px; border-radius: 8px; margin-top: 5px;">
                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Pembayaran</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="payment_status[]" value="Pending" class="form-check-input" {{ is_array(request('payment_status')) && in_array('Pending', request('payment_status')) ? 'checked' : '' }}> Tertunda
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="payment_status[]" value="Paid" class="form-check-input" {{ is_array(request('payment_status')) && in_array('Paid', request('payment_status')) ? 'checked' : '' }}> Dibayar
                                        </label>
                                    </div>

                                    <div class="dropdown-divider mb-3"></div>

                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Pengiriman</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="order_status[]" value="menunggu konfirmasi" class="form-check-input" {{ is_array(request('order_status')) && in_array('menunggu konfirmasi', request('order_status')) ? 'checked' : '' }}> Menunggu Konfirmasi
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="order_status[]" value="dikemas" class="form-check-input" {{ is_array(request('order_status')) && in_array('dikemas', request('order_status')) ? 'checked' : '' }}> Dikemas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="order_status[]" value="dikirim" class="form-check-input" {{ is_array(request('order_status')) && in_array('dikirim', request('order_status')) ? 'checked' : '' }}> Dikirim
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="order_status[]" value="selesai" class="form-check-input" {{ is_array(request('order_status')) && in_array('selesai', request('order_status')) ? 'checked' : '' }}> Selesai
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="order_status[]" value="batal" class="form-check-input" {{ is_array(request('order_status')) && in_array('batal', request('order_status')) ? 'checked' : '' }}> Batal
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-gradient-primary w-100" style="height: 34px; font-weight: 600;">Terapkan Filter</button>
                                </div>
                            </div>
                            <div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
                                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nomor pesanan / pelanggan..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                            @if($hasActiveFilters)
                                <a href="{{ route('orders.index') }}" class="admin-btn-reset" title="Reset Filter">
                                    <i class="mdi mdi-refresh me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                        <button type="button" id="btnBulkDelete" class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center align-self-end align-self-md-center px-3" style="height: 38px;" title="Hapus Terpilih">
                            <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </form>

                    <form action="{{ route('orders.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                        @csrf
                        <div id="bulkDeleteInputs"></div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                     <th style="width: 40px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" id="checkAllOrders" class="form-check-input" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </th>
                                    <th>Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Pesanan</th>
                                    <th>Total</th>
                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $order->payment_method . ' ' . $order->payment_status . ' ' . $order->order_status . ' ' . $order->id) }}">
                                     <td>
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="form-check-input order-row-checkbox" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </td>
                                    <td class="font-weight-600 text-dark">{{ $order->order_number }}</td>
                                    <td class="text-muted">{{ $order->created_at->format('M j, g:i A') }}</td>
                                    <td class="font-weight-500 text-dark">{{ $order->customer_name }}</td>
                                    <td>
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge badge-soft-success">Dibayar</span>
                                        @elseif($order->has_payment_proof)
                                            <span class="badge badge-soft-warning" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#verifyProofModal{{ $order->id }}" title="Klik untuk verifikasi bukti transfer">
                                                <i class="mdi mdi-file-document-check-outline me-1"></i>Bukti Transfer
                                            </span>
                                        @else
                                            <span class="badge badge-soft-warning">Tertunda</span>
                                        @endif
                                    </td>
                                    <td id="order-status-badge-{{ $order->id }}">
                                        @if($order->order_status == 'menunggu pembayaran')
                                            <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                        @elseif($order->order_status == 'menunggu konfirmasi')
                                            <span class="badge badge-pulse-amber">Menunggu Konfirmasi</span>
                                        @elseif($order->order_status == 'dikemas')
                                            <span class="badge badge-soft-info">Dikemas</span>
                                        @elseif($order->order_status == 'dikirim')
                                            <span class="badge badge-soft-primary">Dikirim</span>
                                        @elseif($order->order_status == 'selesai')
                                            <span class="badge badge-soft-success">Selesai</span>
                                        @elseif($order->order_status == 'batal')
                                            <span class="badge badge-soft-danger">Dibatalkan</span>
                                        @else
                                            <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-600 text-dark">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center">
                                            @if($order->has_payment_proof)
                                                <button type="button" 
                                                    class="btn btn-sm btn-inverse-warning d-inline-flex align-items-center justify-content-center me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#verifyProofModal{{ $order->id }}" 
                                                    style="width: 32px; height: 32px; padding: 0; background: #fffbeb; color: #d97706; border: 1px solid #fde68a;" 
                                                    title="Verifikasi Bukti Transfer">
                                                    <i class="mdi mdi-file-document-check-outline" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            @endif
                                            @if(in_array(strtolower($order->order_status ?? ''), ['menunggu konfirmasi', 'unconfirmed']) && !$order->is_bank_transfer)
                                                <button type="button" 
                                                    class="btn btn-sm btn-quick-confirm me-1" 
                                                    id="btn-quick-confirm-{{ $order->id }}"
                                                    onclick="quickConfirmOrder({{ $order->id }}, '{{ $order->order_number }}')"
                                                    title="Konfirmasi Pesanan &amp; Siap Kemas">
                                                    <i class="mdi mdi-check-circle-outline me-1"></i> Konfirmasi &amp; Kemas
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0;" title="Detail Pesanan">
                                                <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editOrderModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0;" title="Edit Pesanan">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Hapus Pesanan">
                                                    <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-cart-outline text-muted mb-2" style="font-size: 64px;"></i>
                                            @if($hasActiveFilters)
                                                <h5 class="text-dark font-weight-bold mb-1">Tidak Ditemukan Pesanan yang Sesuai</h5>
                                                <p class="text-muted mb-3" style="font-size: 14px;">Tidak ada pesanan untuk kriteria filter atau pencarian yang sedang aktif.</p>
                                                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-gradient-primary rounded-pill px-4">
                                                    <i class="mdi mdi-refresh me-1"></i> Reset Filter & Pencarian
                                                </a>
                                            @else
                                                <h5 class="text-muted font-weight-bold">Belum Ada Pesanan</h5>
                                                <p class="text-muted mb-0" style="font-size: 14px;">Oops, pesanan pelanggan belum ada yang masuk nih.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modals (placed outside the table to prevent HTML rendering issues) -->
                    @foreach($orders as $order)
                        <!-- Edit Order Modal -->
                        <div class="modal fade" id="editOrderModal{{ $order->id }}" tabindex="-1" aria-labelledby="editOrderModalLabel{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto;">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                                    <!-- Header Modal -->
                                    <div class="modal-header border-bottom py-2.5 px-3 bg-white">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle" style="width: 34px; height: 34px; background-color: #e0f2fe; color: #0284c7 !important;">
                                                <i class="mdi mdi-pencil-box-outline" style="font-size: 18px;"></i>
                                            </span>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editOrderModalLabel{{ $order->id }}" style="font-size: 15px;">
                                                        Edit Status Pesanan
                                                    </h6>
                                                    <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">
                                                        {{ $order->order_number }}
                                                    </span>
                                                </div>
                                                <small class="text-muted" style="font-size: 11px;">Perbarui status bayar dan alur proses pesanan.</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                                    </div>
                                    <form action="{{ route('orders.update', $order->id) }}" method="POST" class="edit-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-3 bg-white">
                                            <!-- Ringkasan Data Pesanan Terkunci (Read-Only) -->
                                            <div class="p-2.5 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px;">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                    <span class="text-muted d-flex align-items-center" style="font-size: 11px;">
                                                        <i class="mdi mdi-lock-outline text-warning me-1" style="font-size: 13px;"></i> Data Transaksi Terkunci
                                                    </span>
                                                    <span class="badge badge-soft-dark" style="font-size: 10px;">ID #{{ $order->id }}</span>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <span class="text-muted d-block" style="font-size: 10px;">Nama Pelanggan</span>
                                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $order->customer_name }}">
                                                            {{ $order->customer_name }}
                                                        </strong>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <span class="text-muted d-block" style="font-size: 10px;">Total Tagihan</span>
                                                        <strong class="text-dark d-block font-weight-bold" style="font-size: 13px; color: #1E2753 !important;">
                                                            Rp. {{ number_format($order->total, 0, ',', '.') }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden inputs to retain values safely -->
                                            <input type="hidden" name="customer_name" value="{{ $order->customer_name }}">
                                            <input type="hidden" name="total" value="{{ $order->total }}">

                                            <!-- Grid Status (2 Kolom) -->
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">
                                                            Status Bayar <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-select form-control" name="payment_status" required style="border-radius: 6px; font-size: 12px; height: 38px;">
                                                            <option value="Pending" {{ $order->payment_status == 'Pending' ? 'selected' : '' }}>Tertunda</option>
                                                            <option value="Paid" {{ $order->payment_status == 'Paid' ? 'selected' : '' }}>Dibayar</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">
                                                            Status Pesanan <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-select form-control" name="order_status" id="edit-order-status-select-{{ $order->id }}" required style="border-radius: 6px; font-size: 12px; height: 38px;">
                                                            <option value="menunggu pembayaran" {{ $order->order_status == 'menunggu pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                                            <option value="menunggu konfirmasi" {{ $order->order_status == 'menunggu konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                                            <option value="dikemas" {{ $order->order_status == 'dikemas' ? 'selected' : '' }}>Dikemas</option>
                                                            <option value="dikirim" {{ $order->order_status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                                            <option value="selesai" {{ $order->order_status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                            <option value="batal" {{ $order->order_status == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light py-2 px-3 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                                            <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                                                <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Perbarui Status
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Order Modal -->
                        @include('Admin.kelola_penjualan.order_detail_modal', ['order' => $order])

                        <!-- Verify Proof Modal -->
                        @include('Admin.kelola_penjualan.verify_proof_modal', ['order' => $order])
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top gap-3">
                        <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                            {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted text-center text-md-end w-100 w-md-auto font-weight-500" style="font-size: 13.5px;">
                            Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} hasil
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Proteksi sinkronisasi status ketika modal edit pesanan dibuka
        document.addEventListener('show.bs.modal', function(event) {
            const modal = event.target;
            if (modal && modal.id && modal.id.startsWith('editOrderModal')) {
                const orderId = modal.id.replace('editOrderModal', '');
                const badgeContainer = document.getElementById(`order-status-badge-${orderId}`);
                const select = document.getElementById(`edit-order-status-select-${orderId}`) || modal.querySelector('select[name="order_status"]');
                if (badgeContainer && select) {
                    const badgeText = badgeContainer.textContent.trim().toLowerCase();
                    if (badgeText.includes('dikemas') && select.value !== 'dikemas') {
                        select.value = 'dikemas';
                        Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'dikemas'));
                    } else if (badgeText.includes('dikirim') && select.value !== 'dikirim') {
                        select.value = 'dikirim';
                        Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'dikirim'));
                    } else if (badgeText.includes('selesai') && select.value !== 'selesai') {
                        select.value = 'selesai';
                        Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'selesai'));
                    } else if (badgeText.includes('batal') && select.value !== 'batal') {
                        select.value = 'batal';
                        Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'batal'));
                    }
                }
            }
        });

    });

    function quickConfirmOrder(orderId, orderNumber) {
        const btn = document.getElementById(`btn-quick-confirm-${orderId}`);
        
        Swal.fire({
            title: 'Konfirmasi Pesanan?',
            text: `Pesanan ${orderNumber} akan dikonfirmasi dan statusnya diubah menjadi Dikemas untuk segera diproses di dapur.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00b207',
            cancelButtonColor: '#9CA3AF',
            confirmButtonText: '<i class="mdi mdi-check-bold me-1"></i> Ya, Konfirmasi &amp; Kemas',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Memproses...';
                }

                fetch(`{{ url('admin/orders') }}/${orderId}/quick-confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                .then(({ status, ok, body }) => {
                    if (ok && body.success) {
                        Swal.fire({
                            title: 'Pesanan Dikonfirmasi!',
                            text: body.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // 1. Instant DOM update: Ubah status badge di baris tabel
                        const badgeContainer = document.getElementById(`order-status-badge-${orderId}`);
                        if (badgeContainer) {
                            badgeContainer.innerHTML = '<span class="badge badge-soft-info">Dikemas</span>';
                        }

                        // 2. Animasi & hapus tombol quick confirm
                        if (btn) {
                            btn.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            btn.style.opacity = '0';
                            btn.style.transform = 'scale(0.8)';
                            setTimeout(() => btn.remove(), 300);
                        }

                        // 3. Sinkronisasi Dropdown Modal Edit Status (#editOrderModal)
                        const editStatusSelect = document.getElementById(`edit-order-status-select-${orderId}`) ||
                                                 document.querySelector(`#editOrderModal${orderId} select[name="order_status"]`);
                        if (editStatusSelect) {
                            editStatusSelect.value = 'dikemas';
                            Array.from(editStatusSelect.options).forEach(opt => {
                                opt.selected = (opt.value === 'dikemas');
                            });
                        }

                        // 4. Sinkronisasi Badge Status pada Modal Detail (#orderDetailModal)
                        const modalDetailBadge = document.getElementById(`modal-detail-status-badge-${orderId}`);
                        if (modalDetailBadge) {
                            modalDetailBadge.innerHTML = '<span class="badge badge-soft-info" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikemas</span>';
                        }

                        // 5. Perbarui data-search pada elemen tr agar pencarian real-time tetap sinkron
                        const tableRow = document.querySelector(`tr[data-search*="${orderNumber.toLowerCase()}"]`) || (badgeContainer ? badgeContainer.closest('tr') : null);
                        if (tableRow && tableRow.getAttribute('data-search')) {
                            const curSearch = tableRow.getAttribute('data-search');
                            tableRow.setAttribute('data-search', curSearch.replace(/menunggu\s*konfirmasi/gi, 'dikemas'));
                        }
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: body.message || 'Terjadi kesalahan saat mengonfirmasi pesanan.',
                            icon: 'error'
                        });
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Konfirmasi &amp; Kemas';
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: 'Kesalahan Sistem',
                        text: 'Tidak dapat menghubungi server. Silakan coba beberapa saat lagi.',
                        icon: 'error'
                    });
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Konfirmasi &amp; Kemas';
                    }
                });
            }
        });
    }
</script>



@endsection
