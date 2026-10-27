# Dokumentasi Perubahan Kode: Penggabungan Card Metode Pembayaran & Pengiriman ke Card Ringkasan Pesanan

**Tanggal:** 23 Oktober 2026  
**Status:** Selesai, Terintegrasi & Terverifikasi  
**Terkait:**  
- `resources/views/Pelanggan/checkout.blade.php`  
- `resources/css/pelanggan_custom.css`  
- `resources/js/pelanggan_pages.js`  
- `tests/Feature/CheckoutDesignTest.php`  

---

## 1. Latar Belakang & Analisis UX

Perubahan ini menata ulang tata letak antarmuka pengguna (*UI/UX redesign*) pada halaman Checkout Pelanggan (`/checkout`) dengan menyatukan opsi **Metode Pengiriman** dan **Metode Pembayaran** ke dalam satu card utama **Ringkasan Pesanan** (*Order Summary*) di kolom kanan, sehingga tombol aksi utama **"Buat Pesanan Sekarang"** berada tepat di posisi paling bawah card ringkasan pesanan.

### A. Masalah Tata Letak Sebelumnya
Sebelum perubahan, halaman checkout terbagi menjadi 2 kolom dengan ketimpangan vertikal dan fragmentasi interaksi:
1. **Kolom Kiri Terlalu Panjang & Terpecah:**
   - Langkah 1: Alamat Pengiriman & Pin Peta Interaktif Leaflet.
   - Langkah 2: Tulisan di Atas Kue (kondisional jika ada produk kue kustom).
   - Langkah 3: Card terpisah untuk **Metode Pengiriman** (4 kartu opsi kurir).
   - Langkah 4: Card terpisah untuk **Metode Pembayaran** (COD, Transfer Bank + sub-options BRI/BCA/Mandiri, E-Wallet + sub-options DANA/QRIS).
2. **Kolom Kanan (Card Ringkasan Pendek):**
   - Hanya menampilkan daftar item produk dan rincian total pembayaran.
3. **Gesekan Pengalaman Pengguna (*Eye-Tracking Fatigue*):**
   - Pengguna harus menentukan kurir dan pembayaran di kolom kiri, namun harus melirik bolak-balik ke kolom kanan untuk melihat dampak total biaya dan menekan tombol buat pesanan.
   - Kolom kiri membutuhkan scroll sangat panjang, sedangkan kolom kanan sering kali kosong di bagian bawahnya saat pengguna berada di langkah akhir.

### B. Solusi Desain Baru (*Frontend Design Direction*)
1. **Kolom Kiri Bersih & Terfokus:**
   - Dikhususkan sepenuhnya untuk **Informasi Pengantaran & Kustomisasi** (Langkah 1: Alamat, Titik Peta, Catatan Pengiriman; Langkah 2: Ucapan di Atas Kue).
2. **Kolom Kanan sebagai Terminal Transaksi Terpadu (*All-in-One Summary Card*):**
   - Menggabungkan daftar produk belanjaan, pemilihan metode pengiriman, dan pemilihan metode pembayaran ke dalam satu kartu sticky ringkasan pesanan.
   - Alur logis mengalir runtut dari atas ke bawah:
     1. Header Ringkasan Pesanan & Jumlah Item.
     2. Daftar Item Produk Belanjaan.
     3. Pilihan Metode Pengiriman (Maxim Instant, Kuantar, Iron, Pick Up).
     4. Pilihan Metode Pembayaran (COD, Transfer Bank Midtrans, E-Wallet/QRIS).
     5. Rincian Biaya (Subtotal, Biaya Ongkir, Total Pembayaran).
     6. **Tombol Buat Pesanan Sekarang** (`btn-place-order`) tepat di paling bawah.
     7. *Trust Badges* jaminan keamanan transaksi.

---

## 2. Rincian Kode yang Diubah

### A. Berkas Template Blade: `resources/views/Pelanggan/checkout.blade.php`

#### 1. Kolom Kiri (`.checkout-main-col`)
**Sebelum:**
Memuat Card Langkah 1 (*Alamat*), Card Langkah 2 (*Tulisan di Atas Kue*), Card Langkah 3 (*Metode Pengiriman*), dan Card Langkah 4 (*Metode Pembayaran*).

