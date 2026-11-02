@extends('Layout.layout_pelanggan')

@section('title', 'Checkout - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('userChart') }}">Shopping Cart</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span>Checkout</span>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/css_checkout.css') }}?v={{ file_exists(public_path('css/css_checkout.css')) ? filemtime(public_path('css/css_checkout.css')) : time() }}">
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
                        <div class="checkout-faq-q" 
                             onclick="toggleAddressAccordion()" 
                             onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleAddressAccordion(); }"
                             id="addressAccordionHeader"
                             role="button"
                             tabindex="0"
                             aria-expanded="{{ $hasSavedAddresses ? 'false' : 'true' }}"
                             aria-controls="addressAccordionBody">
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
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <label style="margin-bottom: 0;">
                                            <i class="fa-solid fa-map-location-dot"></i> Titik Lokasi Pengantaran di Peta
                                        </label>
                                        <button type="button" class="btn-locate-me-checkout" onclick="locateUserPositionCheckout()">
                                            <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                                        </button>
                                    </div>

                                    <!-- Map Geocoding Search Bar -->
                                    <div class="map-search-container" id="checkoutMapSearchContainer">
                                        <div class="map-search-bar">
                                            <div class="map-search-input-wrap">
                                                <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                                                <input type="text" id="checkoutMapSearchInput" class="map-search-input" 
                                                       placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                                                       autocomplete="off"
                                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnCheckoutMap(); }"
                                                       oninput="toggleClearCheckoutSearchBtn()">
                                                <button type="button" class="btn-clear-map-search" id="checkoutClearSearchBtn" onclick="clearCheckoutMapSearch()" style="display: none;" title="Hapus pencarian">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <button type="button" class="btn-search-map" onclick="searchLocationOnCheckoutMap()">
                                                Cari
                                            </button>
                                        </div>
                                        <div id="checkoutMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
                                    </div>
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
                <div class="checkout-step-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span class="step-badge">2</span>
                        <div>
                            <h2 class="checkout-section-title">Metode Pengiriman</h2>
                            <p class="checkout-section-subtitle">Pilih kurir pengantaran pesanan atau ambil langsung di outlet</p>
                        </div>
                    </div>
                    <button type="button" class="btn-refresh-shipping" id="btnRefreshShipping" onclick="refreshShippingRates()" title="Cek & perbarui tarif ongkir berdasarkan wilayah tujuan">
                        <i class="fa-solid fa-arrows-rotate" id="refreshShippingIcon"></i>
                        <span id="refreshShippingText">Cek Ongkir</span>
                    </button>
                </div>

                <div id="shippingRatesContainer">
                    <div class="shipping-grid">
                        <!-- Gojek -->
                        <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="18000" data-service="Gojek">
                            <input type="radio" name="shippingGroup" value="Gojek">
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo">
                                    <img src="{{ asset('image/couriers/gojek.svg') }}" alt="Gojek" />
                                </span>
                                <span class="shipping-name">Gojek</span>
                                <span class="shipping-cost-tag" id="costGojek">Rp. 18.000</span>
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
                                <span class="shipping-cost-tag" id="costGrab">Rp. 20.000</span>
                            </div>
                        </label>

                        <!-- Maxim (Default Checked) -->
                        <label class="shipping-card-option active" onclick="selectShippingOption(this)" data-cost="12000" data-service="Maxim">
                            <input type="radio" name="shippingGroup" value="Maxim" checked>
                            <div class="shipping-card-content-simple">
                                <span class="shipping-courier-logo">
                                    <img src="{{ asset('image/couriers/maxim.svg') }}" alt="Maxim" />
                                </span>
                                <span class="shipping-name">Maxim</span>
                                <span class="shipping-cost-tag" id="costMaxim">Rp. 12.000</span>
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
                                        <div class="payment-subtitle">Transfer manual atau Virtual Account terverifikasi</div>
                                    </div>
                                    <div class="payment-logos-preview">
                                        <span class="bank-pill">BCA</span>
                                        <span class="bank-pill">BRI</span>
                                        <span class="bank-pill">Mandiri</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- E-Wallet & QRIS Digital -->
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
        biteshipRatesRoute: "{{ route('biteship.rates') }}",
        biteshipAreasRoute: "{{ route('biteship.areas') }}",
        defaultUserNama: "{{ Auth::user()->nama ?? '' }}",
        defaultUserNoHp: "{{ Auth::user()->no_hp ?? '' }}",
        customTextProductIds: @json($customTextProductIds ?? []),
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/js_checkout.js') }}?v={{ file_exists(public_path('js/js_checkout.js')) ? filemtime(public_path('js/js_checkout.js')) : time() }}"></script>
@endsection
