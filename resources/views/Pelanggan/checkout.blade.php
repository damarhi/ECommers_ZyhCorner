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

<style>
    /* Checkout Page Structure */
    .checkout-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 15px 80px;
    }
    
    .checkout-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 40px;
        align-items: start;
    }

    .checkout-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .checkout-section-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 24px;
        border-bottom: 1px solid #f2f2f2;
        padding-bottom: 12px;
    }

    /* Form Fields */
    .form-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .form-group-custom {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .form-group-custom label {
        font-size: 14px;
        font-weight: 500;
        color: #333;
    }
    .form-group-custom label span {
        color: #999;
        font-weight: 400;
        font-size: 12px;
    }
    .form-input-custom {
        padding: 12px 16px;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
        width: 100%;
        background: #fdfdfd;
    }
    .form-input-custom:focus {
        border-color: #00b207;
        background: white;
    }
    .form-textarea-custom {
        padding: 12px 16px;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
        width: 100%;
        min-height: 100px;
        resize: vertical;
        background: #fdfdfd;
    }
    .form-textarea-custom:focus {
        border-color: #00b207;
        background: white;
    }

    /* Map styling */
    #map {
        width: 100%;
        height: 250px;
        border-radius: 8px;
        border: 1px solid #e6e6e6;
        margin-top: 5px;
        z-index: 10;
    }
    .map-note {
        font-size: 12px;
        color: #666;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Checkbox option */
    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #4d4d4d;
        cursor: pointer;
        margin-top: 15px;
        user-select: none;
    }
    .checkbox-option input {
        accent-color: #00b207;
        width: 16px;
        height: 16px;
    }

    /* Right Column Cards */
    .summary-card {
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        background: white;
        padding: 24px;
        margin-bottom: 25px;
    }
    .summary-card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        border-bottom: 1px solid #f2f2f2;
        padding-bottom: 10px;
    }

    /* Order Summary Items list */
    .order-items-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px;
    }
    .order-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        color: #333;
    }
    .order-item-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .order-item-img {
        width: 48px;
        height: 48px;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #f2f2f2;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
        background: #f9f9f9;
        flex-shrink: 0;
    }
    .order-item-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .order-item-info {
        display: flex;
        flex-direction: column;
    }
    .order-item-name {
        font-weight: 500;
        max-width: 160px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .order-item-qty {
        color: #999;
        font-size: 12px;
        margin-top: 2px;
    }
    .order-item-price {
        font-weight: 600;
        color: #1a1a1a;
    }

    /* Summary Totals */
    .summary-totals {
        border-top: 1px solid #f2f2f2;
        padding-top: 15px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 14px;
        color: #666;
    }
    .summary-total-row {
        display: flex;
        justify-content: space-between;
    }
    .summary-total-row span:last-child {
        color: #1a1a1a;
        font-weight: 600;
    }
    .summary-total-row.grand-total {
        font-size: 16px;
        color: #1a1a1a;
        font-weight: 600;
        border-top: 1px solid #f2f2f2;
        padding-top: 15px;
    }
    .summary-total-row.grand-total span:last-child {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
    }

    /* Payment Methods Options */
    .payment-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }
    .payment-option-item {
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .payment-option-item.active {
        border-color: #00b207;
        background: #f2f9f3;
    }
    .payment-option-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
    }
    .payment-option-header input {
        accent-color: #00b207;
        width: 16px;
        height: 16px;
    }
    .payment-sub-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-left: 26px;
        margin-top: 10px;
        padding-left: 10px;
        border-left: 2px solid #e6e6e6;
    }
    .payment-sub-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #4d4d4d;
        cursor: pointer;
    }
    .payment-sub-item input {
        accent-color: #00b207;
        width: 14px;
        height: 14px;
    }

    /* Select Shipping Box */
    .shipping-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }
    .shipping-option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        padding: 8px 0;
    }
    .shipping-option-item input {
        accent-color: #00b207;
        width: 16px;
        height: 16px;
    }

    .btn-place-order {
        display: flex;
        width: 100%;
        background-color: #00b207;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 15px 0;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        justify-content: center;
        align-items: center;
        transition: background 0.3s;
        margin-top: 25px;
    }
    .btn-place-order:hover {
        background-color: #009906;
    }

    @media (max-width: 991px) {
        .checkout-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }
