@extends('Layout.layout_pAdmin')

@section('title', 'Kasir Offline')

@section('content')
<div class="content-wrapper" style="position: relative; overflow-x: hidden;">
    <div class="d-flex justify-content-between mb-4">
            <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-cash-register"></i>
            </span>
            <span class="text-nowrap">Kasir Offline</span>
        </h3>
        <div class="d-flex align-items-center">
            <a href="{{ route('all-sales.index') }}" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; font-weight: 500;" title="Lihat riwayat seluruh transaksi penjualan">
                <i class="mdi mdi-history me-1" style="font-size: 16px;"></i> Riwayat Penjualan
            </a>
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
            <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama / kode produk..."
                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
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
        @php
            $isOutOfStock = ($product->stok <= 0);
            $finalPrice = $product->harga_diskon ?? $product->harga;
            $hasDiscount = ($product->diskonAktif !== null);
            $productImg = $product->foto_1 ? asset('storage/' . $product->foto_1) : asset('image/logo_circle.png');
        @endphp
        <div class="col-6 col-sm-6 col-md-4 col-lg-3 grid-margin stretch-card mb-3 pos-product-item" data-search="{{ strtolower($product->nama_produk . ' ' . ($product->kategori->nama_kategori ?? '') . ' ' . $product->id_produk . ' ' . $finalPrice) }}">
            <div class="card border-0 shadow-sm product-card w-100 {{ $isOutOfStock ? 'out-of-stock-card' : '' }}" style="border-radius: 12px; position: relative; overflow: hidden;">
                <!-- Stock Status Badge (Top Left) -->
                <div class="position-absolute top-0 start-0 p-2" style="z-index: 5;">
                    @if($isOutOfStock)
                        <span class="badge bg-danger text-white shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; letter-spacing: 0.2px;">
                            <i class="mdi mdi-alert-circle-outline me-1"></i>Stok Habis
                        </span>
                    @elseif($product->stok <= 5)
                        <span class="badge bg-warning text-dark shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; letter-spacing: 0.2px;">
                            <i class="mdi mdi-clock-alert-outline me-1"></i>Sisa {{ $product->stok }}
                        </span>
                    @else
                        <span class="badge bg-success text-white shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 500; letter-spacing: 0.2px;">
                            <i class="mdi mdi-check-circle-outline me-1"></i>Tersedia: {{ $product->stok }}
                        </span>
                    @endif
                </div>
                <!-- Quick Restock Button (Top Right) -->
                <div class="position-absolute top-0 end-0 p-2" style="z-index: 5;">
                    <button type="button" 
                            class="btn btn-sm btn-white text-primary shadow-sm rounded-pill d-inline-flex align-items-center px-2 py-1 btn-quick-stock" 
                            onclick="openAddStockModal({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $product->stok }}, '{{ $product->tanggal_expired ?? '' }}', '{{ $productImg }}')"
                            title="Tambah Stok Produk"
                            style="font-size: 11px; font-weight: 600; background: rgba(255,255,255,0.95); border: 1px solid rgba(0,0,0,0.08); gap: 3px; cursor: pointer;">
                        <i class="mdi mdi-plus-box-outline text-success" style="font-size: 14px;"></i> + Stok
                    </button>
                </div>

                <div class="card-body p-0 d-flex flex-column">
                    <div class="image-wrapper w-100" style="position: relative; overflow: hidden; height: 160px; background: #f8f9fa;">
                        @if($product->foto_1)
                            <img src="{{ asset('storage/' . $product->foto_1) }}" class="w-100 h-100 {{ $isOutOfStock ? 'grayscale-img' : '' }}" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 bg-secondary d-flex align-items-center justify-content-center text-white">No Image</div>
                        @endif
                        <div class="product-overlay w-100 h-100" style="position: absolute; top:0; left:0; background: rgba(30,39,83,0.65); display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;">
                            @if($isOutOfStock)
                                <button class="btn btn-secondary btn-sm px-3 mb-2 disabled" style="cursor: not-allowed; opacity: 0.9;" disabled>
                                    <i class="mdi mdi-cancel me-1"></i>Stok Habis
                                </button>
                                <button type="button" class="btn btn-success btn-sm px-3 shadow-sm font-weight-500" onclick="openAddStockModal({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $product->stok }}, '{{ $product->tanggal_expired ?? '' }}', '{{ $productImg }}')">
                                    <i class="mdi mdi-package-variant-plus me-1"></i>Restock
                                </button>
                            @else
                                <button class="btn btn-light btn-sm mb-2 px-3 shadow-sm font-weight-500" onclick="addToCart({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $finalPrice }}, '{{ $productImg }}', {{ $product->stok }}, {{ $product->harga }})">
                                    <i class="mdi mdi-plus me-1 text-primary"></i>Tambah
                                </button>
                                <button class="btn btn-outline-light btn-sm px-3" onclick="removeFromCart({{ $product->id_produk }})">
                                    <i class="mdi mdi-minus me-1"></i>Kurang
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="p-2 px-3 pb-3 flex-grow-1 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="text-dark mb-1 text-truncate" title="{{ $product->nama_produk }}" style="font-size: 13px; font-weight: 600;">{{ $product->nama_produk }}</h6>
                            <small class="text-muted d-block mb-1" style="font-size: 11px;">{{ $product->kategori->nama_kategori ?? 'Umum' }}</small>
                        </div>
                        <div class="mt-1">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="font-weight-bold text-primary" style="font-size: 14px;">Rp. {{ number_format($finalPrice, 0, ',', '.') }}</span>
                                @if($hasDiscount)
                                    <small class="text-muted text-decoration-line-through" style="font-size: 11px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</small>
                                    <span class="badge d-inline-flex align-items-center" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; background-color: #fee2e2; color: #dc2626 !important; border: 1px solid #fca5a5;">
                                        <i class="mdi mdi-tag-outline me-1" style="font-size: 11px;"></i>{{ $product->diskon_badge ?? 'Diskon' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <i class="mdi mdi-cube-outline text-muted mb-2" style="font-size: 64px;"></i>
                <h5 class="text-muted font-weight-bold">Belum Ada Produk</h5>
                <p class="text-muted mb-0" style="font-size: 14px;">Tidak ada produk showcase yang dapat ditampilkan.</p>
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
</div> <!-- /.content-wrapper -->
<!-- Modal Tambah Stok Produk -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle" style="width: 34px; height: 34px; background-color: #dcfce7; color: #16a34a !important;">
                        <i class="mdi mdi-package-variant-plus" style="font-size: 18px;"></i>
                    </span>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addStockModalLabel" style="font-size: 15px;">
                            Tambah Stok Produk
                        </h6>
                        <small class="text-muted" style="font-size: 11px;">Update stok kasir offline secara instan.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <form id="addStockForm" onsubmit="submitAddStock(event)">
                @csrf
                <input type="hidden" id="stock_product_id" name="product_id">
                <div class="modal-body p-3 p-sm-4 bg-white">
                    <!-- Product Info Header -->
                    <div class="d-flex align-items-center rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px;">
                        <img id="stock_product_img" src="" alt="Produk" class="rounded me-3 shadow-sm border" style="width: 46px; height: 46px; object-fit: cover;">
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 id="stock_product_name" class="text-dark font-weight-bold mb-1 text-truncate" style="font-size: 13px;">-</h6>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="text-muted" style="font-size: 11px;">Stok Saat Ini:</span>
                                <span id="stock_current_badge" class="badge badge-soft-dark" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">0 Pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Stock Input -->
                    <div class="mb-3">
                        <label for="additional_stock" class="form-label text-dark font-weight-500 mb-1" style="font-size: 12px;">
                            Jumlah Tambahan Stok <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary font-weight-bold" style="border-radius: 6px 0 0 6px; font-size: 14px;">+</span>
                            <input type="number" class="form-control font-weight-bold text-dark" id="additional_stock" name="additional_stock" min="1" max="99999" value="10" required style="font-size: 14px;" oninput="calculateNewStock()">
                            <span class="input-group-text bg-light text-muted" style="border-radius: 0 6px 6px 0; font-size: 12px;">Pcs</span>
                        </div>
                        <!-- Quick Add Pills -->
                        <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                            <small class="text-muted me-1" style="font-size: 11px;">Tambah Cepat:</small>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="border-radius: 4px; font-size: 10px;" onclick="setQuickStock(5)">+5</button>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="border-radius: 4px; font-size: 10px;" onclick="setQuickStock(10)">+10</button>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="border-radius: 4px; font-size: 10px;" onclick="setQuickStock(20)">+20</button>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="border-radius: 4px; font-size: 10px;" onclick="setQuickStock(50)">+50</button>
                        </div>
                    </div>

                    <!-- Calculated New Stock Box -->
                    <div class="rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 12px;">
                        <span class="text-success font-weight-500 d-flex align-items-center" style="font-size: 11px;">
                            <i class="mdi mdi-calculator me-1" style="font-size: 14px;"></i> Estimasi Stok Baru:
                        </span>
                        <strong id="estimated_new_stock" class="text-success font-weight-bold" style="font-size: 13px;">10 Pcs</strong>
                    </div>

                    <!-- Optional Expiry Date -->
                    <div class="mb-1">
                        <label for="stock_tanggal_expired" class="form-label text-dark font-weight-500 mb-1" style="font-size: 12px;">
                            Perbarui Tanggal Kadaluarsa <small class="text-muted fw-normal">(Opsional)</small>
                        </label>
                        <input type="date" class="form-control" id="stock_tanggal_expired" name="tanggal_expired" style="font-size: 12px; border-radius: 6px;">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Kosongkan jika tanggal kadaluarsa tidak berubah.</small>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                    <button type="submit" id="btn-submit-stock" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                        <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.product-card .card-body {
    padding: 0 !important;
}
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
}
.product-card:hover .product-overlay {
    opacity: 1 !important;
}
.out-of-stock-card {
    opacity: 0.72;
    border: 1px dashed #dc3545 !important;
    background: #fdfdfd;
}
.grayscale-img {
    filter: grayscale(85%);
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
.btn-quick-stock:hover {
    background: #ffffff !important;
    transform: scale(1.06);
    box-shadow: 0 3px 8px rgba(0,0,0,0.12) !important;
}
</style>

<script>
    let currentStockModalValue = 0;
    let addStockModalInstance = null;

    function openAddStockModal(id, name, currentStock, expiryDate, image) {
        document.getElementById('stock_product_id').value = id;
        document.getElementById('stock_product_name').innerText = name;
        document.getElementById('stock_product_img').src = image || '{{ asset("image/logo_circle.png") }}';
        
        currentStockModalValue = parseInt(currentStock) || 0;
        let badgeEl = document.getElementById('stock_current_badge');
        if (currentStockModalValue <= 0) {
            badgeEl.className = 'badge bg-danger';
            badgeEl.innerText = '0 Pcs (Habis)';
        } else {
            badgeEl.className = 'badge bg-success';
            badgeEl.innerText = currentStockModalValue + ' Pcs';
        }

        document.getElementById('additional_stock').value = 10;
        document.getElementById('stock_tanggal_expired').value = expiryDate ? expiryDate.substring(0, 10) : '';
        calculateNewStock();

        if (!addStockModalInstance) {
            addStockModalInstance = new bootstrap.Modal(document.getElementById('addStockModal'));
        }
        addStockModalInstance.show();
    }

    function setQuickStock(val) {
        document.getElementById('additional_stock').value = val;
        calculateNewStock();
    }

    function calculateNewStock() {
        let addVal = parseInt(document.getElementById('additional_stock').value) || 0;
        let newTotal = Math.max(0, currentStockModalValue + addVal);
        document.getElementById('estimated_new_stock').innerText = newTotal + ' Pcs';
    }

    function submitAddStock(event) {
        event.preventDefault();
        let productId = document.getElementById('stock_product_id').value;
        let additionalStock = parseInt(document.getElementById('additional_stock').value) || 0;
        let expiryDate = document.getElementById('stock_tanggal_expired').value;
        let submitBtn = document.getElementById('btn-submit-stock');

        if (additionalStock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Jumlah Tidak Valid',
                text: 'Jumlah tambahan stok minimal 1 item.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';

        fetch('/admin/pos/products/' + productId + '/add-stock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                additional_stock: additionalStock,
                tanggal_expired: expiryDate || null
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Simpan Stok';
            
            if (data.success) {
                if (addStockModalInstance) {
                    addStockModalInstance.hide();
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Stok Berhasil Ditambahkan!',
                    text: data.message || 'Stok produk berhasil diperbarui.',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menambah Stok',
                    text: data.message || 'Terjadi kesalahan pada server.',
                    confirmButtonColor: '#ea4b48'
                });
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Simpan Stok';
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal terhubung ke server.',
                confirmButtonColor: '#ea4b48'
            });
        });
    }

    let cart = {};
    function saveCartToStorage() {
        try {
            localStorage.setItem('zyh_pos_cart', JSON.stringify(cart));
        } catch (e) {
            console.warn('LocalStorage error:', e);
        }
    }

    function loadCartFromStorage() {
        try {
            const saved = localStorage.getItem('zyh_pos_cart');
            if (saved) {
                cart = JSON.parse(saved) || {};
            }
        } catch (e) {
            cart = {};
        }
    }

    function toggleCart() {
        document.getElementById('cart-sidebar').classList.toggle('open');
    }

    function addToCart(id, name, price, image, maxStock, originalPrice) {
        if (maxStock !== undefined && maxStock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Habis',
                text: 'Produk ini sudah habis dan tidak dapat ditambahkan.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (!cart[id]) {
            cart[id] = {
                id: id,
                id_produk: id,
                name: name,
                price: price,
                originalPrice: originalPrice || price,
                quantity: 0,
                qty: 0,
                image: image,
                maxStock: (maxStock !== undefined) ? parseInt(maxStock) : 999
            };
        } else if (originalPrice && !cart[id].originalPrice) {
            cart[id].originalPrice = originalPrice;
        }

        if (cart[id].maxStock !== undefined && cart[id].quantity >= cart[id].maxStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Stok Tercapai',
                text: `Maksimal stok yang tersedia hanya ${cart[id].maxStock} item.`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        cart[id].quantity += 1;
        cart[id].qty = cart[id].quantity;
        saveCartToStorage();
        updateCartUI();
    }

    function removeFromCart(id) {
        if (cart[id] && cart[id].quantity > 0) {
            cart[id].quantity -= 1;
            cart[id].qty = cart[id].quantity;
            if (cart[id].quantity === 0) {
                delete cart[id];
            }
            saveCartToStorage();
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
                        <button class="btn-qty" onclick="addToCart(${id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}', ${item.maxStock || 999}, ${item.originalPrice || item.price})"><i class="mdi mdi-plus"></i></button>
                        <button class="btn btn-sm text-muted p-1 ms-2" onclick="delete cart[${id}]; saveCartToStorage(); updateCartUI();" style="background:transparent; border:none;">
                            <i class="mdi mdi-close-circle-outline" style="font-size: 18px;"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('cart-items').innerHTML = html || '<div class="text-center py-5 text-muted"><i class="mdi mdi-cart-outline mb-2" style="font-size: 48px;"></i><p class="mb-0">Keranjang masih kosong</p></div>';
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
        window.location.href = "{{ route('pos.checkout.page') }}";
    }
    document.addEventListener("DOMContentLoaded", function() {
        loadCartFromStorage();
        updateCartUI();
    });
</script>
@endsection
