@extends('Layout.layout_pelanggan')

@section('title', 'Home - Zyh Corner')



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
                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1989&auto=format&fit=crop"
                    alt="Delicious Cakes" style="border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
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
            @foreach ($featuredProducts as $product)
                <div class="product-card">
                    <div class="product-image">
                        @if ($product->stok == 0)
                            <div class="sale-badge out-of-stock">Habis</div>
                        @elseif ($loop->first)
                            <div class="sale-badge">Sale 50%</div>
                        @endif
                        <div class="product-actions">
                            <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                            <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $loop->first ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop"
                                    alt="Placeholder">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name">{{ $product->nama_produk }}</a>
                        <div class="product-price">
                            Rp. {{ number_format($product->harga, 0, ',', '.') }}
                            @if ($loop->first)
                                <span class="old">Rp. {{ number_format($product->harga * 2, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            <div class="cart-btn"
                                onclick="addToCart({
                        id: '{{ $product->id_produk }}',
                        name: '{{ $product->nama_produk }}',
                        price: {{ $product->harga }},
                        img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}'
                    })">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Add placeholders if less than 4 products -->
            @for ($i = $featuredProducts->count(); $i < 4; $i++)
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1557308536-ee471ef2c390?q=80&w=400&auto=format&fit=crop"
                            alt="Placeholder">
                    </div>
                    <div class="product-info">
                        <div class="product-name">Delicious Cake {{ $i + 1 }}</div>
                        <div class="product-price">Rp. 15.000</div>

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
                    @foreach ($hotDeals as $product)
                        <div class="small-product-card"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->stok == 0)
                                    <div class="sale-badge out-of-stock" style="top: 5px; left: 5px; font-size: 10px; padding: 2px 6px;">Habis</div>
                                @endif
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}"
                                        alt="{{ $product->nama_produk }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=200&auto=format&fit=crop"
                                        alt="Placeholder">
                                @endif
                            </div>
                            <div class="sp-info">
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="sp-price" style="margin: 0;">Rp.
                                        {{ number_format($product->harga, 0, ',', '.') }}</div>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="0" data-stock="{{ $product->stok }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Add to Cart"
                                                onclick="event.stopPropagation(); addToCart({
                                        id: '{{ $product->id_produk }}',
                                        name: '{{ $product->nama_produk }}',
                                        price: {{ $product->harga }},
                                        img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=200&auto=format&fit=crop' }}'
                                    })">
                                                <i class="fa-solid fa-bag-shopping"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn disabled" title="Stok Habis"
                                                disabled>
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @for ($i = $hotDeals->count(); $i < 3; $i++)
                        <div class="small-product-card">
                            <div class="sp-image"><img
                                    src="https://images.unsplash.com/photo-1519869325930-281384150729?q=80&w=200&auto=format&fit=crop"
                                    alt="Placeholder"></div>
                            <div class="sp-info">
                                <div class="sp-name">Sweet Dessert</div>
                                <div class="sp-price">Rp. 12.000</div>

                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Best Seller -->
            <div>
                <h3 class="col-title">Best Seller</h3>
                <div class="small-product-list">
                    @foreach ($bestSellers as $product)
                        <div class="small-product-card"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->stok == 0)
                                    <div class="sale-badge out-of-stock" style="top: 5px; left: 5px; font-size: 10px; padding: 2px 6px;">Habis</div>
                                @endif
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}"
                                        alt="{{ $product->nama_produk }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1550617931-e17a7b70dce2?q=80&w=200&auto=format&fit=crop"
                                        alt="Placeholder">
                                @endif
                            </div>
                            <div class="sp-info">
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="sp-price" style="margin: 0;">Rp.
                                        {{ number_format($product->harga, 0, ',', '.') }}</div>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="0" data-stock="{{ $product->stok }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        <button type="button" class="sp-action-btn" title="Add to Cart"
                                            onclick="event.stopPropagation(); addToCart({
                                    id: '{{ $product->id_produk }}',
                                    name: '{{ $product->nama_produk }}',
                                    price: {{ $product->harga }},
                                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1550617931-e17a7b70dce2?q=80&w=200&auto=format&fit=crop' }}'
                                })">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @for ($i = $bestSellers->count(); $i < 3; $i++)
                        <div class="small-product-card">
                            <div class="sp-image"><img
                                    src="https://images.unsplash.com/photo-1621303837174-89787a7d4729?q=80&w=200&auto=format&fit=crop"
                                    alt="Placeholder"></div>
                            <div class="sp-info">
                                <div class="sp-name">Chocolate Cake</div>
                                <div class="sp-price">Rp. 25.000</div>

                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Top Rated -->
            <div>
                <h3 class="col-title">Top Rated</h3>
                <div class="small-product-list">
                    @foreach ($topRated as $product)
                        <div class="small-product-card"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->stok == 0)
                                    <div class="sale-badge out-of-stock" style="top: 5px; left: 5px; font-size: 10px; padding: 2px 6px;">Habis</div>
                                @endif
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}"
                                        alt="{{ $product->nama_produk }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1571115177098-24ec42ed204d?q=80&w=200&auto=format&fit=crop"
                                        alt="Placeholder">
                                @endif
                            </div>
                            <div class="sp-info">
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="sp-price" style="margin: 0;">Rp.
                                        {{ number_format($product->harga, 0, ',', '.') }}</div>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="0" data-stock="{{ $product->stok }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 12px;"></i>
                                        </button>
                                        <button type="button" class="sp-action-btn" title="Add to Cart"
                                            onclick="event.stopPropagation(); addToCart({
                                    id: '{{ $product->id_produk }}',
                                    name: '{{ $product->nama_produk }}',
                                    price: {{ $product->harga }},
                                    img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?q=80&w=200&auto=format&fit=crop' }}'
                                 })">
                                            <i class="fa-solid fa-bag-shopping" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @for ($i = $topRated->count(); $i < 3; $i++)
                        <div class="small-product-card">
                            <div class="sp-image"><img
                                    src="https:// <h2>75% off</h2>
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
                    <div class="nav-arrow"
                        style="position: absolute; left: -20px; top: 50%; transform: translateY(-50%); z-index: 2;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </div>

                    <div class="category-grid">
                        @foreach ($categories as $kategori)
                            <div class="category-card">
                                <div class="cat-icon">
                                    @if ($kategori->gambar_kategori)
                                        <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}"
                                            alt="{{ $kategori->nama_kategori }}">
                                    @else
                                        <i class="fa-solid fa-cake-candles" style="font-size: 40px; color: #00b207;"></i>
                                    @endif
                                </div>
                                <div class="cat-name">{{ $kategori->nama_kategori }}</div>
                                <div class="cat-count">{{ $kategori->produks_count }} Products</div>
                            </div>
                        @endforeach

                        @for ($i = $categories->count(); $i < 6; $i++)
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
                    <div class="nav-arrow"
                        style="position: absolute; right: -20px; top: 50%; transform: translateY(-50%); z-index: 2;">
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
                    @foreach ($newestProducts as $product)
                        <div class="product-card">
                            <div class="product-image">
                                @if ($product->stok == 0)
                                    <div class="sale-badge out-of-stock">Habis</div>
                                @endif
                                <div class="product-actions">
                                    <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                                    <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                                        data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                        data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                        data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                        data-sale="0" data-stock="{{ $product->stok }}"
                                        data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                        data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                        data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                        data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                        <i class="fa-regular fa-eye"></i>
                                    </div>
                                </div>
                                <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;">
                                    @if ($product->foto_1)
                                        <img src="{{ asset('storage/' . $product->foto_1) }}"
                                            alt="{{ $product->nama_produk }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1603532648955-039310d9ed75?q=80&w=400&auto=format&fit=crop"
                                            alt="Placeholder">
                                    @endif
                                </a>
                            </div>
                            <div class="product-info">
                                <a href="{{ route('product.detail', $product->id_produk) }}"
                                    class="product-name">{{ $product->nama_produk }}</a>
                                <div class="product-price">Rp. {{ number_format($product->harga, 0, ',', '.') }}</div>

                                @if ($product->stok > 0)
                                    <div class="cart-btn"
                                        onclick="addToCart({
                        id: '{{ $product->id_produk }}',
                        name: '{{ $product->nama_produk }}',
                        price: {{ $product->harga }},
                        img: '{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}'
                    })">
                                        <i class="fa-solid fa-bag-shopping"></i>
                                    </div>
                                @else
                                    <div class="cart-btn disabled" title="Stok Habis">
                                        <i class="fa-solid fa-ban"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @for ($i = $newestProducts->count(); $i < 4; $i++)
                        <div class="product-card">
                            <div class="product-image">
                                <img src="https://images.unsplash.com/photo-1559620192-032c4bc4674e?q=80&w=400&auto=format&fit=crop"
                                    alt="Placeholder">
                            </div>
                            <div class="product-info">
                                <div class="product-name">Fresh Pastry {{ $i + 1 }}</div>
                                <div class="product-price">Rp. 18.000</div>

                                <div class="cart-btn"><i class="fa-solid fa-bag-shopping"></i></div>
                            </div>
                        </div>
                    @endfor
                </div>

            </div>
        @endsection
