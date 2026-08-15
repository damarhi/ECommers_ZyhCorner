@extends('Layout.layout_pelanggan')

@section('title', $product->nama_produk . ' - Zyh Corner')

@section('breadcrumb')
    @if (request()->query('custom') == 1)
        <a href="{{ route('custom-products') }}">Custom Products</a>
    @else
        <a href="{{ route('shop') }}">Shop</a>
    @endif
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    @if (request()->query('custom') == 1)
        <a href="{{ route('custom-products') }}">{{ $product->kategori->nama_kategori ?? 'Produk' }}</a>
    @else
        <a href="{{ route('shop') }}">{{ $product->kategori->nama_kategori ?? 'Produk' }}</a>
    @endif
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">{{ Str::limit($product->nama_produk, 30) }}</span>
@endsection

@section('content')


    <div class="detail-container">
        @if (isset($unpaidOrder) && $unpaidOrder)
            <div class="product-payment-warning-banner" style="background: linear-gradient(135deg, #fdf0ea, #fbe5db); border-left: 5px solid #ea4b48; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 12px; min-width: 280px; flex: 1;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 24px; color: #ea4b48; animation: heartBeat 2.5s infinite;"></i>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; color: #721c24; font-weight: 700;">Pesanan Belum Dibayar</h4>
                        <p style="margin: 3px 0 0 0; font-size: 13px; color: #721c24;">Anda sudah memesan produk ini sebelumnya (Order ID: <strong>{{ $unpaidOrder->order_number }}</strong>). Selesaikan pembayaran agar pesanan tidak otomatis dibatalkan.</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 15px; flex-shrink: 0;">
                    <div style="text-align: right;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #721c24; font-weight: 600; display: block; margin-bottom: 2px;">Sisa Waktu</span>
                        <span id="productPaymentCountdown" style="font-size: 18px; font-weight: 800; color: #ea4b48; font-family: monospace; background: rgba(255,255,255,0.7); padding: 4px 8px; border-radius: 4px; border: 1px solid rgba(0,0,0,0.05); display: inline-block;">00:00:00</span>
                    </div>
                    <a href="{{ route('order.detail', $unpaidOrder->id) }}" class="btn-pay-now-banner" style="background-color: #00b207; color: white; border: none; border-radius: 6px; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0, 178, 7, 0.2);">
                        <i class="fa-solid fa-credit-card"></i> Bayar Sekarang
                    </a>
                </div>
            </div>
            <style>
                @keyframes heartBeat {
                    0% { transform: scale(1); }
                    14% { transform: scale(1.1); }
                    28% { transform: scale(1); }
                    42% { transform: scale(1.1); }
                    70% { transform: scale(1); }
                }
                .btn-pay-now-banner:hover {
                    background-color: #008f05 !important;
                    box-shadow: 0 4px 8px rgba(0, 178, 7, 0.4) !important;
                }
            </style>
        @endif

        {{-- PRODUCT MAIN --}}
        <div class="product-main">

            {{-- Gallery --}}
            <div class="gallery-wrapper">
                <div class="gallery-thumbs" id="thumbsContainer">
                    @php
                        $fotos = array_filter([
                            $product->foto_1 ? asset('storage/' . $product->foto_1) : null,
                            $product->foto_2 ? asset('storage/' . $product->foto_2) : null,
                            $product->foto_3 ? asset('storage/' . $product->foto_3) : null,
                            $product->foto_4 ? asset('storage/' . $product->foto_4) : null,
                        ]);
                        if (empty($fotos)) {
                            $fotos = [
                                'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop',
                            ];
                        }
                    @endphp
                    @foreach ($fotos as $i => $foto)
                        <div class="gallery-thumb {{ $i === array_key_first($fotos) ? 'active' : '' }}"
                            onclick="switchImage('{{ $foto }}', this)">
                            <img src="{{ $foto }}" alt="Foto {{ $loop->iteration }}">
                        </div>
                    @endforeach
                </div>
                <div class="gallery-main">
                    @if ($product->stok == 0)
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

                <div class="pi-info-row"><strong>Kategori</strong><span>:
                        {{ $product->kategori->nama_kategori ?? '-' }}</span></div>
                @if ($product->tanggal_expired)
                    <div class="pi-info-row"><strong>Expired</strong><span>:
                            {{ \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') }}</span></div>
                @endif
                <div class="pi-info-row"><strong>SKU</strong><span>:
                        ZYH-{{ str_pad($product->id_produk, 4, '0', STR_PAD_LEFT) }}</span></div>
                @if ($product->bisa_custom_teks)
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin: 15px 0; font-size: 12.5px; color: #166534; line-height: 1.45;">
                        <div style="font-weight: 600; font-size: 13px; color: #15803d; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                            <i class="fa-solid fa-pen-nib" style="color: #00b207;"></i> Bisa Tambah Tulisan di Atas Kue
                        </div>
                        <div style="font-size: 11.5px; color: #475569;">
                            <span style="color: #b45309; font-weight: 600;">Catatan Ketentuan:</span> Tidak menerima request ucapan <em>Selamat Ulang Tahun (HBD/Ultah)</em>, <em>Barakallah Fii Umrik</em>, <em>Anniversary (Anniv)</em>, serta <strong>angka</strong> dalam bentuk apa pun. Tulisan ucapan diisi saat Checkout.
                        </div>
                    </div>
                @endif

                <div class="pi-actions">
                    @if (request()->query('custom') == 1)
                        <div class="qty-box">
                            <button class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                            <input class="qty-input" type="text" value="1" id="qtyInput" min="1">
                            <button class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <button class="btn-buy" onclick="buyCustomNow()" style="background-color: #25d366 !important;">
                            <i class="fa-brands fa-whatsapp"></i> Pesan via WhatsApp @if($product->stok <= 0) (Pre-Order) @endif
                        </button>
                    @elseif ($product->stok > 0)
                        <div class="qty-box">
                            <button class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                            <input class="qty-input" type="text" value="1" id="qtyInput" min="1">
                            <button class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <button class="btn-buy" onclick="buyNow()">
                            <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
                        </button>
                    @else
                        <div class="qty-box" style="opacity: 0.5; pointer-events: none; background-color: #f5f5f5;">
                            <button class="qty-btn" disabled><i class="fa-solid fa-minus"></i></button>
                            <input class="qty-input" type="text" value="0" id="qtyInput" disabled style="background-color: #f5f5f5;">
                            <button class="qty-btn" disabled><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <button class="btn-buy disabled" disabled style="background-color: #e6e6e6 !important; color: #999999 !important;">
                            <i class="fa-solid fa-ban"></i> Stok Habis
                        </button>
                    @endif
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
            </div>

            <div id="tab-desc" class="tab-content active">
                <div class="desc-text">
                    <p>{{ $product->deskripsi ?? 'Tidak ada deskripsi tersedia untuk produk ini.' }}</p>
                </div>
            </div>

            <div id="tab-spec" class="tab-content">
                <table class="spec-table">
                    <tr>
                        <td>Nama Produk</td>
                        <td>{{ $product->nama_produk }}</td>
                    </tr>
                    <tr>
                        <td>Kategori</td>
                        <td>{{ $product->kategori->nama_kategori ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Harga</td>
                        <td>Rp. {{ number_format($product->harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>{{ $product->status_produk ?? 'Aktif' }}</td>
                    </tr>
                    @if ($product->tanggal_expired)
                        <tr>
                            <td>Tanggal Expired</td>
                            <td>{{ \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>SKU</td>
                        <td>ZYH-{{ str_pad($product->id_produk, 4, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                </table>
            </div>


        </div>

        {{-- RELATED PRODUCTS --}}
        @if ($relatedProducts->count() > 0)
            <div>
                <div class="section-header">
                    <h2>Produk Terkait</h2>
                    <a href="{{ route('shop') }}" class="view-all-link">Lihat Semua <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="related-grid">
                    @foreach ($relatedProducts as $rp)
                        <div class="rp-card">
                            <div class="rp-img">
                                <img src="{{ $rp->foto_1 ? asset('storage/' . $rp->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}"
                                    alt="{{ $rp->nama_produk }}">
                            </div>
                            <div class="rp-cat">{{ $rp->kategori->nama_kategori ?? 'Produk' }}</div>
                            <div class="rp-name">{{ $rp->nama_produk }}</div>
                            <div class="rp-price">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</div>

                            <div style="display:flex; gap:8px; margin-top:10px;">
                                @if (request()->query('custom') == 1)
                                    <a href="{{ route('product.detail', $rp->id_produk) }}?custom=1" class="rp-btn-link"
                                        style="flex:1;">Detail</a>
                                    @if ($rp->stok > 0)
                                        @php
                                            $rpMessage = urlencode("Halo Zyh Corner, saya tertarik untuk memesan Custom Product: " . $rp->nama_produk . " (Harga: Rp. " . number_format($rp->harga, 0, ',', '.') . "). Mohon info lebih lanjut.");
                                            $rpWaUrl = "https://wa.me/628123456789?text=" . $rpMessage;
                                        @endphp
                                        <a href="{{ $rpWaUrl }}" target="_blank" class="rp-btn-link" style="flex:0 0 40px; border:none; cursor:pointer; background-color: #25d366; color: white; display: flex; align-items: center; justify-content: center;" title="Pesan via WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    @else
                                        <button class="rp-btn-link disabled" style="flex:0 0 40px; border:none; cursor:not-allowed; background-color: #f2f2f2; color: #ccc;" disabled title="Stok Habis">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('product.detail', $rp->id_produk) }}" class="rp-btn-link"
                                        style="flex:1;">Detail</a>
                                    @if ($rp->stok > 0)
                                        <button class="rp-btn-link" style="flex:0 0 40px; border:none; cursor:pointer;"
                                            onclick="addToCart({
                                id: '{{ $rp->id_produk }}',
                                name: '{{ $rp->nama_produk }}',
                                price: {{ $rp->harga }},
                                img: '{{ $rp->foto_1 ? asset('storage/' . $rp->foto_1) : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop' }}'
                            })"><i
                                                class="fa-solid fa-bag-shopping"></i></button>
                                    @else
                                        <button class="rp-btn-link disabled" style="flex:0 0 40px; border:none; cursor:not-allowed; background-color: #f2f2f2; color: #ccc;" disabled title="Stok Habis">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <script>
        window.detailConfig = {
            productId: '{{ $product->id_produk }}',
            productName: '{{ $product->nama_produk }}',
            productPrice: {{ $product->harga }},
            bisaCustomTeks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
            productImg: '{{ reset($fotos) }}',
            checkoutRoute: '{{ route('checkout') }}',
            loginRoute: '{{ route('login') }}'
        };

        @if (isset($unpaidOrder) && $unpaidOrder)
        (function() {
            const expiryTime = {{ $unpaidOrder->created_at->addHours(24)->timestamp }} * 1000;
            const timerElement = document.getElementById('productPaymentCountdown');

            function updateTimer() {
                const now = new Date().getTime();
                const distance = expiryTime - now;

                if (distance <= 0) {
                    if (timerElement) {
                        timerElement.innerHTML = "00:00:00";
                        timerElement.style.color = "#ea4b48";
                    }
                    clearInterval(timerInterval);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return;
                }

                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const formattedHours = String(hours).padStart(2, '0');
                const formattedMinutes = String(minutes).padStart(2, '0');
                const formattedSeconds = String(seconds).padStart(2, '0');

                if (timerElement) {
                    timerElement.innerHTML = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
                }
            }

            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);
        })();
        @endif
    </script>
@endsection
