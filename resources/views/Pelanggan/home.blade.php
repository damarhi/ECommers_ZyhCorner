@extends('Layout.layout_pelanggan')

@section('title', 'Home - Zyh Corner')

@section('styles')
<style>
    .breadcrumb-wrapper { display: none !important; }

    .home-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Hero Section */
    .hero-section {
        background-color: #f2f8f4;
        border-radius: 10px;
        display: flex;
        align-items: center;
        padding: 50px 60px;
        margin-bottom: 60px;
        margin-top: 20px;
    }
    .hero-text { flex: 1; }
    .hero-text h4 { color: #00b207; font-size: 14px; font-weight: 500; margin-bottom: 15px; letter-spacing: 1px; text-transform: uppercase; }
    .hero-text h1 { font-size: 48px; font-weight: 700; color: #1a1a1a; line-height: 1.2; margin-bottom: 20px; }
    .hero-text p.sale { font-size: 24px; color: #1a1a1a; margin-bottom: 10px; }
    .hero-text p.sale span { color: #ff8a00; font-weight: 600; }
    .hero-text p.desc { color: #666; font-size: 14px; margin-bottom: 30px; }
    .hero-btn { background-color: #00b207; color: white; border: none; padding: 14px 32px; border-radius: 50px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: background 0.3s; }
    .hero-btn:hover { background-color: #009906; }
    .hero-image { flex: 1; text-align: right; }
    .hero-image img { max-width: 100%; max-height: 400px; object-fit: contain; }

    /* Titles */
    .section-title { text-align: center; font-size: 32px; font-weight: 600; margin-bottom: 40px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .section-header h2 { font-size: 24px; font-weight: 600; color: #1a1a1a; }
    .view-all { color: #00b207; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 5px; }
    
    .title-line { display: flex; justify-content: center; gap: 4px; margin-top: 10px; }
    .title-line span { height: 3px; background: #00b207; border-radius: 2px; }
    .title-line span:nth-child(1) { width: 12px; opacity: 0.3; }
    .title-line span:nth-child(2) { width: 30px; }
    .title-line span:nth-child(3) { width: 12px; opacity: 0.3; }

    /* Product Grid */
    .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px; }
    .product-card { border: 1px solid #e6e6e6; border-radius: 8px; overflow: hidden; position: relative; transition: all 0.3s; background: white; padding: 15px; }
    .product-card:hover { border-color: #00b207; box-shadow: 0 5px 15px rgba(0,b2,07,0.1); }
    .product-image { height: 200px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; position: relative; }
    .product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .sale-badge { position: absolute; top: 10px; left: 10px; background-color: #ea4b48; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; z-index: 2; }
    .product-actions { position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px; opacity: 0; transition: opacity 0.3s; z-index: 2; }
    .product-card:hover .product-actions { opacity: 1; }
    .action-btn { width: 35px; height: 35px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a1a; cursor: pointer; border: 1px solid #e6e6e6; transition: all 0.3s; text-decoration: none; }
    .action-btn:hover { background: #00b207; color: white; border-color: #00b207; }
    
    .product-info { position: relative; }
    .product-name { font-size: 14px; color: #4d4d4d; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; text-decoration: none; }
    .product-name:hover { color: #00b207; }
    .product-price { font-size: 16px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .product-price .old { color: #999; text-decoration: line-through; font-size: 14px; font-weight: 400; }
    .product-rating { color: #ff8a00; font-size: 12px; }
    .cart-btn { position: absolute; right: 0; bottom: 0; width: 40px; height: 40px; background-color: #f2f2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a1a; cursor: pointer; transition: all 0.3s; }
    .cart-btn:hover { background-color: #00b207; color: white; }

    /* Four Columns */
    .four-cols { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px; }
    .col-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; }
    .small-product-list { display: flex; flex-direction: column; gap: 15px; }
    .small-product-card { display: flex; align-items: center; gap: 15px; border: 1px solid #e6e6e6; border-radius: 8px; padding: 10px; transition: all 0.3s; background: white; cursor: pointer; }
    .small-product-card:hover { border-color: #00b207; box-shadow: 0 5px 15px rgba(0,b2,07,0.1); }
    .sp-image { width: 80px; height: 80px; flex-shrink: 0; background: #f9f9f9; border-radius: 4px; display: flex; justify-content: center; align-items: center; padding: 5px; }
    .sp-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .sp-info { flex: 1; overflow: hidden; }
    .sp-name { font-size: 14px; color: #4d4d4d; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.3s; }
    .sp-name:hover { color: #00b207; }
    .sp-price { font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 5px; }
    .sp-rating { color: #ff8a00; font-size: 10px; }
    .sp-action-btn:hover { background-color: #00b207 !important; color: white !important; border-color: #00b207 !important; }

    .promo-banner { background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1989&auto=format&fit=crop') center/cover; border-radius: 8px; padding: 30px; text-align: center; color: white; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100%; }
    .promo-banner h4 { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: #1a1a1a; background: rgba(255,255,255,0.8); padding: 4px 10px; border-radius: 20px;}
    .promo-banner h2 { font-size: 36px; font-weight: 700; color: #00b207; margin-bottom: 20px; text-shadow: 1px 1px 2px rgba(255,255,255,0.8); }
    .promo-btn { background-color: white; color: #00b207; font-weight: 600; padding: 12px 24px; border-radius: 50px; border: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.3s; }
    .promo-btn:hover { background-color: #00b207; color: white; }

    /* Categories */
    .category-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px; margin-bottom: 60px; }
    .category-card { border: 1px solid #e6e6e6; border-radius: 8px; padding: 25px 15px; text-align: center; transition: all 0.3s; cursor: pointer; background: white; }
    .category-card:hover { border-color: #00b207; box-shadow: 0 5px 15px rgba(0,b2,07,0.1); }
    .cat-icon { height: 60px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; }
    .cat-icon img { max-height: 100%; max-width: 100%; object-fit: contain; }
    .cat-name { font-size: 16px; font-weight: 500; color: #1a1a1a; margin-bottom: 5px; }
    .cat-count { font-size: 12px; color: #999; }
    
    .nav-arrows { display: flex; align-items: center; justify-content: space-between; }
    .nav-arrow { width: 40px; height: 40px; border-radius: 50%; background: white; border: 1px solid #e6e6e6; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.3s; }
    .nav-arrow:hover { background: #00b207; color: white; border-color: #00b207; }

    /* Base tablet responsive adjustments */
    @media (max-width: 991px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .four-cols {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .category-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        .hero-section {
            flex-direction: column-reverse;
            padding: 30px 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .hero-text h1 {
            font-size: 30px;
        }
        .hero-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .hero-image img {
            max-height: 250px;
        }
        .section-title {
            font-size: 24px;
            margin-bottom: 25px;
        }
        .promo-banner {
            min-height: 250px;
        }
        .nav-arrow {
            display: none !important;
        }
        .four-cols {
            grid-template-columns: 1fr !important;
        }
    }
    
    @media (max-width: 576px) {
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .category-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px;
        }
    }
    
    @media (max-width: 1240px) {
        .nav-arrow {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="home-container">
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-text">
            <h4>Welcome to Zyh Corner</h4>
            <h1>Delicious & Fresh<br>Cakes and Desserts</h1>
            <p class="sale">Sale up to <span>30% OFF</span></p>
            <p class="desc">Free shipping on all your order. we deliver, you enjoy</p>
            <button class="hero-btn">Shop now <i class="fa-solid fa-arrow-right"></i></button>
        </div>
        <div class="hero-image">
            <!-- Using a beautiful cake image from Unsplash to match the vibe -->
            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1989&auto=format&fit=crop" alt="Delicious Cakes" style="border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>

    <!-- Featured Products -->
    <div class="section-title">
        Featured Products
        <div class="title-line">
            <span></span><span></span><span></span>
        </div>
    </div>

    <div class="product-grid">
        @foreach($featuredProducts as $product)
        <div class="product-card">
            <div class="product-image">
                @if($loop->first)
                <div class="sale-badge">Sale 50%</div>
                @endif
                <div class="product-actions">
                    <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                    <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                        data-id="{{ $product->id_produk }}"
                        data-name="{{ $product->nama_produk }}"
                        data-price="{{ $product->harga }}"
                        data-desc="{{ $product->deskripsi }}"
                        data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                        data-sale="{{ $loop->first ? '1' : '0' }}"
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
                    @if($loop->first)
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
                <div class="cart-btn" onclick="addToCart({
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
        
        <!-- Add placeholders if less than 4 products -->
        @for($i = $featuredProducts->count(); $i < 4; $i++)
        <div class="product-card">
            <div class="product-image">
                <img src="https://images.unsplash.com/photo-1557308536-ee471ef2c390?q=80&w=400&auto=format&fit=crop" alt="Placeholder">
            </div>
            <div class="product-info">
                <div class="product-name">Delicious Cake {{ $i + 1 }}</div>
                <div class="product-price">Rp. 15.000</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="cart-btn"><i class="fa-solid fa-bag-shopping"></i></div>
            </div>
        </div>
        @endfor
    </div>

    <!-- 4 Columns Section -->
    <div class="four-cols">
        
        <!-- Hot Deals -->
        <div>
            <h3 class="col-title">Hot Deals</h3>
            <div class="small-product-list">
                @foreach($hotDeals as $product)
                <div class="small-product-card" onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="sp-image">
                        @if($product->foto_1)
                        <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                        @else
                        <img src="https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=200&auto=format&fit=crop" alt="Placeholder">
                        @endif
                    </div>
                    <div class="sp-info">
                        <div class="sp-name">{{ $product->nama_produk }}</div>
                        <div class="sp-rating" style="margin-bottom: 5px;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="sp-price" style="margin: 0;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="sp-action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                    data-id="{{ $product->id_produk }}"
                                    data-name="{{ $product->nama_produk }}"
                                    data-price="{{ $product->harga }}"
                                    data-desc="{{ $product->deskripsi }}"
                                    data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                    data-sale="0"
                                    data-stock="{{ $product->stok }}"
                                    data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                    data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                    data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                    data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-regular fa-eye" style="font-size: 12px;"></i>
                                </button>
                                <button type="button" class="sp-action-btn" title="Add to Cart" onclick="event.stopPropagation(); addToCart({
                                    id: '{{ $product->id_produk }}',
                                    name: '{{ $product->nama_produk }}',
                                    price: {{ $product->harga }},
                                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=200&auto=format&fit=crop' }}'
                                })"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-solid fa-bag-shopping" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @for($i = $hotDeals->count(); $i < 3; $i++)
                <div class="small-product-card">
                    <div class="sp-image"><img src="https://images.unsplash.com/photo-1519869325930-281384150729?q=80&w=200&auto=format&fit=crop" alt="Placeholder"></div>
                    <div class="sp-info">
                        <div class="sp-name">Sweet Dessert</div>
                        <div class="sp-price">Rp. 12.000</div>
                        <div class="sp-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Best Seller -->
        <div>
            <h3 class="col-title">Best Seller</h3>
            <div class="small-product-list">
                @foreach($bestSellers as $product)
                <div class="small-product-card" onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="sp-image">
                        @if($product->foto_1)
                        <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                        @else
                        <img src="https://images.unsplash.com/photo-1550617931-e17a7b70dce2?q=80&w=200&auto=format&fit=crop" alt="Placeholder">
                        @endif
                    </div>
                    <div class="sp-info">
                        <div class="sp-name">{{ $product->nama_produk }}</div>
                        <div class="sp-rating" style="margin-bottom: 5px;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="sp-price" style="margin: 0;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="sp-action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                    data-id="{{ $product->id_produk }}"
                                    data-name="{{ $product->nama_produk }}"
                                    data-price="{{ $product->harga }}"
                                    data-desc="{{ $product->deskripsi }}"
                                    data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                    data-sale="0"
                                    data-stock="{{ $product->stok }}"
                                    data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                    data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                    data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                    data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-regular fa-eye" style="font-size: 12px;"></i>
                                </button>
                                <button type="button" class="sp-action-btn" title="Add to Cart" onclick="event.stopPropagation(); addToCart({
                                    id: '{{ $product->id_produk }}',
                                    name: '{{ $product->nama_produk }}',
                                    price: {{ $product->harga }},
                                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1550617931-e17a7b70dce2?q=80&w=200&auto=format&fit=crop' }}'
                                })"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-solid fa-bag-shopping" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @for($i = $bestSellers->count(); $i < 3; $i++)
                <div class="small-product-card">
                    <div class="sp-image"><img src="https://images.unsplash.com/photo-1621303837174-89787a7d4729?q=80&w=200&auto=format&fit=crop" alt="Placeholder"></div>
                    <div class="sp-info">
                        <div class="sp-name">Chocolate Cake</div>
                        <div class="sp-price">Rp. 25.000</div>
                        <div class="sp-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Top Rated -->
        <div>
            <h3 class="col-title">Top Rated</h3>
            <div class="small-product-list">
                @foreach($topRated as $product)
                <div class="small-product-card" onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="sp-image">
                        @if($product->foto_1)
                        <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                        @else
                        <img src="https://images.unsplash.com/photo-1571115177098-24ec42ed204d?q=80&w=200&auto=format&fit=crop" alt="Placeholder">
                        @endif
                    </div>
                    <div class="sp-info">
                        <div class="sp-name">{{ $product->nama_produk }}</div>
                        <div class="sp-rating" style="margin-bottom: 5px;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="sp-price" style="margin: 0;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="sp-action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                    data-id="{{ $product->id_produk }}"
                                    data-name="{{ $product->nama_produk }}"
                                    data-price="{{ $product->harga }}"
                                    data-desc="{{ $product->deskripsi }}"
                                    data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                    data-sale="0"
                                    data-stock="{{ $product->stok }}"
                                    data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                    data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                    data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                    data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-regular fa-eye" style="font-size: 12px;"></i>
                                </button>
                                <button type="button" class="sp-action-btn" title="Add to Cart" onclick="event.stopPropagation(); addToCart({
                                    id: '{{ $product->id_produk }}',
                                    name: '{{ $product->nama_produk }}',
                                    price: {{ $product->harga }},
                                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?q=80&w=200&auto=format&fit=crop' }}'
                                })"
                                    style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid #e6e6e6; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: #1a1a1a;"
                                >
                                    <i class="fa-solid fa-bag-shopping" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @for($i = $topRated->count(); $i < 3; $i++)
                <div class="small-product-card">
                    <div class="sp-image"><img src="https://images.unsplash.com/photo-1542826438-bd32f43d626f?q=80&w=200&auto=format&fit=crop" alt="Placeholder"></div>
                    <div class="sp-info">
                        <div class="sp-name">Strawberry Cupcake</div>
                        <div class="sp-price">Rp. 10.000</div>
                        <div class="sp-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Banner -->
        <div>
            <div class="promo-banner">
                <h4>Summer Sale</h4>
                <h2>75% off</h2>
                <button class="promo-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
        
    </div>

    <!-- Top Category -->
    <div class="section-title">
        Top Category
        <div class="title-line">
            <span></span><span></span><span></span>
        </div>
    </div>
    
    <div style="position: relative;">
        <!-- Left Arrow -->
        <div class="nav-arrow" style="position: absolute; left: -20px; top: 50%; transform: translateY(-50%); z-index: 2;">
            <i class="fa-solid fa-arrow-left"></i>
        </div>

        <div class="category-grid">
            @foreach($categories as $kategori)
            <div class="category-card">
                <div class="cat-icon">
                    @if($kategori->gambar_kategori)
                    <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="{{ $kategori->nama_kategori }}">
                    @else
                    <i class="fa-solid fa-cake-candles" style="font-size: 40px; color: #00b207;"></i>
                    @endif
                </div>
                <div class="cat-name">{{ $kategori->nama_kategori }}</div>
                <div class="cat-count">{{ $kategori->produks_count }} Products</div>
            </div>
            @endforeach
            
            @for($i = $categories->count(); $i < 6; $i++)
            <div class="category-card">
                <div class="cat-icon">
                    <i class="fa-solid fa-ice-cream" style="font-size: 40px; color: #00b207;"></i>
                </div>
                <div class="cat-name">Category {{ $i + 1 }}</div>
                <div class="cat-count">10 Products</div>
            </div>
            @endfor
        </div>

        <!-- Right Arrow -->
        <div class="nav-arrow" style="position: absolute; right: -20px; top: 50%; transform: translateY(-50%); z-index: 2;">
            <i class="fa-solid fa-arrow-right"></i>
        </div>
    </div>

    <!-- Newest Products -->
    <div class="section-header">
        <div class="section-title-left" style="margin-bottom: 0;">
            Newest Products
            <div class="title-line" style="justify-content: flex-start;">
                <span></span><span></span><span></span>
            </div>
        </div>
        <a href="#" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="product-grid">
        @foreach($newestProducts as $product)
        <div class="product-card">
            <div class="product-image">
                <div class="product-actions">
                    <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                    <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                        data-id="{{ $product->id_produk }}"
                        data-name="{{ $product->nama_produk }}"
                        data-price="{{ $product->harga }}"
                        data-desc="{{ $product->deskripsi }}"
                        data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                        data-sale="0"
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
                    <img src="https://images.unsplash.com/photo-1603532648955-039310d9ed75?q=80&w=400&auto=format&fit=crop" alt="Placeholder">
                    @endif
                </a>
            </div>
            <div class="product-info">
                <a href="{{ route('product.detail', $product->id_produk) }}" class="product-name">{{ $product->nama_produk }}</a>
                <div class="product-price">Rp. {{ number_format($product->harga, 0, ',', '.') }}</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="cart-btn" onclick="addToCart({
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
        
        @for($i = $newestProducts->count(); $i < 4; $i++)
        <div class="product-card">
            <div class="product-image">
                <img src="https://images.unsplash.com/photo-1559620192-032c4bc4674e?q=80&w=400&auto=format&fit=crop" alt="Placeholder">
            </div>
            <div class="product-info">
                <div class="product-name">Fresh Pastry {{ $i + 1 }}</div>
                <div class="product-price">Rp. 18.000</div>
                <div class="product-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="cart-btn"><i class="fa-solid fa-bag-shopping"></i></div>
            </div>
        </div>
        @endfor
    </div>

</div>
@endsection
