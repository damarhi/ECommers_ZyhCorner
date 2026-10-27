<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zyh Corner')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="{{ asset('css/pelanggan.css') }}?v={{ file_exists(public_path('css/pelanggan.css')) ? filemtime(public_path('css/pelanggan.css')) : time() }}">
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png" />
    <!-- GSAP & SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('styles')
    @vite(['resources/css/pelanggan_pages.css', 'resources/js/pelanggan_pages.js'])
    <style>
        .search-bar {
            overflow: visible !important;
            position: relative !important;
        }
        .search-input-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color, #e6e6e6);
            border-radius: 4px;
            overflow: hidden;
            width: 100%;
            background: #ffffff;
        }
        .search-suggestions-dropdown {
            position: absolute !important;
            top: calc(100% + 6px) !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
            z-index: 9999 !important;
            overflow: hidden !important;
        }
        .search-suggestion-item:hover {
            background-color: #f0fdf4 !important;
        }
        .search-suggestion-footer:hover {
            background-color: #e6f7ec !important;
        }
    </style>
    <script>
        window.storePhone = "{{ $storeSettings['wa_number'] ?? '628123456789' }}";
        window.storeName = "{!! addslashes($storeSettings['store_name'] ?? 'Zyh Corner') !!}";
    </script>
</head>

