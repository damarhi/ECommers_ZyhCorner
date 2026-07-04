@extends('Layout.layout_pelanggan')

@section('title', 'Checkout - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('userChart') }}">Shopping Cart</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span>Checkout</span>
@endsection

@section('content')
<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Midtrans Snap JS -->
@php
    $isProduction = config('services.midtrans.is_production', false);
    $snapUrl = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = config('services.midtrans.client_key');
@endphp
<script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>




<div class="checkout-container">
    <div class="checkout-grid">
        
        <!-- Left Side: Billing Information -->
        <div>
            <div class="checkout-card">
                <h2 class="checkout-section-title">Billing Information</h2>
                
                <form id="checkoutForm">
                    <div class="form-group-custom">
                        <label for="streetAddress">Street Address</label>
                        <input type="text" id="streetAddress" class="form-input-custom" placeholder="Detail alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan" required>
                    </div>

                    <div class="form-group-custom">
                        <label>Point Your Location on Map</label>
                        <div id="map"></div>
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <div class="map-note">
                            <i class="fa-solid fa-location-crosshairs" style="color: #00b207;"></i>
                            <span id="coordsDisplay">Klik peta atau seret marker untuk menandai lokasi tepat Anda</span>
                        </div>
                    </div>

                    <label class="checkbox-option">
                        <input type="checkbox" id="differentAddress">
                        <span>Ship to a different address</span>
                    </label>
                </form>
            </div>

            <!-- Additional Info -->
            <div class="checkout-card">
                <h2 class="checkout-section-title">Additional Info</h2>
                <div class="form-group-custom" style="margin-bottom: 0;">
                    <label for="orderNotes">Order Notes <span>(Optional)</span></label>
                    <textarea id="orderNotes" class="form-textarea-custom" placeholder="Notes about your order, e.g. special notes for delivery"></textarea>
                </div>
            </div>
        </div>

        <!-- Right Side: Order Summary & Payment & Shipping -->
        <div>
            <!-- Order Summary Card -->
            <div class="summary-card">
                <h3 class="summary-card-title">Order Summery</h3>
                
                <div class="order-items-list" id="orderItemsSummary">
                    <!-- Rendered by JS -->
                </div>

                <div class="summary-totals">
                    <div class="summary-total-row">
                        <span>Subtotal:</span>
                        <span id="summarySubtotal">Rp. 0</span>
                    </div>
                    <div class="summary-total-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-total-row grand-total">
                        <span>Total:</span>
                        <span id="summaryTotal">Rp. 0</span>
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="summary-card">
                <h3 class="summary-card-title">Payment Method</h3>
                
                <div class="payment-options">
                    
                    <!-- Cash on Delivery -->
                    <div class="payment-option-item active" onclick="selectPaymentMethod('cod')">
                        <div class="payment-option-header">
                            <input type="radio" id="payCod" name="paymentGroup" value="COD" checked>
                            <label for="payCod" style="cursor: pointer;">Cash on Delivery</label>
                        </div>
                    </div>

                    <!-- Transfer Bank -->
                    <div class="payment-option-item" onclick="selectPaymentMethod('transfer')">
                        <div class="payment-option-header">
                            <input type="radio" id="payTransfer" name="paymentGroup" value="Transfer">
                            <label for="payTransfer" style="cursor: pointer;">Transfer Bank</label>
                        </div>
                        <div class="payment-sub-options" id="transferSubOptions" style="display: none;">
                            <label class="payment-sub-item">
                                <input type="radio" name="bankSelect" value="BRI" checked>
                                <span>BRI</span>
                            </label>
                            <label class="payment-sub-item">
                                <input type="radio" name="bankSelect" value="BCA">
                                <span>BCA</span>
                            </label>
                            <label class="payment-sub-item">
                                <input type="radio" name="bankSelect" value="Mandiri">
                                <span>Mandiri</span>
                            </label>
                        </div>
                    </div>

                    <!-- E-Wallet -->
                    <div class="payment-option-item" onclick="selectPaymentMethod('ewallet')">
                        <div class="payment-option-header">
                            <input type="radio" id="payEwallet" name="paymentGroup" value="E-Wallet">
                            <label for="payEwallet" style="cursor: pointer;">E-Wallet</label>
                        </div>
                        <div class="payment-sub-options" id="ewalletSubOptions" style="display: none;">
                            <label class="payment-sub-item">
                                <input type="radio" name="walletSelect" value="DANA" checked>
                                <span>DANA</span>
                            </label>
                            <label class="payment-sub-item">
                                <input type="radio" name="walletSelect" value="QRIS">
                                <span>QRIS</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Select Shipping Card -->
            <div class="summary-card">
                <h3 class="summary-card-title">Select Shipping</h3>
                
                <div class="shipping-options">
                    <label class="shipping-option-item">
                        <input type="radio" name="shippingGroup" value="Maxim" checked>
                        <span>Maxim</span>
                    </label>
                    <label class="shipping-option-item">
                        <input type="radio" name="shippingGroup" value="Kuantar">
                        <span>Kuantar</span>
                    </label>
                    <label class="shipping-option-item">
                        <input type="radio" name="shippingGroup" value="Iron">
                        <span>Iron</span>
                    </label>
                    <label class="shipping-option-item">
                        <input type="radio" name="shippingGroup" value="Pick Up">
                        <span>Pick Up</span>
                    </label>
                </div>

                <button type="button" class="btn-place-order" onclick="submitOrder()">Place Order</button>
            </div>

        </div>

    </div>
</div>


<script>
    window.checkoutConfig = {
        userChartRoute: "{{ route('userChart') }}",
        checkoutPostRoute: "{{ route('checkout.post') }}",
        orderSuccessRoute: "{{ route('order.success') }}",
        csrfToken: "{{ csrf_token() }}"
    };

    // Override window.submitOrder to integrate Midtrans Snap
    window.submitOrder = function() {
        const form = document.getElementById('checkoutForm');
        if (form && !form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        if (localCart.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Keranjang belanja kosong!',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        const addrEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');
        let streetAddress = addrEl ? addrEl.value : '';
        let notes = notesEl ? notesEl.value : '';
        
        const payRadio = document.querySelector('input[name="paymentGroup"]:checked');
        const shipRadio = document.querySelector('input[name="shippingGroup"]:checked');
        let paymentVal = payRadio ? payRadio.value : 'COD';
        let shippingVal = shipRadio ? shipRadio.value : 'Maxim';

        Swal.fire({
            title: 'Memproses Pesanan...',
            text: 'Harap tunggu sementara kami memverifikasi pesanan Anda.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const routeUrl = window.checkoutConfig.checkoutPostRoute;
        const token = window.checkoutConfig.csrfToken;

        fetch(routeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                items: localCart,
                address: streetAddress,
                notes: notes,
                payment_method: paymentVal,
                shipping_method: shippingVal
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Save cart to zyh_last_order for order success display
                localStorage.setItem('zyh_last_order', JSON.stringify(localCart));

                // Clear the cart locally immediately since order is created
                localStorage.removeItem('zyh_cart');
                if (typeof window.updateHeaderCart === 'function') {
                    window.cart = [];
                    window.updateHeaderCart();
                }

                // Redirect directly to the order success page (the customer can complete payment there)
                window.location.href = window.checkoutConfig.orderSuccessRoute + '?order_id=' + data.order.id;
            } else {
                Swal.fire({
                    title: 'Kesalahan!',
                    text: data.message || 'Terjadi kesalahan saat memproses pesanan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal. Harap coba lagi.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
    };
</script>
@endsection
