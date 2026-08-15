@extends('Layout.layout_pelanggan')

@section('title', 'Home - Zyh Corner')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ file_exists(public_path('css/home.css')) ? filemtime(public_path('css/home.css')) : time() }}">
@endsection



@section('content')
    <div class="home-container">

        <!-- Animated Hero Slider Section -->
        <div class="hero-slider-wrapper position-relative overflow-hidden mb-5" style="border-radius: 20px;">
            <div class="hero-slides-container">
                @forelse($heroBanners as $index => $b)
                    <div class="hero-slide-item {{ $loop->first ? 'active' : '' }}" data-slide="{{ $index }}">
                        <div class="hero-section" @if($b->gambar_banner && Storage::disk('public')->exists($b->gambar_banner)) style="background-image: linear-gradient(rgba(255,255,255,0.85), rgba(255,255,255,0.85)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; border-radius: 20px; padding: 45px;" @endif>
                            <div class="hero-text">
                                <h4 class="slide-animated slide-delay-1">{{ $b->subjudul ?? 'Welcome to Zyh Corner' }}</h4>
                                <h1 class="slide-animated slide-delay-2">{!! e($b->judul_banner) !!}</h1>
                                <a href="{{ $b->link_url ? $b->link_url : route('shop') }}" class="hero-btn text-decoration-none d-inline-flex align-items-center slide-animated slide-delay-3 mt-3">Shop now <i class="fa-solid fa-arrow-right ms-2"></i></a>
                            </div>
                            <div class="hero-image slide-animated slide-delay-2">
                                @if($b->gambar_banner && Storage::disk('public')->exists($b->gambar_banner))
                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}"
                                        alt="{{ $b->judul_banner }}" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); width: 100%; max-width: 450px; height: 300px; object-fit: cover;">
                                @else
                                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1989&auto=format&fit=crop"
                                        alt="Delicious Cakes" style="border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Fallback Hero Slide -->
                    <div class="hero-slide-item active" data-slide="0">
                        <div class="hero-section">
                            <div class="hero-text">
                                <h4>Welcome to Zyh Corner</h4>
                                <h1>Delicious & Fresh<br>Cakes and Desserts</h1>
                                <a href="{{ route('shop') }}" class="hero-btn text-decoration-none d-inline-flex align-items-center mt-3">Shop now <i class="fa-solid fa-arrow-right ms-2"></i></a>
                            </div>
                            <div class="hero-image">
                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1989&auto=format&fit=crop"
                                    alt="Delicious Cakes" style="border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if(isset($heroBanners) && $heroBanners->count() > 1)
                <!-- Navigation Arrows -->
                <button class="hero-slider-arrow hero-slider-prev" aria-label="Previous Slide">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="hero-slider-arrow hero-slider-next" aria-label="Next Slide">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Indicator Dots -->
                <div class="hero-slider-dots">
                    @foreach($heroBanners as $index => $b)
                        <span class="hero-dot {{ $loop->first ? 'active' : '' }}" data-slide-to="{{ $index }}"></span>
                    @endforeach
                </div>
            @endif
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
                        bisa_custom_teks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
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
                                        bisa_custom_teks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
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
                                    bisa_custom_teks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
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
                                    bisa_custom_teks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
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
        @if(isset($promoBanners) && $promoBanners->count() > 1)
            @include('Pelanggan.partials.multi_banner_grid', ['banners' => $promoBanners])
        @elseif(isset($popupBanner) && $popupBanner && $popupBanner->status === 'Aktif')
            <div>
                <div class="promo-banner" @if($popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner)) style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ asset('storage/' . $popupBanner->gambar_banner) }}'); background-size: cover; background-position: center;" @endif>
                    @if($popupBanner->subjudul)
                        <h4>{{ $popupBanner->subjudul }}</h4>
                    @endif
                    <h2>{{ $popupBanner->judul_banner }}</h2>
                    @if($popupBanner->link_url)
                        <a href="{{ $popupBanner->link_url }}" class="promo-btn text-decoration-none d-inline-flex align-items-center">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    @else
                        <a href="{{ route('shop') }}" class="promo-btn text-decoration-none d-inline-flex align-items-center">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    @endif
                </div>
            </div>
        @endif

                </div>

                <!-- Top Category -->
                <div class="section-title">
                    Top Category
                    <div class="title-line">
                        <span></span><span></span><span></span>
                    </div>
                </div>

                <div class="category-slider-wrapper">
                    <!-- Left Arrow -->
                    <button type="button" class="category-nav-arrow cat-prev" aria-label="Previous Categories">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <div class="category-grid">
                        @forelse ($categories as $kategori)
                            <div class="category-card" onclick="window.location.href='{{ route('shop', ['category' => $kategori->id_kategori]) }}'">
                                <div class="cat-icon">
                                    @if ($kategori->gambar_kategori && Storage::disk('public')->exists($kategori->gambar_kategori))
                                        <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}"
                                            alt="{{ $kategori->nama_kategori }}">
                                    @else
                                        <i class="fa-solid fa-cake-candles" style="font-size: 38px; color: #00b207;"></i>
                                    @endif
                                </div>
                                <div class="cat-name">{{ $kategori->nama_kategori }}</div>
                                <div class="cat-count">{{ $kategori->produks_count }} Products</div>
                            </div>
                        @empty
                            @for ($i = 0; $i < 5; $i++)
                                <div class="category-card" onclick="window.location.href='{{ route('shop') }}'">
                                    <div class="cat-icon">
                                        <i class="fa-solid fa-ice-cream" style="font-size: 38px; color: #00b207;"></i>
                                    </div>
                                    <div class="cat-name">Category {{ $i + 1 }}</div>
                                    <div class="cat-count">0 Products</div>
                                </div>
                            @endfor
                        @endforelse
                    </div>

                    <!-- Right Arrow -->
                    <button type="button" class="category-nav-arrow cat-next" aria-label="Next Categories">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
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
                        bisa_custom_teks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
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

@section('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ file_exists(public_path('js/home.js')) ? filemtime(public_path('js/home.js')) : time() }}"></script>
@endsection