<body>

    <!-- Header -->
    <header>
        <div class="container top-header">
            <div class="logo">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu(true)"
                    style="background: none; border: none; font-size: 20px; color: #1a1a1a; cursor: pointer; display: none; margin-right: 5px; padding: 5px 0;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo"
                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                <div class="logo-text">{{ $storeSettings['store_name'] ?? 'Zyh Corner' }}</div>
            </div>

            <form id="headerSearchForm" action="{{ request()->routeIs('custom-products') ? route('custom-products') : route('shop') }}" method="GET" class="search-bar" style="margin: 0; position: relative; overflow: visible !important;">
                <div class="search-input-wrapper">
                    <span style="padding-left: 15px; color: #999;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="headerSearchInput" autocomplete="off" placeholder="Search Product. . ." value="{{ request('search') }}" style="flex: 1; padding: 10px 15px; border: none; outline: none; background: transparent;">
                    <button type="submit" style="background-color: #00b207; color: white; border: none; padding: 11px 25px; font-weight: 500; cursor: pointer;">Search</button>
                </div>
                <div id="headerSearchSuggestions" class="search-suggestions-dropdown" style="display: none;"></div>
            </form>

            <div class="header-actions">
                <div class="cart-info" style="cursor: pointer;" onclick="toggleCart(true)">
                    <div class="cart-icon-wrapper">
                        <i class="fa-solid fa-bag-shopping action-icon"></i>
                        <span class="cart-badge" id="cartBadge">0</span>
                    </div>
                    <div class="cart-text">
                        <span>Shopping cart:</span>
                        <span id="cartSubtotalHeader">Rp. 0</span>
                    </div>
                </div>
                <div class="profile-dropdown-wrapper" id="profileDropdownWrapper">
                    <div id="profileDropdownBtn"
                        style="color: inherit; cursor: pointer; display: flex; align-items: center;">
                        @auth
                            <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : asset('image/default-avatar.svg') }}" 
                                 alt="Profile" 
                                 style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1.5px solid #00b207;">
                        @else
                            <i class="fa-regular fa-user action-icon"></i>
                        @endauth
                    </div>
                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        @auth
                            <div class="profile-dropdown-item profile-dropdown-name">{{ Auth::user()->nama ?? 'User' }}
                            </div>
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ url('/admin/dashboard') }}" class="profile-dropdown-item"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard Admin</a>
                            @elseif(Auth::user()->role === 'kasir')
                                <a href="{{ route('orders.index') }}" class="profile-dropdown-item"><i class="fa-solid fa-cash-register me-1"></i> Kelola Pesanan</a>
                            @else
                                <a href="{{ route('dashboard') }}" class="profile-dropdown-item">Dashboard</a>
                            @endif
                            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="button" onclick="confirmLogout()" class="profile-dropdown-item"
                                    style="width: 100%; text-align: left; background: none; border: none; font-family: inherit; cursor: pointer;">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="profile-dropdown-item">Login</a>
                            <a href="#" class="profile-dropdown-item">Settings</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

    </header>

    <nav class="navigation">
        <div class="container nav-container">
            <div class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home </a>
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">Shop </a>
                <a href="{{ route('custom-products') }}" class="{{ request()->routeIs('custom-products') ? 'active' : '' }}">Custom Products </a>
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact Us</a>
                <a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a>
            </div>
            <div class="nav-phone">
                <i class="fa-solid fa-phone-volume"></i>
                <span>(+62) 8123456789</span>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb Area -->
    @if (!request()->routeIs('home', 'shop', 'custom-products'))
        <div class="breadcrumb-wrapper">
            <div class="breadcrumb-area">
                <div class="breadcrumb">
                    <a href="#"><i class="fa-solid fa-house"></i></a>
                    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <a href="#">Account</a>
                        <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
                        <span>Log In</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @yield('content')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top" style="grid-template-columns: 2.2fr 1fr 1fr 1fr; gap: 30px;">
                <div class="footer-col-1">
                    <div class="logo" style="color: white;">
                        <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo"
                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                        <div class="logo-text">{{ $storeSettings['store_name'] ?? 'Zyh Corner' }}</div>
                    </div>
                    <p style="margin: 15px 0; color: #999; font-size: 14px; line-height: 1.6; max-width: 320px;">{{ $storeSettings['store_tagline'] ?? 'Toko Pastry & Bakery pilihan terbaik di Pekanbaru. Menyediakan aneka cake, kue ulang tahun, dessert, dan pesanan kue kustom berkualitas tinggi.' }}</p>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 15px;">
                        <a href="tel:+{{ $storeSettings['wa_number'] ?? '628123456789' }}" style="display: inline-flex; align-items: center; gap: 10px; color: #cccccc; font-size: 14px; font-weight: 500; text-decoration: none;">
                            <i class="fa-solid fa-phone-volume" style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); color: #00b207; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;"></i>
                            <span>{{ $storeSettings['store_phone'] ?? '(+62) 812-3456-789' }}</span>
                        </a>
                        <a href="mailto:{{ $storeSettings['store_email'] ?? 'contact@zyhcorner.com' }}" style="display: inline-flex; align-items: center; gap: 10px; color: #cccccc; font-size: 14px; font-weight: 500; text-decoration: none;">
                            <i class="fa-regular fa-envelope" style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); color: #00b207; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;"></i>
                            <span>{{ $storeSettings['store_email'] ?? 'contact@zyhcorner.com' }}</span>
                        </a>
                        @if (!empty($storeSettings['social_instagram']))
                        <a href="{{ $storeSettings['social_instagram'] }}" target="_blank" style="display: inline-flex; align-items: center; gap: 10px; color: #cccccc; font-size: 14px; font-weight: 500; text-decoration: none;">
                            <i class="fa-brands fa-instagram" style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); color: #00b207; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;"></i>
                            <span>Instagram</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Akun Saya</h3>
                    <div class="footer-links">
                        <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? url('/admin/dashboard') : route('dashboard') }}">Akun Saya</a>
                        <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? route('orders.index') : route('order.history') }}">Riwayat Pesanan</a>
                        <a href="{{ route('userChart') }}">Keranjang Belanja</a>
                        <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? route('settings.index') : route('settings') }}">Pengaturan Profil</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Bantuan</h3>
                    <div class="footer-links">
                        <a href="{{ route('contact') }}">Kontak Kami</a>
                        <a href="{{ route('faq') }}">FAQ / Tanya Jawab</a>
                        <a href="{{ route('contact') }}">Lokasi Toko</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>{{ $storeSettings['store_name'] ?? 'Zyh Corner' }}</h3>
                    <div class="footer-links">
                        <a href="{{ route('about') }}">Tentang Kami</a>
                        <a href="{{ route('shop') }}">Katalog Produk</a>
                        <a href="{{ route('custom-products') }}">Produk Kustom</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom" style="justify-content: center; text-align: center; border-top: 1px solid #262626; padding: 20px 0;">
                <div style="color: #888; font-size: 13.5px;">
                    {{ $storeSettings['store_name'] ?? 'Zyh Corner' }} eCommerce © {{ date('Y') }}. All Rights Reserved
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Navigation Drawer -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileMenu(false)"></div>
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <div class="mobile-nav-header">
            <div class="logo">
                <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo"
                    style="width: 35px; height: 35px; object-fit: cover; border-radius: 50%;">
                <div class="logo-text" style="font-size: 18px;">{{ $storeSettings['store_name'] ?? 'Zyh Corner' }}</div>
            </div>
            <button class="close-mobile-menu" onclick="toggleMobileMenu(false)"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="mobile-nav-body">
            <form id="mobileSearchForm" action="{{ request()->routeIs('custom-products') ? route('custom-products') : route('shop') }}" method="GET" style="margin-bottom: 20px; position: relative; overflow: visible !important;">
                <div class="search-input-wrapper" style="border-radius: 6px;">
                    <span style="padding-left: 12px; color: #999;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="mobileSearchInput" autocomplete="off" placeholder="Search Product. . ." value="{{ request('search') }}" style="flex: 1; padding: 10px 12px; border: none; outline: none; font-size: 14px;">
                    <button type="submit" style="background: #00b207; color: white; border: none; padding: 10px 16px; font-weight: 500; font-size: 14px; cursor: pointer;">Search</button>
                </div>
                <div id="mobileSearchSuggestions" class="search-suggestions-dropdown" style="display: none;"></div>
            </form>
            <div class="mobile-nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">Shop</a>
                <a href="{{ route('custom-products') }}" class="{{ request()->routeIs('custom-products') ? 'active' : '' }}">Custom Products</a>
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact Us</a>
            </div>
            <div class="mobile-nav-phone"
                style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f2f2f2; display: flex; align-items: center; gap: 8px; font-size: 14px; color: #666;">
                <i class="fa-solid fa-phone-volume" style="color: #00b207;"></i>
                <span>(+62) 8123456789</span>
            </div>
        </div>
    </div>

    <!-- Shopping Cart Sidebar -->
    <div class="cart-sidebar-overlay" id="cartOverlay" onclick="toggleCart(false)"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-sidebar-header">
            <h2>Shopping Cart (<span id="cartCount">0</span>)</h2>
            <button class="close-cart" onclick="toggleCart(false)"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="cart-items-list" id="cartItemsList">
            <!-- Cart items injected here -->
        </div>

        <div class="cart-sidebar-footer">
            <div class="cart-total-row">
                <span>Subtotal:</span>
                <span id="cartSubtotal">Rp. 0</span>
            </div>
            <div class="cart-btns">
                @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']))
                    <button type="button" class="btn-checkout" onclick="showAdminShoppingNotice()" style="cursor: pointer; border: none; font-family: inherit;">Checkout</button>
                    <button type="button" class="btn-view-cart" onclick="showAdminShoppingNotice()" style="cursor: pointer; border: none; font-family: inherit;">Go To Cart</button>
                @else
                    <a href="{{ route('userChart') }}" class="btn-checkout">Checkout</a>
                    <a href="{{ route('userChart') }}" class="btn-view-cart">Go To Cart</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Product Quick View Modal -->
    <div class="product-modal-overlay" id="productModal" onclick="if(event.target === this) closeProductModal();">
        <div class="product-modal" role="dialog" aria-modal="true" aria-labelledby="pmName">
            <button type="button" class="close-modal" onclick="closeProductModal()" aria-label="Tutup preview modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="product-modal-content">
                <!-- Gallery Section -->
                <div class="pm-gallery">
                    <div class="pm-thumbnails-container" id="pmThumbnailsContainer">
                        <button type="button" class="pm-thumb-nav" id="pmThumbPrev" onclick="scrollModalThumbs(-1)" aria-label="Foto sebelumnya">
                            <i class="fa-solid fa-chevron-up"></i>
                        </button>
                        <div class="pm-thumbnails" id="pmThumbnails">
                            <!-- Thumbnails injected dynamically -->
                        </div>
                        <button type="button" class="pm-thumb-nav" id="pmThumbNext" onclick="scrollModalThumbs(1)" aria-label="Foto berikutnya">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="pm-main-image">
                        <img id="pmMainImage" src="" alt="Product Preview">
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="pm-details">
                    <div class="pm-header-group">
                        <span class="pm-eyebrow" id="pmEyebrow">Kategori Produk</span>
                        <h2 class="pm-title" id="pmName">Product Name</h2>
                        <div class="pm-meta-row">
                            <span class="pm-stock-pill" id="pmStock">
                                <span class="pm-status-dot"></span>
                                <span class="pm-stock-text">Tersedia</span>
                            </span>
                            <span class="pm-meta-separator">•</span>
                            <span class="pm-sku-text">SKU: <strong id="pmSku">ZYH-0001</strong></span>
                        </div>
                    </div>

                    <!-- Price Box -->
                    <div class="pm-price-area">
                        <span class="pm-new-price" id="pmNewPrice"></span>
                        <span class="pm-old-price" id="pmOldPrice" style="display: none;"></span>
                        <span class="pm-discount-badge" id="pmDiscountBadge" style="display: none;"></span>
                    </div>

                    <!-- Description -->
                    <div class="pm-desc" id="pmDesc"></div>

                    <!-- Action Controls (Aligned 48px Height) -->
                    <div class="pm-actions">
                        <div class="pm-qty">
                            <button type="button" class="pm-qty-btn" onclick="updateModalQty(-1)" aria-label="Kurangi kuantitas">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" readonly aria-label="Jumlah pesanan">
                            <button type="button" class="pm-qty-btn" onclick="updateModalQty(1)" aria-label="Tambah kuantitas">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                        @php
                            $isCustomContext = request()->query('custom') == 1 || request()->routeIs('custom-products') || (isset($product) && isset($product->jenis_produk) && $product->jenis_produk === 'kustom');
                        @endphp
                        <button type="button" class="pm-buy-btn" id="pmBuyBtn" style="{{ $isCustomContext ? 'background-color: #25d366;' : '' }}">
                            @if ($isCustomContext)
                                Pesan via WhatsApp <i class="fa-brands fa-whatsapp"></i>
                            @else
                                Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
                            @endif
                        </button>
                        <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </button>
                        <div id="pmStockWarning" class="pm-stock-warning" style="display: none;">
                            <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok produk tercapai
                        </div>
                    </div>

                    <!-- Metadata Footer -->
                    <div class="pm-info-footer">
                        <div class="pm-info-item">
                            <span class="pm-info-label">Kategori:</span>
                            <span class="pm-chip pm-chip-primary" id="pmCategory">-</span>
                        </div>
                        <div class="pm-info-item pm-tags-item">
                            <span class="pm-info-label">Tags:</span>
                            <div class="pm-tags-list" id="pmTag">
                                <!-- Tag chips injected dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.layoutConfig = {
            isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
            userRole: "{{ Auth::check() ? Auth::user()->role : 'guest' }}",
            loginRoute: "{{ route('login') }}",
            checkoutRoute: "{{ route('checkout') }}",
            cartRoute: "{{ route('userChart') }}",
            posRoute: "{{ route('pos.index') }}",
            adminDashboardRoute: "{{ url('/admin/dashboard') }}",
            successMessage: "{{ session('success') }}",
            searchSuggestionsRoute: "{{ route('search.suggestions') }}",
            productsStockMap: @json(\App\Models\Produk::all()->pluck('stok', 'id_produk'))
        };
        // Inline Fail-proof Search Suggestions Handler
        document.addEventListener('DOMContentLoaded', function() {
            function initSearch(inputId, dropdownId, formId) {
                var inputEl = document.getElementById(inputId);
                var dropdownEl = document.getElementById(dropdownId);
                var formEl = document.getElementById(formId);
                if (!inputEl || !dropdownEl) return;

                var timer = null;

                inputEl.addEventListener('input', function() {
                    var q = this.value.trim();
                    clearTimeout(timer);

                    if (q.length < 2) {
                        dropdownEl.style.display = 'none';
                        dropdownEl.innerHTML = '';
                        return;
                    }

                    dropdownEl.style.display = 'block';
                    dropdownEl.innerHTML = '<div style="padding: 14px; text-align: center; color: #888; font-size: 13px;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px; color: #00b207;"></i> Mencari produk...</div>';

                    timer = setTimeout(function() {
                        var targetUrl = window.layoutConfig.searchSuggestionsRoute + '?q=' + encodeURIComponent(q);
                        fetch(targetUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (!Array.isArray(data) || data.length === 0) {
                                dropdownEl.style.display = 'block';
                                dropdownEl.innerHTML = '<div style="padding: 16px; text-align: center; color: #777; font-size: 13.5px;"><i class="fa-solid fa-face-frown" style="font-size: 20px; color: #bbb; display: block; margin-bottom: 6px;"></i>Tidak ada produk yang cocok</div>';
                                return;
                            }

                            var html = '<div style="max-height: 360px; overflow-y: auto;">';
                            data.forEach(function(item) {
                                var stockBadge = (item.stok === '0' || String(item.stok).toLowerCase() === 'habis') ? '<span style="font-size: 11px; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-weight: 500;">Habis</span>' : '';
                                var customBadge = item.is_kustom ? '<span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: 500; margin-left: 4px;">Kustom</span>' : '';

                                html += '<a href="' + item.url + '" class="search-suggestion-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; text-decoration: none; color: inherit; border-bottom: 1px solid #f2f2f2; transition: background 0.2s ease;">' +
                                    '<img src="' + item.image + '" alt="' + item.name + '" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; flex-shrink: 0; background: #f9f9f9;">' +
                                    '<div style="flex: 1; min-width: 0;">' +
                                        '<div style="font-weight: 600; font-size: 14px; color: #1a1a1a; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">' + item.name + '</div>' +
                                        '<div style="font-size: 12px; color: #888; display: flex; align-items: center; gap: 4px; margin-top: 2px;"><span>' + item.category + '</span>' + customBadge + stockBadge + '</div>' +
                                    '</div>' +
                                    '<div style="font-weight: 600; font-size: 13.5px; color: #00b207; white-space: nowrap; flex-shrink: 0;">' + item.price + '</div>' +
                                '</a>';
                            });
                            html += '</div>';

                            var formAction = formEl ? formEl.getAttribute('action') : '/shop';
                            var searchUrl = formAction + '?search=' + encodeURIComponent(q);
                            html += '<a href="' + searchUrl + '" class="search-suggestion-footer" style="display: block; text-align: center; padding: 11px 14px; background: #f8faf8; color: #00b207; font-weight: 600; font-size: 13px; text-decoration: none; border-top: 1px solid #e6e6e6; transition: background 0.2s;">Lihat semua hasil untuk "' + q + '" <i class="fa-solid fa-arrow-right" style="margin-left: 4px; font-size: 11px;"></i></a>';

                            dropdownEl.style.display = 'block';
                            dropdownEl.innerHTML = html;
                        })
                        .catch(function(err) {
                            console.error(err);
                            dropdownEl.style.display = 'none';
                        });
                    }, 350);
                });

                document.addEventListener('click', function(e) {
                    if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                        dropdownEl.style.display = 'none';
                    }
                });

                inputEl.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        dropdownEl.style.display = 'none';
                    }
                });

                inputEl.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2 && dropdownEl.children.length > 0) {
                        dropdownEl.style.display = 'block';
                    }
                });
            }

            initSearch('headerSearchInput', 'headerSearchSuggestions', 'headerSearchForm');
            initSearch('mobileSearchInput', 'mobileSearchSuggestions', 'mobileSearchForm');
        });
    </script>

    <!-- Anti-bfcache Back Button Protection -->
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
    @yield('scripts')
</body>

</html>
