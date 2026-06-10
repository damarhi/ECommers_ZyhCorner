@extends('Layout.layout_pelanggan')

@section('title', $product->nama_produk . ' - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('shop') }}">Shop</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <a href="{{ route('shop') }}">{{ $product->kategori->nama_kategori ?? 'Produk' }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">{{ Str::limit($product->nama_produk, 30) }}</span>
@endsection

@section('content')
<style>
    .detail-container { max-width: 1200px; margin: 0 auto; padding: 0 15px 80px; }

    /* === PRODUCT MAIN SECTION === */
    .product-main { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-bottom: 60px; }

    /* Gallery */
    .gallery-wrapper { display: flex; gap: 15px; }
    .gallery-thumbs { display: flex; flex-direction: column; gap: 10px; width: 90px; }
    .gallery-thumb {
        width: 90px; height: 90px; border: 2px solid #e6e6e6; border-radius: 8px;
        overflow: hidden; cursor: pointer; padding: 5px; background: white;
        transition: border-color 0.3s; flex-shrink: 0;
    }
    .gallery-thumb.active { border-color: #00b207; }
    .gallery-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .gallery-main {
        flex: 1; background: #f9f9f9; border-radius: 12px; overflow: hidden;
        display: flex; justify-content: center; align-items: center; padding: 30px;
        min-height: 400px; position: relative;
    }
    .gallery-main img { max-width: 100%; max-height: 380px; object-fit: contain; transition: opacity 0.3s; }
    .gallery-badge {
        position: absolute; top: 15px; left: 15px; background: #ea4b48;
        color: white; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;
    }
    .gallery-badge.green { background: #00b207; }

    /* Product Info */
    .product-info-panel {}
    .pi-category { font-size: 12px; color: #00b207; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
    .pi-name { font-size: 30px; font-weight: 700; color: #1a1a1a; line-height: 1.3; margin-bottom: 12px; }
    .pi-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .pi-stars { color: #ff8a00; font-size: 13px; display: flex; gap: 2px; }
    .pi-review-count { font-size: 13px; color: #999; }
    .pi-stock { font-size: 13px; font-weight: 500; padding: 3px 10px; border-radius: 20px; }
    .pi-stock.in { background: #e5f6eb; color: #00b207; }
    .pi-stock.out { background: #f2f2f2; color: #999; }
    .pi-divider { border: none; border-top: 1px solid #f2f2f2; margin: 20px 0; }

    .pi-price-block { display: flex; align-items: baseline; gap: 12px; margin-bottom: 20px; }
    .pi-price { font-size: 32px; font-weight: 700; color: #00b207; }
    .pi-old-price { font-size: 18px; color: #bbb; text-decoration: line-through; }
    .pi-discount { background: #fdf0ea; color: #ea4b48; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }

    .pi-desc { font-size: 14px; color: #666; line-height: 1.8; margin-bottom: 25px; }

    .pi-info-row { display: flex; gap: 8px; font-size: 14px; color: #666; margin-bottom: 8px; }
    .pi-info-row strong { color: #333; min-width: 90px; }

    /* Quantity + Actions */
    .pi-actions { display: flex; align-items: center; gap: 12px; margin-top: 25px; flex-wrap: wrap; }
    .qty-box {
        display: flex; align-items: center; border: 1.5px solid #e6e6e6; border-radius: 50px;
        overflow: hidden; height: 48px;
    }
    .qty-btn {
        width: 44px; height: 100%; border: none; background: #f9f9f9; font-size: 16px;
        cursor: pointer; color: #333; transition: background 0.2s;
    }
    .qty-btn:hover { background: #e6e6e6; }
    .qty-input { width: 44px; text-align: center; border: none; font-size: 15px; font-weight: 600; outline: none; }
    .btn-buy {
        flex: 1; min-width: 150px; height: 48px; background: #00b207; color: white; border: none;
        border-radius: 50px; font-weight: 600; font-size: 15px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.3s;
    }
    .btn-buy:hover { background: #009906; }
    .btn-wish {
        width: 48px; height: 48px; border-radius: 50%; border: 1.5px solid #e6e6e6; background: white;
        display: flex; align-items: center; justify-content: center; font-size: 18px; color: #666; cursor: pointer; transition: all 0.3s;
    }
    .btn-wish:hover { border-color: #ea4b48; color: #ea4b48; }

    /* === TABS === */
    .tabs-section { margin-bottom: 60px; }
    .tab-nav { display: flex; gap: 0; border-bottom: 2px solid #e6e6e6; margin-bottom: 30px; }
    .tab-btn {
        padding: 12px 28px; font-size: 15px; font-weight: 500; color: #888; background: none;
        border: none; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.3s;
    }
    .tab-btn.active { color: #00b207; border-bottom-color: #00b207; }
    .tab-content { display: none; font-size: 14px; color: #666; line-height: 1.9; }
    .tab-content.active { display: block; }

    .desc-text { max-width: 800px; }
    .spec-table { width: 100%; border-collapse: collapse; max-width: 600px; }
    .spec-table td { padding: 12px 16px; border-bottom: 1px solid #f2f2f2; font-size: 14px; }
    .spec-table td:first-child { color: #888; width: 200px; font-weight: 500; }
    .spec-table td:last-child { color: #1a1a1a; }

    /* === RELATED PRODUCTS === */
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .section-header h2 { font-size: 22px; font-weight: 700; color: #1a1a1a; }
    .view-all-link { color: #00b207; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 5px; }
    .related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .rp-card {
        border: 1.5px solid #e6e6e6; border-radius: 10px; overflow: hidden;
        background: white; padding: 15px; transition: all 0.3s; position: relative;
    }
    .rp-card:hover { border-color: #00b207; box-shadow: 0 6px 20px rgba(0,178,7,0.1); transform: translateY(-2px); }
    .rp-img { height: 180px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    .rp-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .rp-cat { font-size: 11px; color: #00b207; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .rp-name { font-size: 14px; color: #333; font-weight: 500; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rp-price { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
    .rp-stars { color: #ff8a00; font-size: 11px; }
    .rp-btn-link {
        display: block; margin-top: 10px; text-align: center; background: #f2f9f3;
        color: #00b207; font-size: 13px; font-weight: 600; padding: 8px; border-radius: 6px;
        text-decoration: none; transition: all 0.3s;
    }
    .rp-btn-link:hover { background: #00b207; color: white; }

    @media (max-width: 768px) {
        .product-main { grid-template-columns: 1fr; }
        .related-grid { grid-template-columns: repeat(2, 1fr); }
        .gallery-wrapper { flex-direction: column-reverse; }
        .gallery-thumbs { flex-direction: row; width: 100%; }
        .gallery-thumb { width: 70px; height: 70px; }
    }
</style>

<div class="detail-container">

    {{-- PRODUCT MAIN --}}
    <div class="product-main">

        {{-- Gallery --}}
        <div class="gallery-wrapper">
            <div class="gallery-thumbs" id="thumbsContainer">
                @php
                    $fotos = array_filter([
                        $product->foto_1 ? asset('storage/'.$product->foto_1) : null,
                        $product->foto_2 ? asset('storage/'.$product->foto_2) : null,
                        $product->foto_3 ? asset('storage/'.$product->foto_3) : null,
                        $product->foto_4 ? asset('storage/'.$product->foto_4) : null,
                    ]);
                    if (empty($fotos)) {
                        $fotos = ['https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop'];
                    }
                @endphp
                @foreach($fotos as $i => $foto)
                <div class="gallery-thumb {{ $i === array_key_first($fotos) ? 'active' : '' }}"
                     onclick="switchImage('{{ $foto }}', this)">
                    <img src="{{ $foto }}" alt="Foto {{ $loop->iteration }}">
                </div>
                @endforeach
            </div>
            <div class="gallery-main">
                @if($product->stok == 0)
                    <div class="gallery-badge">Out of Stock</div>
                @else
                    <div class="gallery-badge green">In Stock</div>
                @endif
                <img id="mainProductImage" src="{{ reset($fotos) }}" alt="{{ $product->nama_produk }}">
            </div>
        </div>

        {{-- Info --}}
        <div class="product-info-panel">
            <div class="pi-category">{{ $product->kategori->nama_kategori ?? 'Produk' }}</div>
            <h1 class="pi-name">{{ $product->nama_produk }}</h1>

            <div class="pi-meta">
                <div class="pi-stars">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <span class="pi-review-count">(4.0) · 24 Ulasan</span>
                <span class="pi-stock {{ $product->stok > 0 ? 'in' : 'out' }}">
                    {{ $product->stok > 0 ? 'Tersedia' : 'Habis' }}
                </span>
            </div>

            <div class="pi-price-block">
                <span class="pi-price">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                {{-- Uncomment jika ada harga diskon --}}
                {{-- <span class="pi-old-price">Rp. {{ number_format($product->harga * 1.3, 0, ',', '.') }}</span> --}}
                {{-- <span class="pi-discount">30% Off</span> --}}
            </div>

            <hr class="pi-divider">

            <p class="pi-desc">
                {{ $product->deskripsi ?? 'Tidak ada deskripsi tersedia untuk produk ini.' }}
            </p>

            <div class="pi-info-row"><strong>Kategori</strong><span>: {{ $product->kategori->nama_kategori ?? '-' }}</span></div>
            @if($product->tanggal_expired)
            <div class="pi-info-row"><strong>Expired</strong><span>: {{ \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') }}</span></div>
            @endif
            <div class="pi-info-row"><strong>SKU</strong><span>: ZYH-{{ str_pad($product->id_produk, 4, '0', STR_PAD_LEFT) }}</span></div>
            <div class="pi-info-row"><strong>Stok</strong><span>: {{ $product->stok ?? 'N/A' }}</span></div>

            <div class="pi-actions">
                <div class="qty-box">
                    <button class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                    <input class="qty-input" type="text" value="1" id="qtyInput" min="1">
                    <button class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
                </div>
                <button class="btn-buy" onclick="buyNow()">
                    <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
                </button>
                <button class="btn-wish" title="Tambah ke Wishlist">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
        </div>

    </div>

    {{-- TABS --}}
    <div class="tabs-section">
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('desc', this)">Deskripsi</button>
            <button class="tab-btn" onclick="switchTab('spec', this)">Spesifikasi</button>
            <button class="tab-btn" onclick="switchTab('review', this)">Ulasan (4)</button>
        </div>

        <div id="tab-desc" class="tab-content active">
            <div class="desc-text">
                <p>{{ $product->deskripsi ?? 'Tidak ada deskripsi tersedia untuk produk ini.' }}</p>
            </div>
        </div>

        <div id="tab-spec" class="tab-content">
            <table class="spec-table">
                <tr><td>Nama Produk</td><td>{{ $product->nama_produk }}</td></tr>
                <tr><td>Kategori</td><td>{{ $product->kategori->nama_kategori ?? '-' }}</td></tr>
                <tr><td>Harga</td><td>Rp. {{ number_format($product->harga, 0, ',', '.') }}</td></tr>
                <tr><td>Status</td><td>{{ $product->status_produk ?? 'Aktif' }}</td></tr>
                @if($product->tanggal_expired)
                <tr><td>Tanggal Expired</td><td>{{ \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') }}</td></tr>
                @endif
                <tr><td>SKU</td><td>ZYH-{{ str_pad($product->id_produk, 4, '0', STR_PAD_LEFT) }}</td></tr>
            </table>
        </div>

        <div id="tab-review" class="tab-content">
            <p style="color:#999; font-style: italic;">Belum ada ulasan. Jadilah yang pertama memberi ulasan!</p>
        </div>
    </div>

    {{-- RELATED PRODUCTS --}}
    @if($relatedProducts->count() > 0)
    <div>
        <div class="section-header">
            <h2>Produk Terkait</h2>
            <a href="{{ route('shop') }}" class="view-all-link">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="related-grid">
            @foreach($relatedProducts as $rp)
            <div class="rp-card">
                <div class="rp-img">
                    <img src="{{ $rp->foto_1 ? asset('storage/'.$rp->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}"
                         alt="{{ $rp->nama_produk }}">
                </div>
                <div class="rp-cat">{{ $rp->kategori->nama_kategori ?? 'Produk' }}</div>
                <div class="rp-name">{{ $rp->nama_produk }}</div>
                <div class="rp-price">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</div>
                <div class="rp-stars">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    <i class="fa-regular fa-star"></i>
                </div>
                <div style="display:flex; gap:8px; margin-top:10px;">
                    <a href="{{ route('product.detail', $rp->id_produk) }}" class="rp-btn-link" style="flex:1;">Detail</a>
                    <button class="rp-btn-link" style="flex:0 0 40px; border:none; cursor:pointer;" onclick="addToCart({
                        id: '{{ $rp->id_produk }}',
                        name: '{{ $rp->nama_produk }}',
                        price: {{ $rp->harga }},
                        img: '{{ $rp->foto_1 ? asset('storage/'.$rp->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}'
                    })"><i class="fa-solid fa-bag-shopping"></i></button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
    // Switch gallery main image
    function switchImage(src, thumbEl) {
        document.getElementById('mainProductImage').style.opacity = '0';
        setTimeout(() => {
            document.getElementById('mainProductImage').src = src;
            document.getElementById('mainProductImage').style.opacity = '1';
        }, 150);
        document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
        thumbEl.classList.add('active');
    }

    // Quantity control
    function changeQty(delta) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
    }

    // Tab switch
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        btn.classList.add('active');
    }

    // Buy Now with authentication check
    function buyNow() {
        if (!window.isLoggedIn) {
            window.location.href = "{{ route('login') }}";
            return;
        }
        
        addToCart({
            id: '{{ $product->id_produk }}',
            name: '{{ $product->nama_produk }}',
            price: {{ $product->harga }},
            img: '{{ reset($fotos) }}',
            qty: parseInt(document.getElementById('qtyInput').value)
        });
        window.location.href = '{{ route('checkout') }}';
    }
</script>
@endsection
