@extends('Layout.layout_pAdmin')

@section('title', 'Kasir Offline')

@section('content')
<div class="content-wrapper" style="position: relative; overflow-x: hidden;">
    <div class="d-flex justify-content-between mb-4">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-format-list-bulleted"></i>
            </span>Cassier
        </h3>
        <div class="d-flex align-items-center">
            <button class="btn btn-primary btn-sm px-4">List Orders</button>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
    <!-- Toolbar: Filter & Search, Cart -->
    <form method="GET" action="{{ route('pos.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                    <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter</span>
                </button>
                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 220px; border-radius: 8px; margin-top: 5px;">
                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Kategori</h6>
                    @foreach($kategoris as $kategori)
                    <div class="form-check mb-2">
                        <label class="form-check-label text-muted" style="cursor: pointer;">
                            <input type="checkbox" name="kategori[]" value="{{ $kategori->id_kategori }}" class="form-check-input" {{ is_array(request('kategori')) && in_array($kategori->id_kategori, request('kategori')) ? 'checked' : '' }}> {{ $kategori->nama_kategori }}
                        </label>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-gradient-primary w-100 mt-2">Terapkan Filter</button>
                </div>
            </div>
            <div class="input-group w-100 w-sm-auto" style="max-width: 250px; height: 32px;">
                <div class="input-group-prepend" style="height: 32px;">
                    <span class="input-group-text bg-transparent border-right-0" style="padding: 0 10px; height: 32px;"><i class="mdi mdi-magnify text-muted"></i></span>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-left-0" placeholder="Cari..." style="height: 32px; padding-left: 0;">
            </div>
        </div>
        <div class="d-flex align-items-center w-100 w-md-auto justify-content-end" style="cursor: pointer;" onclick="toggleCart()">
            <div class="text-end me-3 border-end pe-3">
                <small class="text-muted d-block" style="font-size: 11px;">Total Produk</small>
                <strong id="total-items">0</strong>
            </div>
            <div class="text-end me-3">
                <small class="text-muted d-block" style="font-size: 11px;">Total Harga</small>
                <strong id="total-price">Rp. 0</strong>
            </div>
            <i class="mdi mdi-shopping-outline text-primary" style="font-size: 32px;"></i>
        </div>
    </form>

    <!-- Product Grid -->
    <div class="row" id="product-grid">
        @forelse($products as $product)
        <div class="col-6 col-sm-6 col-md-4 col-lg-3 grid-margin stretch-card mb-3">
            <div class="card border-0 shadow-sm product-card w-100" style="border-radius: 12px; position: relative; overflow: hidden;">
                <div class="card-body p-0 d-flex flex-column">
                    <div class="image-wrapper w-100" style="position: relative; overflow: hidden; height: 160px;">
                        @if($product->foto_1)
                            <img src="{{ asset('storage/' . $product->foto_1) }}" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 bg-secondary d-flex align-items-center justify-content-center text-white">No Image</div>
                        @endif
                        <div class="product-overlay w-100 h-100" style="position: absolute; top:0; left:0; background: rgba(30,39,83,0.6); display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;">
                            <button class="btn btn-light btn-sm mb-2 px-3" onclick="addToCart({{ $product->id_produk }}, '{{ $product->nama_produk }}', {{ $product->harga }}, '{{ $product->foto_1 ? asset('storage/'.$product->foto_1) : '' }}')"><i class="mdi mdi-plus me-1"></i>Tambah</button>
                            <button class="btn btn-outline-light btn-sm px-3" onclick="removeFromCart({{ $product->id_produk }})"><i class="mdi mdi-minus me-1"></i>Kurang</button>
                        </div>
                    </div>
                    <div class="p-2 px-3 pb-3 flex-grow-1 d-flex flex-column">
                        <h6 class="text-muted mb-1" style="font-size: 13px;">{{ $product->nama_produk }}</h6>
                        <p class="mb-0 font-weight-bold" style="font-size: 14px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <i class="mdi mdi-cube-outline text-muted mb-2" style="font-size: 64px;"></i>
                <h5 class="text-muted font-weight-bold">Belum Ada Produk</h5>
                <p class="text-muted mb-0" style="font-size: 14px;">Tidak ada produk yang dapat ditampilkan.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-2 mb-4 gap-3">
        <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
        <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
            {{ $products->total() }} Produk
        </div>
    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Right Offcanvas Cart -->