</style>


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
    let map, marker;
    
    document.addEventListener('DOMContentLoaded', function() {
        renderOrderSummary();
        initCheckoutMap();
    });

    // Initialize Leaflet Map
    function initCheckoutMap() {
        // Del Institute coordinates as starting point
        const initialCoords = [2.3833, 99.1500];
        
        map = L.map('map', {
            scrollWheelZoom: false
        }).setView(initialCoords, 15);

        // Add premium styling layer from OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Create draggable marker
        marker = L.marker(initialCoords, {
            draggable: true
        }).addTo(map);

        // Set initial coordinates values
        updateCoordsInputs(initialCoords[0], initialCoords[1]);

        // Dragend event on marker
        marker.on('dragend', function (e) {
            let latlng = marker.getLatLng();
            updateCoordsInputs(latlng.lat, latlng.lng);
        });

        // Click event on map
        map.on('click', function (e) {
            let latlng = e.latlng;
            marker.setLatLng(latlng);
            updateCoordsInputs(latlng.lat, latlng.lng);
        });
    }

    function updateCoordsInputs(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        document.getElementById('coordsDisplay').innerHTML = `Lokasi Terpilih: (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
    }

    // Render summary products
    function renderOrderSummary() {
        const orderSummaryDiv = document.getElementById('orderItemsSummary');
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        
        if (localCart.length === 0) {
            orderSummaryDiv.innerHTML = '<p style="color:#999; font-style:italic;">Keranjang kosong</p>';
            window.location.href = "{{ route('userChart') }}";
            return;
        }

        let summaryRowsHtml = localCart.map(item => {
            let priceSub = item.price * item.qty;
            let priceSubFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(priceSub);
            
            return `
                <div class="order-item-row">
                    <div class="order-item-left">
                        <div class="order-item-img">
                            <img src="${item.img}" alt="${item.name}">
                        </div>
                        <div class="order-item-info">
                            <span class="order-item-name" title="${item.name}">${item.name}</span>
                            <span class="order-item-qty">Qty: ${item.qty}</span>
                        </div>
                    </div>
                    <span class="order-item-price">${priceSubFormatted}</span>
                </div>
            `;
        }).join('');

        orderSummaryDiv.innerHTML = summaryRowsHtml;

        // Subtotals
        let subtotalVal = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        let subtotalValFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal);
        
        document.getElementById('summarySubtotal').innerHTML = subtotalValFormatted;
        document.getElementById('summaryTotal').innerHTML = subtotalValFormatted;
    }

    // Select payment method visually & reveal options
    window.selectPaymentMethod = function(method) {
        // Toggle active style
        document.querySelectorAll('.payment-option-item').forEach(item => item.classList.remove('active'));
        
        const codRadio = document.getElementById('payCod');
        const transferRadio = document.getElementById('payTransfer');
        const ewalletRadio = document.getElementById('payEwallet');

        const transferSub = document.getElementById('transferSubOptions');
        const ewalletSub = document.getElementById('ewalletSubOptions');

        transferSub.style.display = 'none';
        ewalletSub.style.display = 'none';

        if (method === 'cod') {
            codRadio.checked = true;
            codRadio.closest('.payment-option-item').classList.add('active');
        } else if (method === 'transfer') {
            transferRadio.checked = true;
            transferRadio.closest('.payment-option-item').classList.add('active');
            transferSub.style.display = 'flex';
        } else if (method === 'ewallet') {
            ewalletRadio.checked = true;
            ewalletRadio.closest('.payment-option-item').classList.add('active');
            ewalletSub.style.display = 'flex';
        }
    };

    // Submit Order action
    window.submitOrder = function() {
        const form = document.getElementById('checkoutForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        if(localCart.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Keranjang belanja kosong!',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        // Gather details
        let streetAddress = document.getElementById('streetAddress').value;
        let notes = document.getElementById('orderNotes').value;
        
        let paymentVal = document.querySelector('input[name="paymentGroup"]:checked').value;
        let shippingVal = document.querySelector('input[name="shippingGroup"]:checked').value;

        // Show premium processing spinner
        Swal.fire({
            title: 'Memproses Pesanan...',
            text: 'Harap tunggu sementara kami memverifikasi pesanan Anda.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send AJAX request
        fetch("{{ route('checkout.post') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                // Clear the cart on successful database entry
                localStorage.removeItem('zyh_cart');
                if (typeof updateHeaderCart === 'function') {
                    cart = [];
                    updateHeaderCart();
                }
                window.location.href = "{{ route('order.success') }}";
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
