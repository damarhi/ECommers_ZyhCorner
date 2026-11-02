@extends('Layout.layout_pAdmin')

@section('title', 'Checkout')

@section('content')
<div class="content-wrapper">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('pos.index') }}" class="text-decoration-none text-muted" style="font-size: 14px;">
            <i class="mdi mdi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <h3 class="page-title font-weight-bold text-dark mb-4" style="font-size: 22px;">Checkout</h3>

    <div class="row">
        <!-- Kolom Kiri: Tabel Produk -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th style="font-size: 12px; font-weight: 500;" class="ps-4">PRODUCT</th>
                                    <th style="font-size: 12px; font-weight: 500;">PRICE</th>
                                    <th style="font-size: 12px; font-weight: 500;" class="text-center">QUANTITY</th>
                                    <th style="font-size: 12px; font-weight: 500;" class="pe-4 text-end">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody id="checkout-items">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="checkout-table-footer" class="card-footer bg-white border-top p-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 13px;">Ingin menambahkan barang belanjaan lain?</span>
                    <a href="{{ route('pos.index') }}" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500;">
                        <i class="mdi mdi-plus me-1"></i> Tambah Produk Lain
                    </a>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Panel Pembayaran -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Payment</h5>
                    <div class="form-group mb-3">
                        <label class="text-muted mb-2" style="font-size: 12px;">Customer Name (Optional)</label>
                        <input type="text" class="form-control border-0 shadow-sm" id="customer-name" placeholder="Walk-in Customer" style="border-radius: 8px; font-size: 13px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="text-muted mb-2" style="font-size: 12px;">Select payment</label>
                        <select class="form-select border-0 shadow-sm" id="payment-method" style="border-radius: 8px; font-size: 13px;">
                            <option value="Cash">Cash</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>

                    <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Cart Total</h5>

                    <!-- Rincian Harga Asli & Diskon Setiap Produk -->
                    <div class="mb-3">
                        <span class="text-muted d-block mb-2 font-weight-500" style="font-size: 12px; letter-spacing: 0.3px;">RINCIAN PRODUK & DISKON</span>
                        <div id="checkout-product-breakdown" style="max-height: 220px; overflow-y: auto;">
                            <!-- Render dinamis via JS -->
                        </div>
                    </div>

                    <!-- Ringkasan Akumulasi Total Tagihan -->
                    <div class="p-3 rounded mb-4" style="background: #f8fafc; border: 1px solid #edf2f7;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size: 13px;">Total Harga Asli:</span>
                            <span class="text-dark font-weight-500" id="checkout-total-original" style="font-size: 13px;">Rp. 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size: 13px;">Total Diskon:</span>
                            <span class="text-danger font-weight-bold" id="checkout-total-discount" style="font-size: 13px;">-Rp. 0</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="text-dark font-weight-bold" style="font-size: 14px;">Total Tagihan (Setelah Diskon):</span>
                            <strong id="checkout-total" class="text-primary font-weight-bold" style="font-size: 16px;">Rp. 0</strong>
                        </div>
                        <span id="checkout-subtotal" class="d-none">Rp. 0</span>
                    </div>
                    <button class="btn btn-gradient-primary w-100 mb-3" onclick="processCheckout()" style="border-radius: 8px; font-weight: 500;">Proses Checkout</button>
                    <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 8px; font-weight: 500;">Kembali ke Kasir</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.posCheckoutConfig = {
        posIndexRoute: "{{ route('pos.index') }}",
        checkoutPostRoute: "{{ route('pos.checkout') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/js_pos_checkout.js') }}?v={{ file_exists(public_path('js/js_pos_checkout.js')) ? filemtime(public_path('js/js_pos_checkout.js')) : time() }}"></script>
@endsection
