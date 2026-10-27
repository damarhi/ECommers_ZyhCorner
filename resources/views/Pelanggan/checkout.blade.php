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
        
        <!-- Left Side: Order Fulfillment Steps -->
        <div class="checkout-main-col">
            
            <!-- Step 1: Shipping Address & Map Pin -->
            <div class="checkout-card">
                <div class="checkout-step-header">
                    <span class="step-badge">1</span>
                    <div>
                        <h2 class="checkout-section-title">Alamat Pengiriman & Titik Lokasi</h2>
                        <p class="checkout-section-subtitle">Lengkapi data penerima, wilayah, alamat lengkap, dan titik peta pengantaran</p>
                    </div>
                </div>
                
                <form id="checkoutForm">
                    @php
                        $hasSavedAddresses = isset($userAddresses) && $userAddresses->count() > 0;
                        $defaultAddr = $hasSavedAddresses ? ($userAddresses->firstWhere('is_default', 1) ?? $userAddresses->first()) : null;
                    @endphp

                    @if($hasSavedAddresses)
                        <div class="saved-addresses-selector">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <label for="savedAddressSelect" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 0;">
                                    <i class="fa-solid fa-address-book" style="color: #00b207;"></i> Pilih dari Alamat Tersimpan:
                                </label>
                                <a href="{{ route('dashboard') }}" style="font-size: 12px; color: #00b207; font-weight: 600; text-decoration: none;">
                                    Kelola Buku Alamat &rarr;
                                </a>
                            </div>
                            <div class="saved-address-dropdown-wrapper">
                                <select id="savedAddressSelect" class="form-select-custom saved-address-select" onchange="onSavedAddressDropdownChange(this)">
                                    @foreach($userAddresses as $idx => $addr)
                                        <option value="{{ $addr->id }}" 
                                                data-address="{{ json_encode($addr) }}"
                                                {{ ($defaultAddr && $defaultAddr->id === $addr->id) ? 'selected' : '' }}>
                                            {{ $addr->label }}{{ $addr->is_default ? ' [Utama]' : '' }} &mdash; {{ $addr->recipient_name }} | {{ $addr->street_address }}{{ $addr->postal_code ? ' (' . $addr->postal_code . ')' : '' }}
                                        </option>
                                    @endforeach
                                    <option value="new">+ Tulis Alamat Baru</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- FAQ-Style Collapsible Address Details Card -->
                    <div class="checkout-address-accordion {{ $hasSavedAddresses ? '' : 'open' }}" id="checkoutAddressAccordion">
                        <div class="checkout-faq-q" onclick="toggleAddressAccordion()" id="addressAccordionHeader">
                            <div class="checkout-accordion-summary" id="addressSummaryPreview">
                                <div class="address-summary-pill">
                                    <i class="fa-solid fa-location-dot" style="color: #00b207;"></i>
                                    <span class="summary-name" id="summaryRecipientName">{{ $defaultAddr->recipient_name ?? (Auth::user()->nama ?? 'Penerima') }}</span>
                                    <span class="summary-sep">&bull;</span>
                                    <span class="summary-address" id="summaryStreetAddress">{{ $defaultAddr->street_address ?? (Auth::user()->alamat ?? 'Tulis rincian alamat...') }}</span>
                                    <span class="summary-badge-gps" id="summaryGpsBadge" style="{{ ($defaultAddr && $defaultAddr->latitude) ? '' : 'display: none;' }}">
                                        <i class="fa-solid fa-crosshairs"></i> GPS Terpasang
                                    </span>
                                </div>
                            </div>
                            <div class="checkout-accordion-toggle-btn">
                                <span class="accordion-toggle-label" id="accordionToggleLabel">{{ $hasSavedAddresses ? 'Detail Alamat' : 'Tutup Detail' }}</span>
                                <span class="checkout-faq-icon" id="addressAccordionIcon">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </span>
                            </div>
                        </div>

                        <div class="checkout-faq-a" id="addressAccordionBody">
                            <div class="checkout-faq-a-inner">
                                <!-- Row 1: Recipient Identity (Biteship Point A) -->
                                <div class="form-row-2">
                                    <div class="form-group-custom">
                                        <label for="recipientName">
                                            <i class="fa-solid fa-user"></i> Nama Penerima
                                        </label>
                                        <input type="text" id="recipientName" name="recipient_name" class="form-input-custom" value="{{ $defaultAddr->recipient_name ?? (Auth::user()->nama ?? '') }}" placeholder="Nama lengkap penerima pesanan" required>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="recipientPhone">
                                            <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
                                        </label>
                                        <input type="tel" id="recipientPhone" name="recipient_phone" class="form-input-custom" value="{{ $defaultAddr->recipient_phone ?? (Auth::user()->no_hp ?? '') }}" placeholder="Contoh: 081234567890" required>
                                    </div>
                                </div>

                                <!-- Row 2: Area / District Autocomplete & Postal Code (Biteship Point B) -->
                                <div class="form-row-2">
                                    <div class="form-group-custom area-search-wrapper">
                                        <label for="areaSearchInput">
                                            <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
                                        </label>
                                        <input type="text" id="areaSearchInput" name="destination_area_name" class="form-input-custom" value="{{ $defaultAddr->destination_area_name ?? '' }}" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
                                        <input type="hidden" id="destinationAreaId" name="destination_area_id" value="{{ $defaultAddr->destination_area_id ?? '' }}">
                                        <div id="areaDropdownResults" class="area-dropdown-results"></div>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="postalCode">
                                            <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
                                        </label>
                                        <input type="text" id="postalCode" name="postal_code" class="form-input-custom" maxlength="5" inputmode="numeric" value="{{ $defaultAddr->postal_code ?? '' }}" placeholder="Contoh: 28282">
                                    </div>
                                </div>

                                <!-- Row 3: Street Address (Biteship Point B) -->
                                <div class="form-group-custom">
                                    <label for="streetAddress">
                                        <i class="fa-solid fa-location-dot"></i> Detail Alamat Lengkap
                                    </label>
                                    <input type="text" id="streetAddress" class="form-input-custom" value="{{ $defaultAddr->street_address ?? (Auth::user()->alamat ?? '') }}" placeholder="Detail jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required>
                                </div>

                                <!-- Row 4: Map & GPS Coordinate (Biteship Point B) -->
                                <div class="form-group-custom">
                                    <label>
                                        <i class="fa-solid fa-map-location-dot"></i> Titik Lokasi Pengantaran di Peta
                                    </label>
                                    <div class="map-wrapper">
                                        <div id="map"></div>
                                        <div class="map-note">
                                            <i class="fa-solid fa-location-crosshairs"></i>
                                            <span id="coordsDisplay">{{ ($defaultAddr && $defaultAddr->latitude) ? 'Titik koordinat tersimpan: ' . number_format($defaultAddr->latitude, 5) . ', ' . number_format($defaultAddr->longitude, 5) : 'Klik peta atau seret marker untuk menandai lokasi tepat Anda' }}</span>
                                        </div>
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude" value="{{ $defaultAddr->latitude ?? '' }}">
                                    <input type="hidden" id="longitude" name="longitude" value="{{ $defaultAddr->longitude ?? '' }}">
                                </div>

                                <!-- Save Address Option (When writing a new address) -->
                                <div class="form-group-custom" id="saveAddressGroup" style="margin-bottom: 8px; {{ $hasSavedAddresses ? 'display: none;' : '' }}">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #334155; font-weight: 500;">
                                        <input type="checkbox" id="saveAddressCheckbox" name="save_address" value="1" style="width: 16px; height: 16px; accent-color: #00b207; cursor: pointer;" onchange="toggleAddressLabel(this)">
                                        <span>Simpan alamat ini ke buku alamat saya untuk pesanan berikutnya</span>
                                    </label>
                                    <div id="addressLabelWrapper" style="display: none; margin-top: 6px;">
                                        <input type="text" id="newAddressLabel" class="form-input-custom" placeholder="Nama Label Alamat (Contoh: Rumah, Kantor, Toko)" style="max-width: 320px; font-size: 13px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Delivery Note -->
                    <div class="form-group-custom" style="margin-bottom: 0;">
                        <label for="orderNotes">
                            <i class="fa-regular fa-clipboard"></i> Catatan Pengiriman <span class="label-optional">(Opsional)</span>
                        </label>
                        <textarea id="orderNotes" class="form-textarea-custom" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam samping masjid" style="min-height: 76px;">{{ $defaultAddr->notes ?? '' }}</textarea>
                    </div>
                </form>
            </div>
            <!-- Step 2: Shipping Courier Method -->
            <div class="checkout-card">
                <div class="checkout-step-header">
                    <span class="step-badge">2</span>
                    <div>
                        <h2 class="checkout-section-title">Metode Pengiriman</h2>
                        <p class="checkout-section-subtitle">Pilih kurir pengantaran pesanan atau ambil langsung di outlet</p>
                    </div>
                </div>

                <div id="shippingRatesContainer">
                    <div class="shipping-grid">
                        <!-- Gojek -->
                        <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="20000" data-service="Gojek">
                            <input type="radio" name="shippingGroup" value="Gojek">
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo">
                                    <img src="{{ asset('image/couriers/gojek.svg') }}" alt="Gojek" />
                                </span>
                                <span class="shipping-name">Gojek</span>
                            </div>
                        </label>

                        <!-- Grab -->
                        <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="20000" data-service="Grab">
                            <input type="radio" name="shippingGroup" value="Grab">
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo">
                                    <img src="{{ asset('image/couriers/grab.svg') }}" alt="Grab" />
                                </span>
                                <span class="shipping-name">Grab</span>
                            </div>
                        </label>

                        <!-- Maxim (Default Checked) -->
                        <label class="shipping-card-option active" onclick="selectShippingOption(this)" data-cost="20000" data-service="Maxim">
                            <input type="radio" name="shippingGroup" value="Maxim" checked>
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo">
                                    <img src="{{ asset('image/couriers/maxim.svg') }}" alt="Maxim" />
                                </span>
                                <span class="shipping-name">Maxim</span>
                            </div>
                        </label>

                        <!-- Pick Up di Tempat -->
                        <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="0" data-service="Pick Up">
                            <input type="radio" name="shippingGroup" value="Pick Up">
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo store-logo">
                                    <img src="{{ asset('image/logo_circle.png') }}" alt="Pick Up" />
                                </span>
                                <span class="shipping-name">Pick Up di Tempat</span>
                                <span class="shipping-badge free">Gratis</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 3: Custom Cake Text Card (Conditional) -->
            <div class="checkout-card" id="customCakeTextCard" style="display: none;">
                <div class="checkout-step-header">
                    <span class="step-badge">3</span>
                    <div>
                        <h2 class="checkout-section-title" style="display: flex; align-items: center; gap: 8px;">
                            Tulisan di Atas Kue <span class="step-optional-badge">Opsional</span>
                        </h2>
                        <p class="checkout-section-subtitle">Permintaan teks ucapan khusus pada permukaan cake</p>
                    </div>
                </div>
                
                <!-- Notice Warning Banner -->
                <div class="custom-cake-notice">
                    <div class="notice-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div class="notice-body">
                        <strong>Ketentuan Penulisan Ucapan di Atas Kue:</strong>
                        <p>Mohon maaf, toko kami <strong>TIDAK MENERIMA</strong> penulisan ucapan <em>Selamat Ulang Tahun (HBD / Ultah)</em>, <em>Barakallah Fii Umrik</em>, <em>Anniversary (Anniv)</em>, serta <strong>angka</strong> dalam bentuk apa pun di atas kue.</p>
                        <p class="notice-examples">Contoh ucapan yang diperbolehkan: "Selamat Wisuda", "Semangat Kerja", "Terima Kasih", "Congratulations", dll.</p>
                    </div>
                </div>

                <div id="customTextInputsContainer">
                    <!-- Rendered by JS per product -->
                </div>
            </div>
        </div>

        <!-- Right Side: Sticky Order Summary, Payment & Checkout Action -->
        <div class="checkout-sidebar-col">
            <div class="summary-card sticky-summary-card">
                <div class="summary-card-header">
                    <h3 class="summary-card-title">Ringkasan Pesanan</h3>
                    <span class="summary-badge-count" id="summaryItemCount">0 Produk</span>
                </div>
                
                <!-- Product list container -->
                <div class="order-items-list" id="orderItemsSummary">
                    <!-- Rendered by JS -->
                </div>
                <!-- Section: Payment Method -->
                <div class="summary-section-divider"></div>
                <div class="summary-section-block">
                    <div class="summary-section-header">
                        <span class="summary-section-icon"><i class="fa-solid fa-credit-card"></i></span>
                        <h4 class="summary-section-title">Metode Pembayaran</h4>
                    </div>
                    
                    <div class="payment-options">
                        
                        <!-- Cash on Delivery -->
                        <div class="payment-option-item active" onclick="selectPaymentMethod('cod')">
                            <div class="payment-option-header">
                                <input type="radio" id="payCod" name="paymentGroup" value="COD" checked>
                                <label for="payCod" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                    <div>
                                        <div class="payment-title">Cash on Delivery (COD)</div>
                                        <div class="payment-subtitle">Bayar langsung tunai kepada kurir saat pesanan sampai</div>
                                    </div>
                                    <span class="payment-badge-tag"><i class="fa-solid fa-hand-holding-dollar"></i> Tunai</span>
                                </label>
                            </div>
                        </div>

                        <!-- Transfer Bank -->
                        <div class="payment-option-item" onclick="selectPaymentMethod('transfer')">
                            <div class="payment-option-header">
                                <input type="radio" id="payTransfer" name="paymentGroup" value="Transfer">
                                <label for="payTransfer" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                    <div>
                                        <div class="payment-title">Transfer Bank & Virtual Account</div>
                                    </div>
                                    <div class="payment-logos-preview">
                                        <span class="bank-pill">BCA</span>
                                        <span class="bank-pill">BRI</span>
                                        <span class="bank-pill">Mandiri</span>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-sub-options" id="transferSubOptions" style="display: none;">
                                <span class="sub-options-label">Pilih Bank Rekening:</span>
                                <div class="bank-chips-grid">
                                    <label class="payment-sub-chip">
                                        <input type="radio" name="bankSelect" value="BRI" checked>
                                        <span><i class="fa-solid fa-building-columns"></i> Bank BRI</span>
                                    </label>
                                    <label class="payment-sub-chip">
                                        <input type="radio" name="bankSelect" value="BCA">
                                        <span><i class="fa-solid fa-building-columns"></i> Bank BCA</span>
                                    </label>
                                    <label class="payment-sub-chip">
                                        <input type="radio" name="bankSelect" value="Mandiri">
                                        <span><i class="fa-solid fa-building-columns"></i> Bank Mandiri</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- E-Wallet -->
                        <div class="payment-option-item" onclick="selectPaymentMethod('ewallet')">
                            <div class="payment-option-header">
                                <input type="radio" id="payEwallet" name="paymentGroup" value="E-Wallet">
                                <label for="payEwallet" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                    <div>
                                        <div class="payment-title">E-Wallet & QRIS Digital</div>
                                    </div>
                                    <div class="payment-logos-preview">
                                        <span class="bank-pill qris">QRIS</span>
                                        <span class="bank-pill">DANA</span>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-sub-options" id="ewalletSubOptions" style="display: none;">
                                <span class="sub-options-label">Pilih Layanan E-Wallet:</span>
                                <div class="bank-chips-grid">
                                    <label class="payment-sub-chip">
                                        <input type="radio" name="walletSelect" value="DANA" checked>
                                        <span><i class="fa-solid fa-wallet"></i> DANA</span>
                                    </label>
                                    <label class="payment-sub-chip">
                                        <input type="radio" name="walletSelect" value="QRIS">
                                        <span><i class="fa-solid fa-qrcode"></i> QRIS Universal</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Totals -->
                <div class="summary-section-divider"></div>
                <div class="summary-totals">
                    <div class="summary-total-row">
                        <span class="label">Subtotal Produk</span>
                        <span class="value" id="summarySubtotal">Rp. 0</span>
                    </div>
                    <div class="summary-total-row">
                        <span class="label">Biaya Pengiriman</span>
                        <span class="value" id="summaryShipping">Rp. 20.000</span>
                    </div>
                    <div class="summary-total-row grand-total">
                        <span class="label">Total Pembayaran</span>
                        <span class="value price" id="summaryTotal">Rp. 0</span>
                    </div>
                </div>

                <!-- Primary Checkout CTA Button: Placed at the very bottom of summary card -->
                <button type="button" class="btn-place-order" onclick="submitOrder()">
                    <span>Buat Pesanan Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>


            </div>
        </div>
    </div>
