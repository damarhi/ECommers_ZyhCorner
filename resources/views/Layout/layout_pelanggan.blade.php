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
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container top-header">
            <div class="logo">
                <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                <div class="logo-text">Zyh Corner</div>
            </div>

            <div class="search-bar">
                <span style="padding-left: 15px; color: #999;"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" placeholder="Search">
                <button>Search</button>
            </div>

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
                    <div id="profileDropdownBtn" style="color: inherit; cursor: pointer; display: flex; align-items: center;">
                        <i class="fa-regular fa-user action-icon"></i>
                    </div>
                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        @auth
                            <div class="profile-dropdown-item profile-dropdown-name">{{ Auth::user()->nama ?? 'User' }}</div>
                            <a href="#" class="profile-dropdown-item">Settings</a>
                            <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="button" onclick="confirmLogout()" class="profile-dropdown-item" style="width: 100%; text-align: left; background: none; border: none; font-family: inherit; cursor: pointer;">Logout</button>
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
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home <i class="fa-solid fa-chevron-down"></i></a>
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">Shop <i class="fa-solid fa-chevron-down"></i></a>
                <a href="#">Pages <i class="fa-solid fa-chevron-down"></i></a>
                <a href="#">Blog <i class="fa-solid fa-chevron-down"></i></a>
                <a href="#">About Us</a>
                <a href="#">Contact Us</a>
            </div>
            <div class="nav-phone">
                <i class="fa-solid fa-phone-volume"></i>
                <span>(+62) 8123456789</span>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb Area -->
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

    @yield('content')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-col-1">
                    <div class="logo" style="color: white;">
                        <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                        <div class="logo-text">Zyh Corner</div>
                    </div>
                    <p>Morbi cursus porttitor enim lobortis molestie. Duis gravida turpis dui, eget bibendum magna congue nec.</p>
                    <div class="footer-contact">
                        <a href="tel:2195550114">(219) 555-0114</a>
                        <span>or</span>
                        <a href="mailto:Proxy@gmail.com">Proxy@gmail.com</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>My Account</h3>
                    <div class="footer-links">
                        <a href="#">My Account</a>
                        <a href="#">Order History</a>
                        <a href="#">Shoping Cart</a>
                        <a href="#">Wishlist</a>
                        <a href="#">Settings</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Helps</h3>
                    <div class="footer-links">
                        <a href="#">Contact</a>
                        <a href="#">Faqs</a>
                        <a href="#">Terms & Condition</a>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Proxy</h3>
                    <div class="footer-links">
                        <a href="#">About</a>
                        <a href="#">Shop</a>
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
                        <div class="pm-rating">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                            <span style="margin-left: 5px; color: #666;">4 Review</span>
                        </div>
                        <span class="pm-meta-dot">•</span>
                        <span class="pm-sku">SKU: <span id="pmSku" style="color: #1a1a1a; font-weight: 500;">2,51,594</span></span>
                    </div>
                    <div class="pm-price-area">
                        <span class="pm-old-price" id="pmOldPrice" style="color: #999; text-decoration: line-through; font-size: 18px;"></span>
                        <span class="pm-new-price" id="pmNewPrice" style="color: #00b207; font-size: 28px; font-weight: 600;"></span>
                        <span class="pm-discount-badge" id="pmDiscountBadge" style="display:none; background: #fdf0ea; color: #ea4b48; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;"></span>
                    </div>
                    <div class="pm-desc" id="pmDesc" style="margin-bottom: 25px; border-top: 1px solid #f2f2f2; padding-top: 20px;"></div>
                    <div class="pm-actions" style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #f2f2f2;">
                        <div class="pm-qty" style="border: 1.5px solid #e6e6e6; border-radius: 50px; padding: 4px; width: 140px; display: flex; align-items: center; justify-content: space-between;">
                            <button class="pm-qty-btn" onclick="updateModalQty(-1)" style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #f2f2f2; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-minus"></i></button>
                            <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" style="width: 40px; border: none; text-align: center; font-weight: 600; font-size: 16px; outline: none;">
                            <button class="pm-qty-btn" onclick="updateModalQty(1)" style="width: 32px; height: 32px; border-radius: 50%; border: none; background: #f2f2f2; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <button class="pm-buy-btn" id="pmBuyBtn" style="background: #00b207; color: white; border: none; border-radius: 50px; padding: 0 40px; height: 50px; font-weight: 600; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: 0.3s; flex: 1;">
                            Buy Now <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                        <button class="pm-cart-btn" id="pmCartBtn" style="width: 50px; height: 50px; border-radius: 50%; border: none; background: #f2f8f4; color: #1a1a1a; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s;">
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
        window.isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        function openProductModal(btn) {
            const data = btn.dataset;
            
            document.getElementById('pmName').textContent = data.name;
            document.getElementById('pmQtyInput').value = 1;
            
            let price = parseInt(data.price);
            document.getElementById('pmNewPrice').textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price);
            
            if (data.sale === '1') {
                document.getElementById('pmOldPrice').style.display = 'inline-block';
                document.getElementById('pmDiscountBadge').style.display = 'inline-block';
                document.getElementById('pmOldPrice').textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price * 2);
                document.getElementById('pmDiscountBadge').textContent = '50% Off';
            } else {
                document.getElementById('pmOldPrice').style.display = 'none';
                document.getElementById('pmDiscountBadge').style.display = 'none';
            }
            
            if(data.stock == '0') {
                document.getElementById('pmStock').textContent = 'Out of Stock';
                document.getElementById('pmStock').className = 'pm-stock-badge out';
            } else {
                document.getElementById('pmStock').textContent = 'In Stock';
                document.getElementById('pmStock').className = 'pm-stock-badge';
            }
            
            document.getElementById('pmDesc').textContent = data.desc || 'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla nibh diam, blandit vel consequat nec, ultrices et ipsum. Nulla varius magna a consequat pulvinar.';
            document.getElementById('pmCategory').textContent = data.cat;
            document.getElementById('pmTag').textContent = data.cat + ', ' + data.name.split(' ')[0] + ', Cream, Chocolate';
            document.getElementById('pmSku').textContent = 'ZYH-' + data.id.padStart(4, '0');
            
            // Set up Add to Cart and Buy Now buttons
            document.getElementById('pmCartBtn').onclick = function() {
                addToCart({
                    id: data.id,
                    name: data.name,
                    price: price,
                    img: data.f1 || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop',
                    qty: parseInt(document.getElementById('pmQtyInput').value)
                });
                closeProductModal();
            };

            document.getElementById('pmBuyBtn').onclick = function() {
                if (!window.isLoggedIn) {
                    closeProductModal();
                    window.location.href = "{{ route('login') }}";
                    return;
                }
                addToCart({
                    id: data.id,
                    name: data.name,
                    price: price,
                    img: data.f1 || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop',
                    qty: parseInt(document.getElementById('pmQtyInput').value)
                });
                window.location.href = "{{ route('checkout') }}"; 
            };
            
            // Handle images
            const thumbContainer = document.getElementById('pmThumbnails');
            thumbContainer.innerHTML = '';
            
            const images = [];
            if(data.f1) images.push(data.f1);
            if(data.f2) images.push(data.f2);
            if(data.f3) images.push(data.f3);
            if(data.f4) images.push(data.f4);
            
            if(images.length === 0) {
                images.push('https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop');
            }
            
            document.getElementById('pmMainImage').src = images[0];
            
            images.forEach((imgSrc, index) => {
                const div = document.createElement('div');
                div.className = 'pm-thumb' + (index === 0 ? ' active' : '');
                div.innerHTML = '<img src="' + imgSrc + '" alt="thumb">';
                div.onclick = function() {
                    document.querySelectorAll('.pm-thumb').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('pmMainImage').src = imgSrc;
                };
                thumbContainer.appendChild(div);
            });
            
            document.getElementById('productModal').style.display = 'flex';
        }

        function updateModalQty(delta) {
            const input = document.getElementById('pmQtyInput');
            let val = parseInt(input.value) + delta;
            if(val < 1) val = 1;
            input.value = val;
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        // Close modal on click outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeProductModal();
            }
        }

        // --- Shopping Cart Logic ---
        let cart = JSON.parse(localStorage.getItem('zyh_cart')) || [];

        function toggleCart(show) {
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartOverlay');
            if(show) {
                overlay.style.display = 'block';
                setTimeout(() => {
                    overlay.style.opacity = '1';
                    sidebar.classList.add('open');
                }, 10);
                renderCart();
            } else {
                sidebar.classList.remove('open');
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        }

        function addToCart(product) {
            const existing = cart.find(item => item.id === product.id);
            if(existing) {
                existing.qty += (product.qty || 1);
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    img: product.img,
                    qty: product.qty || 1
                });
            }
            saveCart();
            toggleCart(true);
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if(item) {
                item.qty += delta;
                if(item.qty < 1) {
                    removeFromCart(id);
                } else {
                    saveCart();
                    renderCart();
                }
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            saveCart();
            renderCart();
        }

        function saveCart() {
            localStorage.setItem('zyh_cart', JSON.stringify(cart));
            updateHeaderCart();
        }

        function updateHeaderCart() {
            const count = cart.reduce((total, item) => total + item.qty, 0);
            const subtotal = cart.reduce((total, item) => total + (item.price * item.qty), 0);
            
            document.getElementById('cartBadge').textContent = count;
            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartSubtotalHeader').textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
            document.getElementById('cartSubtotal').textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
        }

        function renderCart() {
            const list = document.getElementById('cartItemsList');
            if(cart.length === 0) {
                list.innerHTML = `
                    <div class="empty-cart-msg">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <p>Your cart is empty</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-img">
                        <img src="${item.img}" alt="${item.name}">
                    </div>
                    <div class="cart-item-info">
                        <span class="cart-item-name">${item.name}</span>
                        <div class="cart-item-price">
                            ${item.qty} x Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}
                        </div>
                        <div class="cart-item-qty-wrap">
                            <button class="cart-qty-btn" onclick="updateQty('${item.id}', -1)"><i class="fa-solid fa-minus"></i></button>
                            <span>${item.qty}</span>
                            <button class="cart-qty-btn" onclick="updateQty('${item.id}', 1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="cart-item-remove" onclick="removeFromCart('${item.id}')">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                </div>
            `).join('');
            
            updateHeaderCart();
        }

        // Initial header update
        updateHeaderCart();

        // --- Profile Dropdown Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const profileWrapper = document.getElementById('profileDropdownWrapper');
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileMenu = document.getElementById('profileDropdownMenu');
            
            if (profileWrapper && profileBtn && profileMenu) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const show = profileMenu.classList.toggle('show');
                    if (show) {
                        profileWrapper.classList.add('active');
                    } else {
                        profileWrapper.classList.remove('active');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('show');
                        profileWrapper.classList.remove('active');
                    }
                });
            }
        });

        // --- SweetAlert Success Session Handler ---
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: {
                    popup: 'rounded-4'
                }
            });
        @endif

        // --- Confirm Logout Alert ---
        window.confirmLogout = function() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari akun Anda?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00b207',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        };
    </script>
</body>
</html>
