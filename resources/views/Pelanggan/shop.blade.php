@extends('Layout.layout_pelanggan')

@section('title', 'Shop - Zyh Corner')

@section('breadcrumb')
    <a href="#">Categories</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Cake</span>
@endsection

@section('content')
<style>
    .shop-container { max-width: 1200px; margin: 0 auto; padding: 0 15px; margin-bottom: 80px; }
    
    /* Breadcrumb Override */
    .breadcrumb-area {
        background: none;
        height: 60px;
        color: #1a1a1a;
    }
    .breadcrumb a {
        color: #666;
    }
    .breadcrumb {
        padding-left: 0;
    }
    .breadcrumb-wrapper {
        padding: 0;
        background-color: transparent;
    }
    
    /* Promo Banner */
    .shop-promo-banner {
        background: linear-gradient(rgba(45, 78, 55, 0.9), rgba(45, 78, 55, 0.9)), url('https://images.unsplash.com/photo-1558300222-3118128e461b?q=80&w=2069&auto=format&fit=crop') center/cover;
        border-radius: 10px;
        padding: 50px 60px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
    }
    .spb-content h4 { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; color: #b3cbb9; margin-bottom: 10px; }
    .spb-content h2 { font-size: 36px; font-weight: 600; margin-bottom: 25px; }
    .countdown { display: flex; gap: 20px; margin-bottom: 30px; }
    .cd-item { text-align: center; }
    .cd-item .num { font-size: 24px; font-weight: 600; color: #00b207; display: block; margin-bottom: 5px; }
    .cd-item .label { font-size: 10px; color: #b3cbb9; text-transform: uppercase; letter-spacing: 1px; }
    .cd-item .colon { font-size: 24px; font-weight: 600; color: #b3cbb9; position: relative; top: -20px; left: 10px; }
    
    .spb-btn { background-color: #00b207; color: white; border: none; padding: 12px 28px; border-radius: 50px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: background 0.3s; }
    .spb-btn:hover { background-color: #009906; }
    
    .spb-badge { position: absolute; right: 80px; top: 50px; background-color: #ff8a00; color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; font-weight: 600; box-shadow: 0 5px 15px rgba(255,138,0,0.3); }
    .spb-badge span:first-child { font-size: 20px; line-height: 1; }
    .spb-badge span:last-child { font-size: 12px; font-weight: 500; }

    /* Filter Toolbar */
    .filter-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    .filter-group { display: flex; gap: 15px; }
    .filter-select { padding: 10px 15px; border: 1px solid #e6e6e6; border-radius: 4px; font-size: 14px; color: #4d4d4d; outline: none; background-color: white; appearance: none; min-width: 150px; cursor: pointer; }
    .select-wrapper { position: relative; }
    .select-wrapper::after { content: '\f107'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #999; }
    
    .active-filters-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; font-size: 14px; color: #666; }
    .active-filters { display: flex; align-items: center; gap: 15px; }
    .filter-tag { display: inline-flex; align-items: center; gap: 8px; color: #1a1a1a; font-weight: 500; }
    .filter-tag i { color: #999; cursor: pointer; font-size: 12px; }
    
    .results-count { font-weight: 500; color: #1a1a1a; }
    .results-count span { font-weight: 600; }

    /* Product Grid */
    .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
    .product-card { border: 1px solid #e6e6e6; border-radius: 8px; overflow: hidden; position: relative; transition: all 0.3s; background: white; padding: 15px; }
    .product-card:hover { border-color: #00b207; box-shadow: 0 5px 15px rgba(0,b2,07,0.1); }
    .product-image { height: 200px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; position: relative; }
    .product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .badge { position: absolute; top: 10px; left: 10px; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; z-index: 2; }
    .badge.sale { background-color: #ea4b48; }
    .badge.out-of-stock { background-color: #1a1a1a; }
    
    .product-actions { position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px; opacity: 0; transition: opacity 0.3s; z-index: 2; }
    .product-card:hover .product-actions { opacity: 1; }
    .action-btn { width: 35px; height: 35px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a1a; cursor: pointer; border: 1px solid #e6e6e6; transition: all 0.3s; text-decoration: none; }
    .action-btn:hover { background: #00b207; color: white; border-color: #00b207; }
    
    .product-info { position: relative; }
    .product-name { font-size: 14px; color: #4d4d4d; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none; display: block; }
    .product-name:hover { color: #00b207; }
    .product-price { font-size: 16px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .product-price .old { color: #999; text-decoration: line-through; font-size: 14px; font-weight: 400; }
    .product-rating { color: #ff8a00; font-size: 12px; }
    .cart-btn { position: absolute; right: 0; bottom: 0; width: 40px; height: 40px; background-color: #f2f2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a1a; cursor: pointer; transition: all 0.3s; }
    .cart-btn:hover { background-color: #00b207; color: white; }
    .cart-btn.active { background-color: #00b207; color: white; }

    /* Pagination */
    .pagination-wrapper { display: flex; justify-content: center; margin-top: 50px; }
    .pagination { display: flex; gap: 5px; align-items: center; }
    .page-link { width: 36px; height: 36px; display: flex; justify-content: center; align-items: center; border-radius: 50%; font-size: 14px; color: #666; text-decoration: none; transition: all 0.3s; }
    .page-link:hover { background-color: #f2f2f2; color: #1a1a1a; }
    .page-link.active { background-color: #00b207; color: white; font-weight: 500; }
    .page-arrow { width: 36px; height: 36px; display: flex; justify-content: center; align-items: center; border-radius: 50%; border: 1px solid #e6e6e6; color: #666; text-decoration: none; transition: all 0.3s; }
    .page-arrow:hover { border-color: #00b207; color: #00b207; }
</style>

<div class="shop-container">

    <!-- Promo Banner -->
    <div class="shop-promo-banner">
        <div class="spb-content">
            <h4>Best Deals</h4>
            <h2>Sale of the Month</h2>
            <div class="countdown">
                <div class="cd-item">
                    <span class="num">00</span>
                    <span class="label">Days</span>
                </div>
                <span class="colon">:</span>
                <div class="cd-item">
                    <span class="num">02</span>
                    <span class="label">Hours</span>
                </div>
                <span class="colon">:</span>
                <div class="cd-item">
                    <span class="num">18</span>
                    <span class="label">Mins</span>
                </div>
                <span class="colon">:</span>
                <div class="cd-item">
                    <span class="num">46</span>
                    <span class="label">Secs</span>
                </div>
            </div>
            <button class="spb-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></button>
        </div>
        
        <div class="spb-badge">
            <span>56%</span>
            <span>OFF</span>
        </div>
        
        <div style="position: absolute; right: 10%; bottom: 0;">
            <img src="https://images.unsplash.com/photo-1551024601-bec78aea704b?q=80&w=400&auto=format&fit=crop" style="max-height: 250px; background: transparent; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.3)); border-radius: 20px 20px 0 0;" alt="Cake">
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-toolbar">
        <div class="filter-group">
            <div class="select-wrapper">
                <select class="filter-select">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_kategori }}">{{ $cat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="select-wrapper">
                <select class="filter-select">
                    <option value="">Select Price</option>
                    <option value="10-50">Rp. 10.000 - Rp. 50.000</option>
                    <option value="50-100">Rp. 50.000 - Rp. 100.000</option>
                </select>
            </div>
            <div class="select-wrapper">
                <select class="filter-select">
                    <option value="">Select Rating</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars & Up</option>
                </select>
            </div>
        </div>
        
        <div class="filter-group">
            <div class="select-wrapper">
                <select class="filter-select" style="min-width: 120px;">
                    <option value="latest">Sort by: Latest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
            </div>
            <div class="select-wrapper">
                <select class="filter-select" style="min-width: 100px;">
                    <option value="16">Show: 16</option>
                    <option value="32">Show: 32</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Active Filters -->
    <div class="active-filters-row">
        <div class="active-filters">
            <span>Active Filters:</span>
            <div class="filter-tag">Cake <i class="fa-solid fa-xmark"></i></div>
            <div class="filter-tag">Min Rp. 10.000 - Max Rp. 50.000 <i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="results-count">
            <span>{{ number_format($products->total(), 0, ',', '.') }}</span> Results found.
        </div>
    </div>

    <!-- Product Grid -->
    <div class="product-grid">
        @foreach($products as $index => $product)
        <div class="product-card">
            <div class="product-image">
                @if($index == 0 && $product->stok == 0)
                    <div class="badge out-of-stock">Out of Stock</div>
                @elseif($index == 3 || $index == 7)
                    <div class="badge sale">Sale 50%</div>
                @endif
                
                <div class="product-actions">
                    <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                    <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                        data-id="{{ $product->id_produk }}"
                        data-name="{{ $product->nama_produk }}"
                        data-price="{{ $product->harga }}"
                        data-desc="{{ $product->deskripsi }}"
                        data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                        data-sale="{{ ($index == 3 || $index == 7) ? '1' : '0' }}"
                        data-stock="{{ $product->stok }}"
                        data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                        data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                        data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                        data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </div>
                </div>
                
                <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;">
                    @if($product->foto_1)
                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                    @else
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop" alt="Placeholder">
                    @endif
                </a>
            </div>
            <div class="product-info">
                <a href="{{ route('product.detail', $product->id_produk) }}" class="product-name">{{ $product->nama_produk }}</a>
                <div class="product-price">
                    Rp. {{ number_format($product->harga, 0, ',', '.') }}
                    @if($index == 3 || $index == 7)
                    <span class="old">Rp. {{ number_format($product->harga * 2, 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="cart-btn {{ $index == 2 ? 'active' : '' }}" onclick="addToCart({
                    id: '{{ $product->id_produk }}',
                    name: '{{ $product->nama_produk }}',
                    price: {{ $product->harga }},
                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}'
                })">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if ($products->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination">
            @if (!$products->onFirstPage())
                <a href="{{ $products->previousPageUrl() }}" class="page-arrow"><i class="fa-solid fa-chevron-left"></i></a>
            @endif
            
            @for ($i = 1; $i <= $products->lastPage(); $i++)
                @if ($i == $products->currentPage())
                    <span class="page-link active">{{ $i }}</span>
                @else
                    <a href="{{ $products->url($i) }}" class="page-link">{{ $i }}</a>
                @endif
            @endfor

            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="page-arrow"><i class="fa-solid fa-chevron-right"></i></a>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
