@extends('Layout.layout_pelanggan')

@section('title', 'Home - Zyh Corner')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ file_exists(public_path('css/home.css')) ? filemtime(public_path('css/home.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/css_home.css') }}?v={{ file_exists(public_path('css/css_home.css')) ? filemtime(public_path('css/css_home.css')) : time() }}">
@endsection

@section('content')
    <div class="home-container">

        <!-- Hero Section / Dynamic Banner Slider -->
        <div class="hero-slider-wrapper position-relative overflow-hidden mb-5" style="border-radius: 24px;">
            <div class="hero-slides-container">
                @forelse($heroBanners as $index => $banner)
                    @php
                        $subjudul = $banner->subjudul;
                        $badgeText = 'Zyh Corner';
                        $calloutPrefix = null;
                        $calloutHighlight = null;
                        $descText = null;

                        if ($subjudul) {
                            if (str_contains($subjudul, '-')) {
                                $parts = explode('-', $subjudul, 2);
                                $badgeText = trim($parts[0]);
                                $calloutCandidate = trim($parts[1]);
                            } else {
                                $calloutCandidate = trim($subjudul);
                            }

                            if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $calloutCandidate, $matches)) {
                                $calloutPrefix = trim($matches[1]) ?: null;
                                $calloutHighlight = trim($matches[2]);
                                $descText = 'Dipanggang segar setiap pagi dengan bahan alami pilihan terbaik untuk momen spesial Anda.';
                            } else {
                                $descText = $calloutCandidate;
                            }
                        }
                    @endphp
                    <div class="hero-slide-item {{ $loop->first ? 'active' : '' }}" data-slide="{{ $index }}">
                        <div class="hero-section">
                            <div class="hero-image hero-showcase-bg">
                                <div class="hero-image-frame">
                                    @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
                                        <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
                                    @else
                                        <img src="{{ asset('storage/banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png') }}" alt="{{ $banner->judul_banner }}">
                                    @endif
                                </div>
                            </div>
                            <div class="hero-text hero-content">
                                <span class="hero-badge-pill">
                                    <i class="fa-solid fa-sparkles text-success"></i> {{ $badgeText }}
                                </span>
                                <h1>{{ $banner->judul_banner }}</h1>
                                @if($calloutHighlight)
                                    <div class="hero-sale-callout">
                                        @if($calloutPrefix)
                                            <span>{{ $calloutPrefix }}</span>
                                        @endif
                                        <span class="hero-sale-highlight">{{ $calloutHighlight }}</span>
                                    </div>
                                @endif
                                @if($descText)
                                    <p class="desc">{{ $descText }}</p>
                                @endif
                                <a href="{{ $banner->link_url ? url($banner->link_url) : route('shop') }}" class="hero-btn">
                                    Shop Now <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="hero-slide-item active" data-slide="0">
                        <div class="hero-section">
                            <div class="hero-image hero-showcase-bg">
                                <div class="hero-image-frame">
                                    <img src="{{ asset('storage/banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png') }}" alt="Zyh Corner Pastry">
                                </div>
                            </div>
                            <div class="hero-text hero-content">
                                <span class="hero-badge-pill"><i class="fa-solid fa-sparkles text-success"></i> Zyh Corner</span>
                                <h1>Delicious & Fresh Cakes and Desserts</h1>
                                <p class="desc">Nikmati aneka pilihan pastry dan cake segar kualitas terbaik dari Zyh Corner setiap hari.</p>
                                <a href="{{ route('shop') }}" class="hero-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($heroBanners->count() > 1)
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

        <!-- Informasi Tambahan (Value Propositions / Features Bar) -->
        <div class="features-bar">
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="feature-content">
                    <h4>Free Shipping</h4>
                    <p>Free shipping on all your order</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="feature-content">
                    <h4>Customer Support 24/7</h4>
                    <p>Instant access to Support</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="feature-content">
                    <h4>100% Secure Payment</h4>
                    <p>We ensure your money is safe</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="feature-content">
                    <h4>Freshly Baked Daily</h4>
                    <p>100% Guaranteed Freshness</p>
                </div>
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
            @forelse ($featuredProducts as $product)
                <div class="product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.06 }}s; cursor: pointer;"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="product-image">
                        @if ($product->has_diskon)
                            <div class="sale-badge">{{ $product->diskon_badge }}</div>
                        @endif
                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        @if ($product->is_signature)
                            <div class="product-meta-eyebrow">
                                <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                            </div>
                        @endif
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price d-flex align-items-center justify-content-between">
                            <div>
                                @if ($product->has_diskon)
                                    Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                    <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                @else
                                    Rp. {{ number_format($product->harga, 0, ',', '.') }}
                                @endif
                            </div>
                            @if ($product->stok == 0)
                                <span class="stock-status-inline">Habis</span>
                            @endif
                        </div>
                        @if ($product->stok > 0)
                            <div class="cart-btn" title="Lihat Preview & Pesan"
                                onclick="event.stopPropagation(); openProductModal(this);"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    Belum ada produk unggulan yang tersedia saat ini.
                </div>
            @endforelse
        </div>

        <!-- 4 Columns Section -->
        <div class="four-cols">

            <!-- 1. Hot Deals -->
            <div>
                <h3 class="col-title">Hot Deals</h3>
                <div class="small-product-list">
                    @forelse ($hotDeals as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk hot deals.</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Best Seller -->
            <div>
                <h3 class="col-title">Best Seller</h3>
                <div class="small-product-list">
                    @forelse ($bestSellers as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk best seller.</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Zyh Signatures -->
            <div>
                <h3 class="col-title">Zyh Signatures</h3>
                <div class="small-product-list">
                    @forelse (($signatureProducts ?? $topRated) as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk signature.</div>
                    @endforelse
                </div>
            </div>
            <!-- 4. Promotional Banner (Ecobazar Figma Replica) -->
            <div>
                @php
                    $promoSub = 'SUMMER SALE';
                    $promoTitle = '75% OFF';
                    if (isset($popupBanner) && $popupBanner) {
                        if (!empty($popupBanner->judul_banner)) {
                            if (preg_match('/(.*?)\s*(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                                $promoSub = strtoupper(trim($matches[1])) ?: 'SUMMER SALE';
                                $promoTitle = strtoupper(trim($matches[2]));
                            } elseif (preg_match('/(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                                $promoTitle = strtoupper(trim($matches[1]));
                                $promoSub = strtoupper($popupBanner->subjudul ?? 'SUMMER SALE');
                            } else {
                                $promoTitle = $popupBanner->judul_banner;
                                $promoSub = strtoupper($popupBanner->subjudul ?? 'SPECIAL PROMO');
                            }
                        } elseif (!empty($popupBanner->subjudul)) {
                            $promoSub = strtoupper($popupBanner->subjudul);
                        }
                    }
                @endphp
                <div class="promo-banner-figma">
                    <!-- Layer 1: Background Edge-to-Edge Imagery Area -->
                    <div class="promo-figma-image-wrap">
                        @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner))
                            <img src="{{ asset('storage/' . $popupBanner->gambar_banner) }}" alt="{{ $popupBanner->judul_banner ?? 'Summer Sale' }}" class="promo-figma-img">
                        @else
                            <img src="{{ asset('storage/banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png') }}" alt="Summer Sale" class="promo-figma-img">
                        @endif
                    </div>

                    <!-- Layer 3: Top Content Area (Badge, Title, Tagline, CTA Button) -->
                    <div class="promo-figma-header">
                        <span class="promo-figma-badge-pill">
                            <i class="fa-solid fa-sparkles"></i> {{ $promoSub }}
                        </span>
                        <h2 class="promo-figma-title">{{ $promoTitle }}</h2>
                        <p class="promo-figma-tagline">Aneka Pastry & Cake Pilihan Terbaik</p>
                        <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-figma-btn">
                            Shop Now <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Layer 3: Floating Tag at Bottom -->
                    <div class="promo-floating-tag">
                        <i class="fa-regular fa-clock"></i> Limited Time Offer
                    </div>
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

        <div class="category-grid">
            @forelse ($categories as $kategori)
                <div class="category-card scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;" onclick="window.location.href='{{ route('shop', ['category' => $kategori->id_kategori]) }}'">
                    <div class="cat-icon">
                        @if ($kategori->gambar_kategori && Storage::disk('public')->exists($kategori->gambar_kategori))
                            <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="{{ $kategori->nama_kategori }}">
                        @else
                            <i class="fa-solid fa-cake-candles" style="font-size: 38px; color: #00b207;"></i>
                        @endif
                    </div>
                    <div class="cat-name">{{ $kategori->nama_kategori }}</div>
                    <div class="cat-count">{{ $kategori->produks_count }} Products</div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 25px; color: #777;">
                    Belum ada kategori produk tersedia.
                </div>
            @endforelse
        </div>

        <!-- Newest Products -->
        <div class="section-header">
            <div class="section-title-left" style="margin-bottom: 0;">
                Newest Products
                <div class="title-line" style="justify-content: flex-start;">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <a href="{{ route('shop') }}" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="product-grid">
            @forelse ($newestProducts as $product)
                <div class="product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.06 }}s; cursor: pointer;"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="product-image">
                        @if ($product->has_diskon)
                            <div class="sale-badge">{{ $product->diskon_badge }}</div>
                        @endif
                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        @if ($product->is_signature)
                            <div class="product-meta-eyebrow">
                                <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                            </div>
                        @endif
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price d-flex align-items-center justify-content-between">
                            <div>
                                @if ($product->has_diskon)
                                    Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                    <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                @else
                                    Rp. {{ number_format($product->harga, 0, ',', '.') }}
                                @endif
                            </div>
                            @if ($product->stok == 0)
                                <span class="stock-status-inline">Habis</span>
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            <div class="cart-btn" title="Lihat Preview & Pesan"
                                onclick="event.stopPropagation(); openProductModal(this);"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    Belum ada produk terbaru saat ini.
                </div>
            @endforelse
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ file_exists(public_path('js/home.js')) ? filemtime(public_path('js/home.js')) : time() }}"></script>
@endsection
