@extends('Layout.layout_pelanggan')

@section('title', 'Shopping Cart - Zyh Corner')

@section('breadcrumb')
    <span>Shopping Cart</span>
@endsection

@section('content')
<style>
    /* Cart Layout */
    .cart-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 15px 80px;
    }
    .cart-title {
        text-align: center;
        font-size: 32px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 40px;
    }

    .cart-grid {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 40px;
        align-items: start;
    }

    /* Left Table */
    .cart-table-wrapper {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        overflow: hidden;
    }
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .cart-table th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #808080;
        padding: 20px;
        border-bottom: 1px solid #e6e6e6;
    }
    .cart-table td {
        padding: 20px;
        border-bottom: 1px solid #f2f2f2;
        vertical-align: middle;
    }
    .cart-table tr:last-child td {
        border-bottom: none;
    }

    /* Product Cell */
    .cart-product-cell {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .cart-product-img {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        overflow: hidden;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        flex-shrink: 0;
        border: 1px solid #f2f2f2;
    }
    .cart-product-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .cart-product-name {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }

    /* Price and Subtotal */
    .cart-price-cell {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 500;
    }
    .cart-subtotal-cell {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 600;
    }

    /* Quantity Box */
    .cart-qty-box {
        display: flex;
        align-items: center;
        border: 1.5px solid #e6e6e6;
        border-radius: 50px;
        background: #fdfdfd;
        width: 110px;
        height: 38px;
        padding: 2px;
        justify-content: space-between;
    }
    .cart-qty-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: #f2f2f2;
        color: #333;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .cart-qty-btn:hover {
        background: #e6e6e6;
    }
    .cart-qty-input {
        width: 35px;
        border: none;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        outline: none;
        background: transparent;
    }

    /* Remove Button */
    .cart-remove-btn {
        background: none;
        border: none;
        color: #999;
        font-size: 18px;
        cursor: pointer;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1px solid #e6e6e6;
    }
    .cart-remove-btn:hover {
        color: #ea4b48;
        border-color: #ea4b48;
        background: #fdf0ea;
    }

    /* Table Actions Bottom */
    .cart-table-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: white;
        border-top: 1px solid #e6e6e6;
    }
    .btn-action-gray {
        background: #f2f2f2;
        color: #4d4d4d;
        border: none;
        border-radius: 50px;
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }
    .btn-action-gray:hover {
        background: #e6e6e6;
        color: #1a1a1a;
    }

    /* Right Card: Cart Total */
    .cart-total-card {
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        background: white;
        padding: 24px;
    }
    .cart-total-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
    }
    .cart-total-row {
        display: flex;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #f2f2f2;
        font-size: 14px;
        color: #4d4d4d;
    }
    .cart-total-row:last-of-type {
        border-bottom: none;
        padding-top: 20px;
        padding-bottom: 25px;
    }
    .cart-total-row span:last-child {
        color: #1a1a1a;
        font-weight: 600;
    }
    .cart-total-row.grand-total {
        font-size: 16px;
        color: #1a1a1a;
    }
    .cart-total-row.grand-total span:last-child {
        font-size: 18px;
        font-weight: 700;
    }

    .btn-checkout-green {
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
        text-decoration: none;
        transition: background 0.3s;
    }
    .btn-checkout-green:hover {
        background-color: #009906;
    }

    /* Empty Cart State */
    .empty-cart-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
    }
    .empty-cart-state i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }
    .empty-cart-state p {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }

    @media (max-width: 991px) {
        .cart-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }

    @media (max-width: 576px) {
        .cart-container {
            padding: 30px 15px 40px;
        }
        .cart-title {
            font-size: 24px;
            margin-bottom: 25px;
        }
        .cart-table th, .cart-table td {
            padding: 10px;
        }
        .cart-product-img {
            width: 60px;
            height: 60px;
        }
        .cart-product-name {
            font-size: 13px;
            max-width: 120px;
        }
        .cart-qty-box {
            width: 90px;
            height: 32px;
        }
        .cart-qty-btn {
            width: 26px;
            height: 26px;
        }
        .cart-qty-input {
            width: 25px;
            font-size: 12px;
        }
        .cart-table-actions {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }
        .btn-action-gray {
            width: 100%;
        }
    }
</style>