**Sesudah:**
Card Langkah 3 dan Card Langkah 4 dipindahkan ke kolom kanan. Kolom kiri kini hanya memuat:
```html
<!-- Left Side: Order Fulfillment Steps -->
<div class="checkout-main-col">
    <!-- Step 1: Shipping Address & Map Pin -->
    <div class="checkout-card">
        <div class="checkout-step-header">
            <span class="step-badge">1</span>
            <div>
                <h2 class="checkout-section-title">Alamat Pengiriman & Titik Lokasi</h2>
                <p class="checkout-section-subtitle">Pastikan alamat dan titik penjemputan/pengantaran sesuai</p>
            </div>
        </div>
        <form id="checkoutForm">
            <!-- input streetAddress, Leaflet map, orderNotes -->
        </form>
    </div>

    <!-- Step 2: Custom Cake Text Card (Conditional) -->
    <div class="checkout-card" id="customCakeTextCard" style="display: none;">
        <!-- Banner notice & dynamic inputs container -->
    </div>
</div>
```

#### 2. Kolom Kanan (`.checkout-sidebar-col` / `.sticky-summary-card`)
**Sebelum:**
```html
<div class="summary-card sticky-summary-card">
    <div class="summary-card-header">...</div>
    <div class="order-items-list" id="orderItemsSummary">...</div>
    <div class="summary-totals">...</div>
    <button type="button" class="btn-place-order" onclick="submitOrder()">...</button>
    <div class="checkout-trust-box">...</div>
</div>
```

**Sesudah:**
Menambahkan sub-seksi Metode Pengiriman dan Metode Pembayaran di antara daftar produk belanjaan dan rincian total:
```html
<div class="checkout-sidebar-col">
    <div class="summary-card sticky-summary-card">
        <div class="summary-card-header">
            <h3 class="summary-card-title">Ringkasan Pesanan</h3>
            <span class="summary-badge-count" id="summaryItemCount">0 Produk</span>
        </div>
        
        <!-- 1. Product list container -->
        <div class="order-items-list" id="orderItemsSummary"></div>

        <!-- 2. Section: Shipping Courier Method -->
        <div class="summary-section-divider"></div>
        <div class="summary-section-block">
            <div class="summary-section-header">
                <span class="summary-section-icon"><i class="fa-solid fa-truck-fast"></i></span>
                <h4 class="summary-section-title">Metode Pengiriman</h4>
            </div>
            
            <div class="shipping-grid">
                <label class="shipping-card-option active" onclick="selectShippingOption(this)">
                    <input type="radio" name="shippingGroup" value="Maxim" checked>
                    <div class="shipping-card-content">
                        <div class="shipping-card-top">
                            <span class="shipping-icon"><i class="fa-solid fa-motorcycle"></i></span>
                            <span class="shipping-name">Maxim Instant</span>
                            <span class="shipping-badge">Populer</span>
                        </div>
                        <span class="shipping-desc">Pengantaran cepat via Maxim Delivery (30-60 menit)</span>
                    </div>
                </label>
                <!-- Opsi Kuantar, Iron, Pick Up -->
            </div>
        </div>

        <!-- 3. Section: Payment Method -->
        <div class="summary-section-divider"></div>
        <div class="summary-section-block">
            <div class="summary-section-header">
                <span class="summary-section-icon"><i class="fa-solid fa-credit-card"></i></span>
                <h4 class="summary-section-title">Metode Pembayaran</h4>
            </div>
            
            <div class="payment-options">
                <!-- COD -->
                <div class="payment-option-item active" onclick="selectPaymentMethod('cod')">...</div>
                <!-- Transfer Bank & VA -->
                <div class="payment-option-item" onclick="selectPaymentMethod('transfer')">...</div>
                <!-- E-Wallet & QRIS -->
                <div class="payment-option-item" onclick="selectPaymentMethod('ewallet')">...</div>
            </div>
        </div>

        <!-- 4. Totals -->
        <div class="summary-section-divider"></div>
        <div class="summary-totals">
            <div class="summary-total-row">
                <span class="label">Subtotal Produk</span>
                <span class="value" id="summarySubtotal">Rp. 0</span>
            </div>
            <div class="summary-total-row">
                <span class="label">Biaya Pengiriman</span>
                <span class="value free-badge">Gratis</span>
            </div>
            <div class="summary-total-row grand-total">
                <span class="label">Total Pembayaran</span>
                <span class="value price" id="summaryTotal">Rp. 0</span>
            </div>
        </div>

        <!-- 5. Primary Checkout CTA Button: Placed at the very bottom -->
        <button type="button" class="btn-place-order" onclick="submitOrder()">
            <span>Buat Pesanan Sekarang</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>

        <!-- 6. Security & Quality Trust Badges -->
        <div class="checkout-trust-box">...</div>
    </div>
</div>
```

---

### B. Berkas CSS: `resources/css/pelanggan_custom.css`

