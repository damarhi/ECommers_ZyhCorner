<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Zyh Corner')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="{{ asset('css/pelanggan.css') }}?v={{ file_exists(public_path('css/pelanggan.css')) ? filemtime(public_path('css/pelanggan.css')) : time() }}">
    <!-- SweetAlert2 -->
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('styles')
    @vite(['resources/css/pelanggan_pages.css', 'resources/js/pelanggan_pages.js'])
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
                <div class="logo-text">Zyh Corner</div>
            </div>

            <form action="{{ route('shop') }}" method="GET" class="search-bar" style="margin: 0;">
                <span style="padding-left: 15px; color: #999;"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" placeholder="Search Product. . ." value="{{ request('search') }}">
                <button type="submit">Search</button>
            </form>

            <div class="header-actions">
                <i class="fa-regular fa-heart action-icon"></i>
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
                            <a href="{{ route('dashboard') }}" class="profile-dropdown-item">Dashboard</a>
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
            <div class="footer-top">
                <div class="footer-col-1">
                    <div class="logo" style="color: white;">
                        <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo"
                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                        <div class="logo-text">Zyh Corner</div>
                    </div>
                    <p>Morbi cursus porttitor enim lobortis molestie. Duis gravida turpis dui, eget bibendum magna
                        congue nec.</p>
                    <div class="footer-contact">
                        <a href="tel:2195550114">(219) 555-0114</a>
                        <span>or</span>
                        <a href="mailto:Proxy@gmail.com">Proxy@gmail.com</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>My Account</h3>
                    <div class="footer-links">
                        <a href="{{ route('dashboard') }}">My Account</a>
                        <a href="{{ route('dashboard') }}">Order History</a>
                        <a href="{{ route('userChart') }}">Shopping Cart</a>
                        <a href="#">Wishlist</a>
                        <a href="{{ route('dashboard') }}">Settings</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Helps</h3>
                    <div class="footer-links">
                        <a href="{{ route('contact') }}">Contact</a>
                        <a href="#">Faqs</a>
                        <a href="#">Terms & Condition</a>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Proxy</h3>
                    <div class="footer-links">
                        <a href="{{ route('about') }}">About</a>
                        <a href="{{ route('shop') }}">Shop</a>
                        <a href="#">Product</a>
                        <a href="#">Products Details</a>
                        <a href="#">Track Order</a>
                    </div>

                    <h3 style="margin-top: 30px; margin-bottom: 15px;">Download our Mobile App</h3>
                    <div class="app-buttons">
                        <a href="#" class="app-btn">
                            <i class="fa-brands fa-apple"></i>
                            <div>
                                <span>Download on the</span>
                                <span>App Store</span>
                            </div>
                        </a>
                        <a href="#" class="app-btn">
                            <i class="fa-brands fa-google-play"></i>
                            <div>
                                <span>Download on the</span>
                                <span>Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="social-icons">
                    <a href="#" class="social-icon active"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-pinterest-p"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
                <div>
                    ZyhCorner eCommerce © 2021. All Rights Reserved
                </div>
                <div class="payment-icons">
                    <div class="payment-icon"><i class="fa-brands fa-apple-pay"></i></div>
                    <div class="payment-icon"><i class="fa-brands fa-cc-visa"></i></div>
                    <div class="payment-icon"><i class="fa-brands fa-cc-discover"></i></div>
                    <div class="payment-icon"><i class="fa-brands fa-cc-mastercard"></i></div>
                    <div class="payment-icon" style="font-size: 10px; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-lock" style="font-size: 12px;"></i> Secure<br>Payment
                    </div>
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
                <div class="logo-text" style="font-size: 18px;">Zyh Corner</div>
            </div>
            <button class="close-mobile-menu" onclick="toggleMobileMenu(false)"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="mobile-nav-body">
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
                <a href="{{ route('userChart') }}" class="btn-checkout">Checkout</a>
                <a href="{{ route('userChart') }}" class="btn-view-cart">Go To Cart</a>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="product-modal-overlay" id="productModal">
        <div class="product-modal">
            <button class="close-modal" onclick="closeProductModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="product-modal-content">
                <div class="pm-gallery">
                    <div class="pm-thumbnails-container">
                        <button class="pm-thumb-nav"><i class="fa-solid fa-chevron-up"></i></button>
                        <div class="pm-thumbnails" id="pmThumbnails">
                            <!-- Thumbnails injected here -->
                        </div>
                        <button class="pm-thumb-nav"><i class="fa-solid fa-chevron-down"></i></button>
                    </div>
                    <div class="pm-main-image">
                        <img id="pmMainImage" src="" alt="Product">
                    </div>
                </div>
                <div class="pm-details">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px; flex-wrap: wrap;">
                        <h2 id="pmName" style="margin:0;">Product Name</h2>
                        <span class="pm-stock-badge" id="pmStock">In Stock</span>
                    </div>
                    <div class="pm-meta">
                        <span class="pm-sku">SKU: <span id="pmSku"
                                style="color: #1a1a1a; font-weight: 500;">2,51,594</span></span>
                    </div>
                    <div class="pm-price-area">
                        <span class="pm-old-price" id="pmOldPrice"
                            style="color: #999; text-decoration: line-through; font-size: 18px;"></span>
                        <span class="pm-new-price" id="pmNewPrice"
                            style="color: #00b207; font-size: 28px; font-weight: 600;"></span>
                        <span class="pm-discount-badge" id="pmDiscountBadge"
                            style="display:none; background: #fdf0ea; color: #ea4b48; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;"></span>
                    </div>
                    <div class="pm-desc" id="pmDesc"
                        style="margin-bottom: 25px; border-top: 1px solid #f2f2f2; padding-top: 20px;"></div>
                    <div class="pm-actions"
                        style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #f2f2f2;">
                        <div class="pm-qty"
                            style="border: 1.5px solid #e6e6e6; border-radius: 50px; padding: 4px; width: 140px; display: flex; align-items: center; justify-content: space-between;">
                            <button class="pm-qty-btn" onclick="updateModalQty(-1)"
                                style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #f2f2f2; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i
                                    class="fa-solid fa-minus"></i></button>
                            <input type="text" value="1" id="pmQtyInput" class="pm-qty-input"
                                style="width: 40px; border: none; text-align: center; font-weight: 600; font-size: 16px; outline: none;">
                            <button class="pm-qty-btn" onclick="updateModalQty(1)"
                                style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #f2f2f2; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i
                                    class="fa-solid fa-plus"></i></button>
                        </div>
                        <button class="pm-buy-btn" id="pmBuyBtn"
                            style="background: #00b207; color: white; border: none; border-radius: 50px; padding: 0 40px; height: 50px; font-weight: 600; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: 0.3s; flex: 1;">
                            Buy Now <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                        <button class="pm-cart-btn" id="pmCartBtn"
                            style="width: 50px; height: 50px; border-radius: 50%; border: none; background: #f2f8f4; color: #1a1a1a; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </button>
                    </div>
                    <div class="pm-info">
                        <p><strong>Category:</strong> <span id="pmCategory" style="color: #666;"></span></p>
                        <p><strong>Tag:</strong> <span id="pmTag" style="color: #666;"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.layoutConfig = {
            isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
            loginRoute: "{{ route('login') }}",
            checkoutRoute: "{{ route('checkout') }}",
            successMessage: "{{ session('success') }}"
        };
    </script>
</body>

</html>
