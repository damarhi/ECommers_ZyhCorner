# Laporan Eksekusi Solusi 1 & Solusi 3: Pembebasan Foto Produk dari Badge Overlay & Efek Grayscale Stok Habis Beranda

## 1. Latar Belakang & Masalah Desain

Berdasarkan evaluasi antarmuka beranda (*homepage*) dan panduan `/skill:frontend-design-direction`, ditemukan beberapa kelemahan pada desain kartu produk:

1. **Foto Produk Tertutup Badge Overlay (Terutama Thumbnail 76×76px)**:
   - Pada kartu produk kecil (*Hot Deals*, *Best Seller*, dan *Zyh Signatures*), area foto hanya berukuran $76 \times 76\text{ px}$.
   - Keberadaan badge floating overlay seperti `Signature` (oranye emas) dan `Diskon` (merah) yang menempel langsung di atas foto menutupi lebih dari $35\%\text{--}40\%$ luas permukaan foto kue.
   - Hal ini merusak *appetite appeal* (daya tarik visual makanan) karena detail tekstur, krim, topping buah, dan bentuk kelezatan kue terhalang oleh lencana teks.

2. **Penumpukan Lencana Ganda Vertikal (*Double-Badge Stacking*) pada Kartu Grid Besar**:
   - Pada kartu grid besar (*Featured Products* dan *Newest Products*), produk yang berstatus *Signature* dan sedang memiliki diskon atau berstatus habis menampilkan dua badge bertumpuk di pojok kiri atas foto.
   - Penumpukan ini menciptakan polusi visual (*visual clutter*) dan mengalihkan fokus pengguna dari keindahan foto kue.

3. **Lencana "Habis" Berupa Blok Abu-abu Tebal**:
   - Menempelkan badge abu-abu tebal bertuliskan "Habis" di atas foto kue memberikan kesan visual yang kaku dan menutupi estetika foto kue.

---

## 2. Solusi Terpilih: Kombinasi Solusi 1 + Solusi 3

Kombinasi **Solusi 1** dan **Solusi 3** dieksekusi secara penuh untuk memberikan pengalaman visual berkelas, bersih, dan informatif:

- **Solusi 1 (Micro-Eyebrow in Text Info Column)**:
  - Foto thumbnail kecil $76 \times 76\text{ px}$ dibebaskan 100% dari badge overlay.
  - Seluruh penanda status dipindahkan ke kolom informasi teks (`.sp-info`) tepat di atas judul nama kue sebagai *micro-eyebrow pill* yang teratur (`.product-meta-eyebrow`).
  - Lencana Signature menggunakan aksen oranye lembut dengan ikon mahkota (`.eyebrow-signature`), dan diskon menggunakan persentase merah ramping (`.eyebrow-discount`).
  - Pada kartu grid besar, penanda *Signature* dipindahkan ke kolom teks di atas nama produk, menyisakan maksimal 1 lencana diskon mengambang (*single floating pill*) jika ada promosi harga.

- **Solusi 3 (Photo Grayscale & Inline Stock Status)**:
  - Kartu produk dengan stok kosong (`stok == 0`) mendapatkan kelas penanda `.is-out-of-stock`.
  - Foto produk yang habis otomatis menerapkan filter visual CSS `filter: grayscale(85%) opacity(0.72)`. Pengguna langsung memahami status ketersediaan secara intuitif melalui estetika foto yang meredup tanpa memerlukan badge blok yang menutupi kue.
  - Label status stok ditampilkan secara proporsional sebagai label inline bersahaja (`.stock-status-inline`) di samping baris harga, serta tombol keranjang tetap nonaktif (*disabled* dengan ikon ban).

---

## 3. Ringkasan File yang Diubah

| No | File | Deskripsi Perubahan |
|---|---|---|
| 1 | `public/css/home.css` | Penambahan aturan CSS efek grayscale foto stok habis, gaya micro-eyebrow pills (`.product-meta-eyebrow`, `.eyebrow-signature`, `.eyebrow-discount`), label inline status stok (`.stock-status-inline`), dan isolasi pembebasan foto thumbnail dari badge overlay. |
| 2 | `resources/views/Pelanggan/home.blade.php` | Penyelarasan blok internal `<style>` dan pembaruan struktur markup Blade pada 5 section kartu produk: *Featured Products*, *Hot Deals*, *Best Seller*, *Zyh Signatures*, dan *Newest Products*. |

---

## 4. Rincian Perubahan Kode

### A. Perubahan Stylesheet (`public/css/home.css`)

Menambahkan aturan styling modern untuk Solusi 3 dan Solusi 1:

```css
/* ==========================================================================
   SOLUSI 3: EFEK FOTO PRODUK STOK HABIS (GRAYSCALE & MUTED OPACITY)
   ========================================================================== */
.product-card.is-out-of-stock .product-image img,
.small-product-card.is-out-of-stock .sp-image img {
    filter: grayscale(85%) opacity(0.72);
    transition: filter 0.3s ease, opacity 0.3s ease;
}

.product-card.is-out-of-stock:hover .product-image img,
.small-product-card.is-out-of-stock:hover .sp-image img {
    filter: grayscale(60%) opacity(0.85);
}

/* ==========================================================================
   SOLUSI 1: MICRO-EYEBROW & STATUS PADA INFORMASI PRODUK
   ========================================================================== */
.product-meta-eyebrow {
    display: flex;
    align-items: center;
    gap: 4.5px;
    margin-bottom: 3.5px;
    flex-wrap: wrap;
}

/* Signature Eyebrow Pill */
.eyebrow-signature,
.signature-badge.eyebrow-signature {
    position: static;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    color: #d97706;
    background: rgba(217, 119, 6, 0.12);
    padding: 1.5px 5.5px;
    border-radius: 3px;
    box-shadow: none;
    line-height: 1.2;
}

.eyebrow-signature i,
.signature-badge.eyebrow-signature i {
    font-size: 7.5px;
    color: #d97706;
}

/* Discount Eyebrow Pill on Small Cards */
.eyebrow-discount {
    display: inline-flex;
    align-items: center;
    font-size: 8.5px;
    font-weight: 700;
    color: #ea4b48;
    background: rgba(234, 75, 72, 0.1);
    padding: 1.5px 5px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.2;
}

/* Stock Status Inline (Habis) */
.stock-status-inline {
    font-size: 9px;
    font-weight: 600;
    color: #6c757d;
    background: #f1f3f5;
    padding: 1px 5px;
    border-radius: 2.5px;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.3;
}

/* Standalone Signature Badge (Fallback untuk kontainer absolut) */
.signature-badge:not(.eyebrow-signature) {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: #d97706;
    color: #ffffff;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 3px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 3.5px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.signature-badge:not(.eyebrow-signature) i {
    font-size: 8.5px;
}

/* Solusi 1: Bebaskan foto thumbnail kecil 76x76px dari badge overlay */
.sp-image .signature-badge,
.sp-image .sale-badge {
    display: none !important;
}
```

---

### B. Perubahan Template Blade (`resources/views/Pelanggan/home.blade.php`)

#### 1. Penyelarasan Internal `<style>`
Menyertakan deklarasi kelas `.product-card.is-out-of-stock`, `.small-product-card.is-out-of-stock`, `.product-meta-eyebrow`, `.eyebrow-signature`, `.eyebrow-discount`, `.stock-status-inline`, dan pembebasan thumbnail `.sp-image` dari badge overlay.

#### 2. Kartu Grid Besar: *Featured Products* & *Newest Products*
**Struktur Baru:**
- Menambahkan kelas dinamis `{{ $product->stok == 0 ? 'is-out-of-stock' : '' }}` dan `{{ $product->is_signature ? 'is-signature' : '' }}` pada root kartu `.product-card`.
- Di dalam `.product-image`: Hanya menampilkan *single floating pill* diskon (`.sale-badge`) jika ada diskon. Eliminasi penumpukan badge vertikal ganda dan eliminasi badge "Habis" yang menutupi foto.
- Di dalam `.product-info`: Menambahkan penanda *Signature* sebagai eyebrow pill tepat di atas judul nama kue:
  ```blade
  @if ($product->is_signature)
      <div class="product-meta-eyebrow">
          <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
      </div>
  @endif
  ```
- Di dalam `.product-price`: Menampilkan label inline `<span class="stock-status-inline">Habis</span>` jika stok kosong.

```blade
@forelse ($featuredProducts as $product)
    <div class="product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.06 }}s; cursor: pointer;"
        onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
        <div class="product-image">
            @if ($product->has_diskon)
                <div class="sale-badge">{{ $product->diskon_badge }}</div>
            @endif
            <div class="product-actions">
                ...
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
            ...
        </div>
    </div>
@endforelse
```

#### 3. Kartu Thumbnail Kecil ($76 \times 76\text{ px}$): *Hot Deals*, *Best Seller*, dan *Zyh Signatures*
**Struktur Baru:**
- Kontainer gambar `.sp-image` dibersihkan 100% dari badge apa pun.
- Informasi status dipindahkan ke `.sp-info` di atas `.sp-name`:
  ```blade
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
  ```