<div id="cart-sidebar" class="bg-white shadow-lg" style="position: fixed; top: 0; right: -100%; max-width: 400px; width: 100%; height: 100vh; z-index: 1050; transition: right 0.3s ease; display: flex; flex-direction: column;">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold">Shopping Card (<span id="cart-count">0</span>)</h5>
        <button class="btn-close" onclick="toggleCart()"></button>
    </div>
    <div class="p-4 flex-grow-1 overflow-auto" id="cart-items">
        <!-- Items go here -->
    </div>
    <div class="p-4 border-top">
        <div class="d-flex justify-content-between mb-3">
            <span class="text-muted" style="font-size:14px;"><span id="cart-total-items-bottom">0</span> Product</span>
            <strong id="cart-total-price-bottom">Rp. 0</strong>
        </div>
        <button class="btn btn-gradient-primary w-100 mb-2" onclick="showCheckout()" style="border-radius: 8px;">Checkout</button>
        <button class="btn btn-outline-secondary w-100" onclick="toggleCart()" style="border-radius: 8px;">Kembali</button>
    </div>
</div>

<!-- Checkout View (Initially Hidden) -->
<div id="checkout-view" class="content-wrapper" style="display: none; position: absolute; top:0; left:0; width:100%; min-height:100vh; background: #F8F9FC; z-index: 1000; padding: 2.75rem 2.25rem;">
    <div class="d-flex align-items-center mb-4">
        <a href="#" class="text-decoration-none text-muted me-2" onclick="hideCheckout()"><i class="mdi mdi-arrow-left me-1"></i>Kembali</a>
    </div>
    <h3 class="page-title font-weight-bold text-dark mb-4" style="font-size: 22px;">Checkout</h3>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th style="font-size:12px; font-weight:500;" class="ps-4">PRODUCT</th>
                                    <th style="font-size:12px; font-weight:500;">PRICE</th>
                                    <th style="font-size:12px; font-weight:500;">QUANTITY</th>
                                    <th style="font-size:12px; font-weight:500;" class="pe-4">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody id="checkout-items">
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 text-center border-top">
                        <button class="btn btn-light btn-sm px-4" onclick="renderCheckout()" style="border-radius:20px; background:#f4f6f8;">Update Cart</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Payment</h5>
                    <div class="form-group mb-3">
                        <label class="text-muted mb-2" style="font-size:12px;">Customer Name (Optional)</label>
                        <input type="text" class="form-control border-0 shadow-sm" id="customer-name" placeholder="Walk-in Customer" style="border-radius: 8px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="text-muted mb-2" style="font-size:12px;">Select payment</label>
                        <select class="form-select border-0 shadow-sm" id="payment-method" style="border-radius: 8px;">
                            <option value="Cash">Cash</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                    
                    <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Cart Total</h5>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted" style="font-size:14px;">Subtotal:</span>
                        <strong id="checkout-subtotal">Rp. 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted" style="font-size:14px;">Shipping:</span>
                        <strong class="text-dark">Free</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted" style="font-size:14px;">Total:</span>
                        <strong id="checkout-total">Rp. 0</strong>
                    </div>
                    <button class="btn btn-gradient-primary w-100 mb-3" onclick="processCheckout()" style="border-radius: 8px;">Proses Checkout</button>
                    <button class="btn btn-outline-secondary w-100" onclick="hideCheckout()" style="border-radius: 8px;">Kembali ke Kasir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card .card-body {
    padding: 0 !important;
}
.product-card:hover .product-overlay {
    opacity: 1 !important;
}
#cart-sidebar.open {
    right: 0 !important;
}
.btn-qty {
    background: #f4f6f8;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-qty:hover {
    background: #e4e6e8;
}
</style>