</div>


<script>
    window.checkoutConfig = {
        userChartRoute: "{{ route('userChart') }}",
        checkoutPostRoute: "{{ route('checkout.post') }}",
        orderSuccessRoute: "{{ route('order.success') }}",
        customTextProductIds: @json($customTextProductIds ?? []),
        csrfToken: "{{ csrf_token() }}"
    };

    window.currentShippingCost = 20000;

    window.selectShippingOption = function(cardEl) {
        if (!cardEl) return;
        document.querySelectorAll('.shipping-card-option').forEach(el => el.classList.remove('active'));
        cardEl.classList.add('active');
        const radio = cardEl.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        
        const costAttr = cardEl.getAttribute('data-cost');
        const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
        window.currentShippingCost = isNaN(cost) ? 0 : cost;
        window.updateOrderTotalsWithShipping();
    };

    window.updateOrderTotalsWithShipping = function() {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        let subtotalVal = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const subtotalEl = document.getElementById('summarySubtotal');
        const shippingEl = document.getElementById('summaryShipping');
        const totalEl = document.getElementById('summaryTotal');
        
        if (subtotalEl) {
            subtotalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal);
        }
        
        const cost = window.currentShippingCost !== undefined ? window.currentShippingCost : 20000;
        if (shippingEl) {
            if (cost === 0) {
                shippingEl.className = 'value free-badge';
                shippingEl.textContent = 'Gratis';
            } else {
                shippingEl.className = 'value';
                shippingEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(cost);
            }
        }
        
        if (totalEl) {
            totalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal + cost);
        }
    };
    window.toggleAddressLabel = function(chk) {
        const wrapper = document.getElementById('addressLabelWrapper');
        if (wrapper) {
            wrapper.style.display = chk.checked ? 'block' : 'none';
            if (chk.checked) {
                const inp = document.getElementById('newAddressLabel');
                if (inp && !inp.value) inp.value = 'Alamat Rumah';
            }
        }
    };
    window.toggleAddressAccordion = function(forceOpen) {
        const acc = document.getElementById('checkoutAddressAccordion');
        const body = document.getElementById('addressAccordionBody');
        const label = document.getElementById('accordionToggleLabel');
        if (!acc || !body) return;

        const isCurrentlyOpen = acc.classList.contains('open');
        const shouldOpen = (forceOpen !== undefined) ? forceOpen : !isCurrentlyOpen;

        if (shouldOpen) {
            acc.classList.add('open');
            body.style.maxHeight = '2500px';
            if (label) label.textContent = 'Tutup Detail';

            // Invalidate Leaflet map layout after animation
            setTimeout(() => {
                if (typeof checkoutMap !== 'undefined' && checkoutMap) {
                    checkoutMap.invalidateSize();
                } else if (typeof window.checkoutMap !== 'undefined' && window.checkoutMap) {
                    window.checkoutMap.invalidateSize();
                }
            }, 350);
        } else {
            acc.classList.remove('open');
            body.style.maxHeight = '0px';
            if (label) label.textContent = 'Detail Alamat';
        }
    };

    window.updateSummaryPreview = function(name, street, hasGps) {
        const sumName = document.getElementById('summaryRecipientName');
        const sumStreet = document.getElementById('summaryStreetAddress');
        const sumGps = document.getElementById('summaryGpsBadge');

        if (sumName && name) sumName.textContent = name;
        if (sumStreet && street) sumStreet.textContent = street;
        if (sumGps) {
            sumGps.style.display = hasGps ? 'inline-flex' : 'none';
        }
    };

    window.onSavedAddressDropdownChange = function(selectEl) {
        if (!selectEl) return;
        const val = selectEl.value;

        if (val === 'new') {
            window.selectNewAddress();
        } else {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (opt && opt.dataset.address) {
                try {
                    const addr = JSON.parse(opt.dataset.address);
                    window.selectSavedAddress(addr);
                } catch (e) {
                    console.error("Gagal parse data alamat", e);
                }
            }
        }
    };

    window.selectSavedAddress = function(addr, cardEl) {
        if (!addr) return;
        document.querySelectorAll('.saved-address-card').forEach(c => c.classList.remove('active'));
        if (cardEl) cardEl.classList.add('active');

        const selectEl = document.getElementById('savedAddressSelect');
        if (selectEl && addr.id && selectEl.value !== String(addr.id)) {
            selectEl.value = String(addr.id);
        }

        const nameEl = document.getElementById('recipientName');
        const phoneEl = document.getElementById('recipientPhone');
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const streetEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        const coordsDisplay = document.getElementById('coordsDisplay');
        const saveGroup = document.getElementById('saveAddressGroup');

        if (nameEl) nameEl.value = addr.recipient_name || '';
        if (phoneEl) phoneEl.value = addr.recipient_phone || '';
        if (areaInput) areaInput.value = addr.destination_area_name || '';
        if (areaIdEl) areaIdEl.value = addr.destination_area_id || '';
        if (postalEl) postalEl.value = addr.postal_code || '';
        if (streetEl) streetEl.value = addr.street_address || '';
        if (notesEl && addr.notes) notesEl.value = addr.notes;

        const hasGps = !!(addr.latitude && addr.longitude);
        if (hasGps) {
            if (latEl) latEl.value = addr.latitude;
            if (lngEl) lngEl.value = addr.longitude;
            if (coordsDisplay) coordsDisplay.textContent = `Titik koordinat tersimpan: ${parseFloat(addr.latitude).toFixed(5)}, ${parseFloat(addr.longitude).toFixed(5)}`;
            if (typeof checkoutMarker !== 'undefined' && checkoutMarker && typeof checkoutMap !== 'undefined' && checkoutMap) {
                const pos = [parseFloat(addr.latitude), parseFloat(addr.longitude)];
                checkoutMarker.setLatLng(pos);
                checkoutMap.setView(pos, 15);
            }
        }

        window.updateSummaryPreview(addr.recipient_name, addr.street_address, hasGps);

        if (saveGroup) {
            saveGroup.style.display = 'none';
            const chk = document.getElementById('saveAddressCheckbox');
            if (chk) chk.checked = false;
            const labelWrap = document.getElementById('addressLabelWrapper');
            if (labelWrap) labelWrap.style.display = 'none';
        }
    };

    window.selectNewAddress = function(cardEl) {
        document.querySelectorAll('.saved-address-card').forEach(c => c.classList.remove('active'));
        if (cardEl) cardEl.classList.add('active');

        const selectEl = document.getElementById('savedAddressSelect');
        if (selectEl && selectEl.value !== 'new') {
            selectEl.value = 'new';
        }

        const nameEl = document.getElementById('recipientName');
        const phoneEl = document.getElementById('recipientPhone');
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const streetEl = document.getElementById('streetAddress');
        const saveGroup = document.getElementById('saveAddressGroup');

        if (nameEl) nameEl.value = '{{ Auth::user()->nama ?? '' }}';
        if (phoneEl) phoneEl.value = '{{ Auth::user()->no_hp ?? '' }}';
        if (areaInput) areaInput.value = '';
        if (areaIdEl) areaIdEl.value = '';
        if (postalEl) postalEl.value = '';
        if (streetEl) {
            streetEl.value = '';
        }

        window.updateSummaryPreview('Alamat Baru', 'Masukkan rincian alamat baru...', false);

        if (saveGroup) {
            saveGroup.style.display = 'block';
            const chk = document.getElementById('saveAddressCheckbox');
            if (chk) {
                chk.checked = true;
                window.toggleAddressLabel(chk);
            }
        }

        window.toggleAddressAccordion(true);
        if (streetEl) streetEl.focus();
    };
    document.addEventListener('DOMContentLoaded', function() {
        renderCustomTextCard();
        initAreaAutocomplete();
        window.updateOrderTotalsWithShipping();

        const acc = document.getElementById('checkoutAddressAccordion');
        const body = document.getElementById('addressAccordionBody');
        if (acc && body) {
            if (acc.classList.contains('open')) {
                body.style.maxHeight = '2500px';
            } else {
                body.style.maxHeight = '0px';
            }
        }
    });

    function initAreaAutocomplete() {
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdInput = document.getElementById('destinationAreaId');
        const postalInput = document.getElementById('postalCode');
        const resultsContainer = document.getElementById('areaDropdownResults');
        if (!areaInput || !resultsContainer) return;

        const sampleAreas = [
            { id: 'ID_PKU_01', name: 'Sukajadi', city: 'Pekanbaru, Riau', postal: '28121' },
            { id: 'ID_PKU_02', name: 'Tampan / Tuah Madani', city: 'Pekanbaru, Riau', postal: '28292' },
            { id: 'ID_PKU_03', name: 'Marpoyan Damai', city: 'Pekanbaru, Riau', postal: '28282' },
            { id: 'ID_PKU_04', name: 'Bukit Raya', city: 'Pekanbaru, Riau', postal: '28288' },
            { id: 'ID_PKU_05', name: 'Payung Sekaki', city: 'Pekanbaru, Riau', postal: '28291' },
            { id: 'ID_PKU_06', name: 'Rumbai / Rumbai Pesisir', city: 'Pekanbaru, Riau', postal: '28261' },
            { id: 'ID_PKU_07', name: 'Tenayan Raya / Kulim', city: 'Pekanbaru, Riau', postal: '28285' },
            { id: 'ID_PKU_08', name: 'Senapelan', city: 'Pekanbaru, Riau', postal: '28153' },
            { id: 'ID_PKU_09', name: 'Limapuluh', city: 'Pekanbaru, Riau', postal: '28144' },
            { id: 'ID_PKU_10', name: 'Bina Widya', city: 'Pekanbaru, Riau', postal: '28293' }
        ];

        areaInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            const matches = sampleAreas.filter(a => 
                a.name.toLowerCase().includes(query) || 
                a.city.toLowerCase().includes(query) || 
                a.postal.includes(query)
            );

            if (matches.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="area-dropdown-item" style="color: #64748b; cursor: default;">
                        Wilayah tidak ditemukan di daftar cepat. Anda dapat mengetik manual.
                    </div>
                `;
                resultsContainer.style.display = 'block';
                if (areaIdInput) areaIdInput.value = 'CUSTOM_' + query.replace(/[^a-zA-Z0-9]/g, '_').toUpperCase();
                return;
            }

            resultsContainer.innerHTML = matches.map(area => `
                <div class="area-dropdown-item" onclick="selectArea('${area.id}', '${area.name}, ${area.city}', '${area.postal}')">
                    <strong>${area.name}</strong>
                    <div class="area-detail">${area.city} &bull; Kode Pos: ${area.postal}</div>
                </div>
            `).join('');
            resultsContainer.style.display = 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!areaInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    window.selectArea = function(id, name, postal) {
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdInput = document.getElementById('destinationAreaId');
        const postalInput = document.getElementById('postalCode');
        const resultsContainer = document.getElementById('areaDropdownResults');

        if (areaInput) areaInput.value = name;
        if (areaIdInput) areaIdInput.value = id;
        if (postalInput) postalInput.value = postal;
        if (resultsContainer) resultsContainer.style.display = 'none';
    };
    function renderCustomTextCard() {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const card = document.getElementById('customCakeTextCard');
        const container = document.getElementById('customTextInputsContainer');
        if (!card || !container) return;

        const serverIds = (window.checkoutConfig && window.checkoutConfig.customTextProductIds) ? window.checkoutConfig.customTextProductIds : [];

        // Sync flags for items in localCart
        localCart.forEach(item => {
            if (serverIds.includes(String(item.id))) {
                item.bisa_custom_teks = true;
            }
        });

        const customItems = localCart.filter(item => 
            item.bisa_custom_teks === true || 
            item.bisa_custom_teks === 1 || 
            item.bisa_custom_teks === '1' || 
            item.bisa_custom_teks === 'true' ||
            serverIds.includes(String(item.id))
        );
        
        if (customItems.length === 0) {
            card.style.display = 'none';
            return;
        }

        card.style.display = 'block';
        container.innerHTML = customItems.map(item => `
            <div class="form-group-custom" style="margin-bottom: 15px;">
                <label for="custom_text_${item.id}" style="font-weight: 600; color: #1a1a1a; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-cake-candles" style="color: #00b207;"></i> Tulisan di Atas Kue: <span style="color: #00b207;">${item.name}</span>
                </label>
                <input type="text" id="custom_text_${item.id}" class="form-input-custom custom-text-field"
                    placeholder="Contoh: Selamat Wisuda / Sukses Selalu / Terima Kasih"
                    value="${item.custom_text || ''}"
                    onchange="saveCustomTextToCart('${item.id}', this.value)">
            </div>
        `).join('');
    }

    window.saveCustomTextToCart = function(id, text) {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const item = localCart.find(i => i.id === id);
        if (item) {
            item.custom_text = text.trim();
            localStorage.setItem('zyh_cart', JSON.stringify(localCart));
        }
    };

    // Override window.submitOrder to integrate Midtrans Snap
    window.submitOrder = function() {
        const form = document.getElementById('checkoutForm');
        if (form && !form.checkValidity()) {
            window.toggleAddressAccordion(true);
            setTimeout(() => {
                form.reportValidity();
            }, 150);
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

        // Collect custom text values from inputs
        localCart.forEach(item => {
            const input = document.getElementById('custom_text_' + item.id);
            if (input) {
                item.custom_text = input.value.trim();
            }
        });
        localStorage.setItem('zyh_cart', JSON.stringify(localCart));

        const recipientNameEl = document.getElementById('recipientName');
        const recipientPhoneEl = document.getElementById('recipientPhone');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const addrEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');

        let recipientName = recipientNameEl ? recipientNameEl.value.trim() : '';
        let recipientPhone = recipientPhoneEl ? recipientPhoneEl.value.trim() : '';
        let destinationAreaId = areaIdEl ? areaIdEl.value.trim() : '';
        let postalCode = postalEl ? postalEl.value.trim() : '';
        let streetAddress = addrEl ? addrEl.value : '';
        let notes = notesEl ? notesEl.value : '';
        
        const payRadio = document.querySelector('input[name="paymentGroup"]:checked');
        const shipRadio = document.querySelector('input[name="shippingGroup"]:checked');
        let paymentVal = payRadio ? payRadio.value : 'COD';
        let shippingVal = shipRadio ? shipRadio.value : 'Maxim';
        let shippingCost = window.currentShippingCost !== undefined ? window.currentShippingCost : 20000;

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
                shipping_method: shippingVal,
                recipient_name: recipientName,
                recipient_phone: recipientPhone,
                destination_area_id: destinationAreaId,
                destination_area_name: document.getElementById('areaSearchInput')?.value || '',
                postal_code: postalCode,
                shipping_cost: shippingCost,
                save_address: document.getElementById('saveAddressCheckbox')?.checked || false,
                address_label: document.getElementById('newAddressLabel')?.value.trim() || ''
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