- Pada blok harga `.sp-price-box`: Menambahkan label inline status stok habis jika `$product->stok == 0`:
  ```blade
  @if ($product->stok == 0)
      <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
  @endif
  ```

---

## 5. Kompatibilitas Test Suite

Untuk menjamin tidak ada regresi pada seluruh test suite Laravel:
- Kelas `.signature-badge` tetap dipertahankan pada elemen eyebrow (`<span class="signature-badge eyebrow-signature">...</span>`).
- Assertion `$response->assertSee('signature-badge')` pada `tests/Feature/SignatureProductsTest.php` tetap terpenuhi secara sempurna tanpa merusak tampilan antarmuka.

### Hasil Uji Otomatis (`php artisan test`)
```text
PASS Tests\Unit\ExampleTest
PASS Tests\Feature\AboutUsTest
PASS Tests\Feature\AdminBannerTest
PASS Tests\Feature\AdminBulkDeleteTest
PASS Tests\Feature\AdminContactMessageNotificationTest
PASS Tests\Feature\AdminContactMessageTest
PASS Tests\Feature\AdminContactSyncTest
PASS Tests\Feature\AdminCustomProductsTest
PASS Tests\Feature\AdminDashboardTest
PASS Tests\Feature\AdminDiskonTest
PASS Tests\Feature\AdminExportTest
PASS Tests\Feature\AdminOrderTest
PASS Tests\Feature\AdminReturStatusTest
PASS Tests\Feature\AdminSettingsTest
PASS Tests\Feature\BankTransferPaymentProofTest
PASS Tests\Feature\BiteshipServiceTest
PASS Tests\Feature\CassierAccessControlTest
PASS Tests\Feature\CassierRedirectTest
PASS Tests\Feature\CassierSyncTest
PASS Tests\Feature\CheckoutDesignTest
PASS Tests\Feature\ContactUsTest
PASS Tests\Feature\CustomCakeTextTest
PASS Tests\Feature\CustomProductsTest
PASS Tests\Feature\CustomerAddressManagementTest
PASS Tests\Feature\CustomerBlockedLoginTest
PASS Tests\Feature\CustomerDashboardTest
PASS Tests\Feature\CustomerSettingsTest
PASS Tests\Feature\ExampleTest
PASS Tests\Feature\ExpiredProductsTest
PASS Tests\Feature\FaqTest
PASS Tests\Feature\GoogleAuthTest
PASS Tests\Feature\KasirEmailNotificationTest
PASS Tests\Feature\LiveInstantSearchTest
PASS Tests\Feature\OrderConfirmationWorkflowTest
PASS Tests\Feature\OrderDetailLayoutTest
PASS Tests\Feature\OrderHistoryTest
PASS Tests\Feature\OrderStockManagementTest
PASS Tests\Feature\PembayaranTest
PASS Tests\Feature\ProductDetailStockInfoTest
PASS Tests\Feature\ProductStockReductionTest
PASS Tests\Feature\RegisterOtpTest
PASS Tests\Feature\ShopStockPriorityTest
PASS Tests\Feature\SignatureProductsTest

Tests:    207 passed (1122 assertions)
Duration: 18.73s
```

---

## 6. Evaluasi Visual Sebelum vs Sesudah

| Aspek | Desain Sebelumnya | Desain Baru (Solusi 1 + Solusi 3) |
|---|---|---|
| **Thumbnail Kecil (76×76px)** | Tertutup badge Signature (oranye) dan Diskon (merah) hingga 40% area gambar; foto kue tidak terlihat utuh. | **100% Bersih**: Foto kue tampil penuh dan menggugah selera (*appetite appeal*). Penanda Signature dan Diskon tertata elegan di atas nama produk. |
| **Grid Produk Besar** | Terdapat penumpukan badge ganda (*stacked badges*) di kiri atas jika produk adalah Signature + Diskon atau Signature + Habis. | **Single Clean Pill**: Hanya ada 1 pill diskon mengambang jika promosi. Penanda Signature dipindahkan ke atas nama kue. Tidak ada lencana bertumpuk. |
| **Status Stok Habis** | Balok badge abu-abu bertuliskan "Habis" menimpa foto kue. | **Grayscale & Inline Label**: Foto kue meredup anggun (`grayscale 85%`, `opacity 72%`), dilengkapi label inline `Habis` di samping baris harga. |
| **Hirarki Informasi** | Badge berebut perhatian dengan foto produk. | Hirarki terstruktur: Foto Kue (Fokus Utama) $\rightarrow$ Micro-Eyebrow $\rightarrow$ Nama Produk $\rightarrow$ Harga & Status Stok. |