#### 1. Rasio Grid Desktop yang Seimbang
**Sebelum:**
```css
.checkout-grid {
    display: grid;
    grid-template-columns: 1.55fr 1fr;
    gap: 32px;
    align-items: start;
}
```
**Sesudah:**
```css
.checkout-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 32px;
    align-items: start;
}
```

#### 2. Penambahan Header & Divider Sub-Seksi Sidebar
```css
.summary-section-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 20px 0 16px;
}
.summary-section-block {
    margin-bottom: 4px;
}
.summary-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.summary-section-icon {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    background: #f0fdf4;
    color: #00b207;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.summary-section-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
```

#### 3. Optimasi Ukuran Kompak Kartu Kurir & Opsi Pembayaran di Sidebar
```css
.shipping-card-option {
    position: relative;
    display: flex;
    align-items: flex-start;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
    user-select: none;
}
.payment-option-item {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
}
.sticky-summary-card {
    position: sticky;
    top: 90px;
    background: #ffffff;
    border: 1px solid #e8edf2;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
    max-height: calc(100vh - 110px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
```

---

### C. Berkas Pengujian: `tests/Feature/CheckoutDesignTest.php`

Menambahkan asersi DOM XPath untuk memvalidasi:
1. Kolom kiri (`.checkout-main-col`) **TIDAK** memuat `.shipping-grid` maupun `.payment-options`.
2. Card ringkasan pesanan (`.sticky-summary-card`) memuat opsi pengiriman (`.shipping-grid`) dan pembayaran (`.payment-options`).
3. Tombol CTA (`.btn-place-order`) berada di posisi paling bawah setelah perhitungan total (`.summary-totals`).

```php
// Left column should only contain address and custom cake cards
$leftCol = $xpath->query('//div[contains(@class, "checkout-main-col")]')->item(0);
$this->assertNotNull($leftCol);
$leftShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $leftCol);
$this->assertEquals(0, $leftShipping->length, 'Shipping options must not be in the left column');
$leftPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $leftCol);
$this->assertEquals(0, $leftPayment->length, 'Payment options must not be in the left column');

// Right column (Order Summary Card) must contain shipping, payment, totals, and the CTA button
$summaryCard = $xpath->query('//div[contains(@class, "sticky-summary-card")]')->item(0);
$this->assertNotNull($summaryCard);
$summaryShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $summaryCard);
$this->assertEquals(1, $summaryShipping->length, 'Shipping options must be merged inside the summary card');
$summaryPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $summaryCard);
$this->assertEquals(1, $summaryPayment->length, 'Payment options must be merged inside the summary card');
$summaryCta = $xpath->query('.//button[contains(@class, "btn-place-order")]', $summaryCard);
$this->assertEquals(1, $summaryCta->length, 'Place order button must be inside the summary card');

// Verify CTA button appears after summary totals in the summary card
$totalsPos = strpos($html, 'class="summary-totals"');
$ctaPos = strpos($html, 'class="btn-place-order"');
$this->assertGreaterThan($totalsPos, $ctaPos, 'CTA button must be placed at the bottom after totals');
```

---

## 3. Hasil Pengujian & Verifikasi

### A. Pengujian Fitur Khusus: `CheckoutDesignTest`
```bash
php artisan test --filter=CheckoutDesignTest
```
**Output:**
```text
   PASS  Tests\Feature\CheckoutDesignTest
  ✓ checkout page renders with redesigned layout and contracts                                                   0.50s  

  Tests:    1 passed (38 assertions)
  Duration: 0.76s
```

### B. Kompilasi Aset Frontend (Vite)
```bash
npm run build
```
**Output:**
```text
vite v8.0.9 building client environment for production...
transforming...✓ 58 modules transformed.
rendering chunks...
computing gzip size...
public/build/assets/pelanggan_pages-xLz_iFk7.css  100.48 kB │ gzip: 17.26 kB
public/build/assets/pelanggan_pages-BYUocfiY.js    63.96 kB │ gzip: 13.25 kB
✓ built in 468ms
```

### C. Pembersihan Cache View Blade
```bash
php artisan view:clear
```
**Output:**
```text
   INFO  Compiled views cleared successfully.
```

### D. Full Test Suite Regresi Aplikasi
```bash
php artisan test
```
**Output:**
```text
  Tests:    91 passed (408 assertions)
  Duration: 8.86s
```
Semua 91 pengujian di seluruh aplikasi (termasuk stok, otentikasi, banner, POS, midtrans, dan checkout) berjalan lancar dan **lulus 100% tanpa regresi**.