<div class="cart-container">
    <h1 class="cart-title">My Shopping Cart</h1>

    <div id="cartPageContent">
        <!-- Rendered dynamically by JS -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        renderCartPage();
    });

    function renderCartPage() {
        const container = document.getElementById('cartPageContent');
        
        // Read cart data from localStorage
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        
        if (localCart.length === 0) {
            container.innerHTML = `
                <div class="empty-cart-state">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <h2>Keranjang Belanja Anda Kosong</h2>
                    <p>Silakan kembali ke toko untuk menambahkan produk-produk lezat kami ke dalam keranjang.</p>
                    <a href="{{ route('shop') }}" class="btn-checkout-green" style="max-width: 250px; margin: 0 auto;">Mulai Belanja</a>
                </div>
            `;
            return;
        }

        let tableRows = localCart.map(item => {
            let priceFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(item.price);
            let subtotalFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(item.price * item.qty);
            
            return `
                <tr>
                    <td>
                        <div class="cart-product-cell">
                            <div class="cart-product-img">
                                <img src="${item.img}" alt="${item.name}">
                            </div>
                            <span class="cart-product-name" title="${item.name}">${item.name}</span>
                        </div>
                    </td>
                    <td class="cart-price-cell">${priceFormatted}</td>
                    <td>
                        <div class="cart-qty-box">
                            <button type="button" class="cart-qty-btn" onclick="updatePageQty('${item.id}', -1)"><i class="fa-solid fa-minus"></i></button>
                            <input type="text" class="cart-qty-input" value="${item.qty}" readonly>
                            <button type="button" class="cart-qty-btn" onclick="updatePageQty('${item.id}', 1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </td>
                    <td class="cart-subtotal-cell">${subtotalFormatted}</td>
                    <td>
                        <button type="button" class="cart-remove-btn" onclick="removePageItem('${item.id}')" title="Hapus Produk">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        let totalAmount = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        let totalAmountFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(totalAmount);

        container.innerHTML = `
            <div class="cart-grid">
                <!-- Table Card -->
                <div class="cart-table-wrapper">
                    <div style="overflow-x: auto;">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Product</th>
                                    <th style="width: 15%;">Price</th>
                                    <th style="width: 20%;">Quantity</th>
                                    <th style="width: 15%;">Subtotal</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-table-actions">
                        <a href="{{ route('shop') }}" class="btn-action-gray">Return to shop</a>
                        <button type="button" class="btn-action-gray" style="background: #f2f2f2; color: #1a1a1a;" onclick="renderCartPage()">Update Cart</button>
                    </div>
                </div>

                <!-- Summary Total Card -->
                <div class="cart-total-card">
                    <h3 class="cart-total-title">Cart Total</h3>
                    
                    <div class="cart-total-row">
                        <span>Subtotal:</span>
                        <span>${totalAmountFormatted}</span>
                    </div>
                    
                    <div class="cart-total-row">
                        <span>Shipping:</span>
                        <span style="color: #2c2c2c; font-weight: 500;">Free</span>
                    </div>
                    
                    <div class="cart-total-row grand-total">
                        <span>Total:</span>
                        <span>${totalAmountFormatted}</span>
                    </div>
                    
                    <button type="button" class="btn-checkout-green" onclick="processCheckout()">Proceed to checkout</button>
                </div>
            </div>
        `;
    }

    // Update quantity from cart page
    window.updatePageQty = function(id, delta) {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const item = localCart.find(i => i.id === id);
        if(item) {
            item.qty += delta;
            if(item.qty < 1) {
                removePageItem(id);
                return;
            }
            localStorage.setItem('zyh_cart', JSON.stringify(localCart));
            renderCartPage();
            
            // Sync with global header cart
            if (typeof updateHeaderCart === 'function') {
                cart = localCart; // update layout's cart variable reference
                updateHeaderCart();
            }
        }
    };

    // Remove item from cart page
    window.removePageItem = function(id) {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        localCart = localCart.filter(item => item.id !== id);
        localStorage.setItem('zyh_cart', JSON.stringify(localCart));
        renderCartPage();
        
        // Sync with global header cart
        if (typeof updateHeaderCart === 'function') {
            cart = localCart; // update layout's cart variable reference
            updateHeaderCart();
        }
    };

    // Process checkout action
    window.processCheckout = function() {
        if (!window.isLoggedIn) {
            window.location.href = "{{ route('login') }}";
            return;
        }
        window.location.href = "{{ route('checkout') }}";
    };
</script>
@endsection