<script>
    let cart = {};

    function toggleCart() {
        document.getElementById('cart-sidebar').classList.toggle('open');
    }

    function addToCart(id, name, price, image) {
        if (!cart[id]) {
            cart[id] = { name: name, price: price, quantity: 0, image: image };
        }
        cart[id].quantity += 1;
        updateCartUI();
    }

    function removeFromCart(id) {
        if (cart[id] && cart[id].quantity > 0) {
            cart[id].quantity -= 1;
            if (cart[id].quantity === 0) {
                delete cart[id];
            }
            updateCartUI();
        }
    }

    function updateCartUI() {
        let totalItems = 0;
        let totalPrice = 0;
        let html = '';
        
        for (let id in cart) {
            let item = cart[id];
            totalItems += item.quantity;
            totalPrice += (item.price * item.quantity);
            
            html += `
                <div class="d-flex mb-4 position-relative">
                    <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-1 text-muted" style="font-size:13px;">${item.name}</h6>
                        <div class="d-flex align-items-center">
                            <span class="text-muted" style="font-size: 12px;">${item.quantity} x</span> 
                            <strong class="ms-1" style="font-size: 13px;">Rp. ${item.price.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn-qty" onclick="removeFromCart(${id})"><i class="mdi mdi-minus"></i></button>
                        <span class="mx-2" style="font-size: 14px;">${item.quantity}</span>
                        <button class="btn-qty" onclick="addToCart(${id}, '${item.name}', ${item.price}, '${item.image}')"><i class="mdi mdi-plus"></i></button>
                        <button class="btn btn-sm text-muted p-1 ms-2" onclick="delete cart[${id}]; updateCartUI();" style="background:transparent; border:none;">
                            <i class="mdi mdi-close-circle-outline" style="font-size: 18px;"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('cart-items').innerHTML = html;
        document.getElementById('total-items').innerText = totalItems;
        document.getElementById('cart-count').innerText = totalItems;
        document.getElementById('cart-total-items-bottom').innerText = totalItems;
        
        let priceFormatted = 'Rp. ' + totalPrice.toLocaleString('id-ID');
        document.getElementById('total-price').innerText = priceFormatted;
        document.getElementById('cart-total-price-bottom').innerText = priceFormatted;
    }

    function showCheckout() {
        if(Object.keys(cart).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        document.getElementById('cart-sidebar').classList.remove('open');
        document.getElementById('checkout-view').style.display = 'block';
        renderCheckout();
    }

    function hideCheckout() {
        document.getElementById('checkout-view').style.display = 'none';
        updateCartUI(); // Ensure sidebar reflects any changes
    }

    function renderCheckout() {
        let html = '';
        let totalPrice = 0;
        
        for (let id in cart) {
            let item = cart[id];
            let subtotal = item.price * item.quantity;
            totalPrice += subtotal;
            html += `
                <tr class="border-bottom">
                    <td class="align-middle py-4 ps-4">
                        <div class="d-flex align-items-center">
                            <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 70px; height: 70px; object-fit: cover;">
                            <span class="ms-3 text-muted" style="font-size:14px;">${item.name}</span>
                        </div>
                    </td>
                    <td class="align-middle font-weight-bold" style="font-size:14px;">Rp. ${item.price.toLocaleString('id-ID')}</td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center rounded-pill border px-2 py-1" style="width: 100px; background: #f8f9fa;">
                            <button class="btn p-0 text-muted" onclick="removeFromCart(${id}); renderCheckout();" style="border:none; background:transparent;"><i class="mdi mdi-minus"></i></button>
                            <input type="text" class="form-control text-center border-0 p-0 mx-2 bg-transparent" value="${item.quantity}" readonly style="width: 30px; font-size: 14px;">
                            <button class="btn p-0 text-muted" onclick="addToCart(${id}, '${item.name}', ${item.price}, '${item.image}'); renderCheckout();" style="border:none; background:transparent;"><i class="mdi mdi-plus"></i></button>
                        </div>
                    </td>
                    <td class="align-middle font-weight-bold pe-4" style="font-size:14px;">
                        <div class="d-flex align-items-center justify-content-between">
                            Rp. ${subtotal.toLocaleString('id-ID')}
                            <button class="btn btn-sm text-muted p-0" onclick="delete cart[${id}]; renderCheckout();" style="border:none; background:transparent;">
                                <i class="mdi mdi-close-circle-outline" style="font-size:20px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        document.getElementById('checkout-items').innerHTML = html;
        let priceFormatted = 'Rp. ' + totalPrice.toLocaleString('id-ID');
        document.getElementById('checkout-subtotal').innerText = priceFormatted;
        document.getElementById('checkout-total').innerText = priceFormatted;
    }

    function processCheckout() {
        if(Object.keys(cart).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        let paymentMethod = document.getElementById('payment-method').value;
        let customerName = document.getElementById('customer-name').value || 'Walk-in Customer';
        
        // Show loading state with SweetAlert
        Swal.fire({
            title: 'Memproses Transaksi',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        let payload = {
            _token: '{{ csrf_token() }}',
            items: cart,
            payment_method: paymentMethod,
            customer_name: customerName
        };
        
        fetch('{{ route("pos.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Checkout Berhasil!',
                    text: data.message || 'Transaksi telah berhasil disimpan.',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    cart = {};
                    // Reset customer name input
                    document.getElementById('customer-name').value = '';
                    updateCartUI();
                    hideCheckout();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Checkout Gagal!',
                    text: data.message || 'Terjadi kesalahan saat memproses transaksi.',
                    confirmButtonColor: '#ea4b48'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: error.message || 'Gagal terhubung ke server.',
                confirmButtonColor: '#ea4b48'
            });
        });
    }
</script>

@endsection
