# Dokumentasi Perubahan Kode: Redesain Tata Letak Halaman Checkout (Opsi A)

**Tanggal Pelaksanaan:** 23 Oktober 2026  
**Status:** Berhasil Diterapkan, Diuji, & Terkompilasi  
**Metodologi:** `frontend-design-direction`  

---

## 1. Ringkasan Eksekutif & Tujuan Perubahan

Berdasarkan analisis visual dan identifikasi masalah pada tangkapan layar (*screenshot*) sebelumnya:
1. **Masalah Sebelumnya (*Scroll Trap & Layout Imbalance*):**
   - Menumpuk seluruh opsi pengiriman, pembayaran, rincian produk, dan tombol pesanan di kolom kanan dengan `max-height: calc(100vh - 110px); overflow-y: auto;` menyebabkan *double scroll* dan *scroll trap*. Header dan daftar produk sering tergeser ke atas atau tersembunyi.
   - Kolom kiri hanya menyisakan alamat dan ucapan kue, meninggalkan ruang kosong (*dead space*) raksasa berwarna putih sepanjang lebih dari 600px di bawahnya.
2. **Solusi Opsi A (*Balanced Two-Column Functional Distribution*):**
   - **Kolom Kiri (`.checkout-main-col`)** difokuskan untuk alur pemenuhan pesanan (*order fulfillment*):
     - **Langkah 1**: Alamat Pengiriman, Pin Peta Interaktif Leaflet, & Catatan Pengiriman.
     - **Langkah 2**: Metode Pengiriman Kurir (Maxim, Kuantar, Iron, Pick Up) dalam format grid 2x2 yang ringkas dan modern.
     - **Langkah 3**: Tulisan di Atas Kue (kondisional jika ada kue kustom) dengan banner notice hangat dan input yang rapi.
   - **Kolom Kanan (`.checkout-sidebar-col` / `.sticky-summary-card`)** difokuskan sebagai terminal transaksi & pembayaran (*financial terminal*):
     - Header Ringkasan Pesanan & badge jumlah produk (`#summaryItemCount`).
     - Daftar Item Produk Belanjaan (`#orderItemsSummary`).
     - Opsi Metode Pembayaran (`.payment-options`: COD, Transfer Midtrans, E-Wallet QRIS).
     - Rincian Total Biaya (`.summary-totals`: Subtotal, Biaya Ongkir, Total Pembayaran).
     - **Tombol Utama "Buat Pesanan Sekarang" (`.btn-place-order`)** tepat di bagian paling bawah kartu ringkasan.
     - *Trust Badges* keamanan dan kesegaran pastry.
   - Menghapus jebakan scroll internal sehingga kartu sticky dapat menempel mulus di viewport desktop tanpa terpotong.

---

## 2. Rincian Berkas & Kode yang Diubah

### A. Template Blade: `resources/views/Pelanggan/checkout.blade.php`

#### 1. Penataan Langkah di Kolom Kiri
Memindahkan seksi kurir pengiriman dari sidebar kanan ke kolom kiri sebagai **Langkah 2**, dan menetapkan tulisan di atas kue sebagai **Langkah 3**:

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
            <!-- input streetAddress, Leaflet map, latitude/longitude, orderNotes -->
        </form>
    </div>

    <!-- Step 2: Shipping Courier Method -->
    <div class="checkout-card">
        <div class="checkout-step-header">
            <span class="step-badge">2</span>
            <div>
                <h2 class="checkout-section-title">Metode Pengiriman</h2>
                <p class="checkout-section-subtitle">Pilih opsi pengiriman yang sesuai dengan preferensi Anda</p>
            </div>
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

            <label class="shipping-card-option" onclick="selectShippingOption(this)">
                <input type="radio" name="shippingGroup" value="Kuantar">
                <div class="shipping-card-content">
                    <div class="shipping-card-top">
                        <span class="shipping-icon"><i class="fa-solid fa-box"></i></span>
                        <span class="shipping-name">Kuantar Same Day</span>
                    </div>
                    <span class="shipping-desc">Pengantaran terjadwal pada hari yang sama</span>
                </div>
            </label>

            <label class="shipping-card-option" onclick="selectShippingOption(this)">
                <input type="radio" name="shippingGroup" value="Iron">
                <div class="shipping-card-content">
                    <div class="shipping-card-top">
                        <span class="shipping-icon"><i class="fa-solid fa-van-shuttle"></i></span>
                        <span class="shipping-name">Iron Courier</span>
                    </div>
                    <span class="shipping-desc">Kurir khusus internal toko untuk pesanan cake besar</span>
                </div>
            </label>

            <label class="shipping-card-option" onclick="selectShippingOption(this)">
                <input type="radio" name="shippingGroup" value="Pick Up">
                <div class="shipping-card-content">
                    <div class="shipping-card-top">
                        <span class="shipping-icon"><i class="fa-solid fa-store"></i></span>
                        <span class="shipping-name">Ambil di Toko (Pick Up)</span>
                        <span class="shipping-badge free">Gratis</span>
                    </div>
                    <span class="shipping-desc">Ambil langsung di outlet Zyh Corner Pekanbaru</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Step 3: Custom Cake Text Card (Conditional) -->
    <div class="checkout-card" id="customCakeTextCard" style="display: none;">
        <div class="checkout-step-header">
            <span class="step-badge">3</span>
            <div>
                <h2 class="checkout-section-title" style="display: flex; align-items: center; gap: 8px;">
                    Tulisan di Atas Kue <span class="step-optional-badge">Opsional</span>
                </h2>
                <p class="checkout-section-subtitle">Permintaan teks ucapan khusus pada permukaan cake</p>
            </div>
        </div>
        
        <!-- Notice Warning Banner -->
        <div class="custom-cake-notice">
            <div class="notice-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="notice-body">
                <strong>Ketentuan Penulisan Ucapan di Atas Kue:</strong>
                <p>Mohon maaf, toko kami <strong>TIDAK MENERIMA</strong> penulisan ucapan <em>Selamat Ulang Tahun (HBD / Ultah)</em>, <em>Barakallah Fii Umrik</em>, <em>Anniversary (Anniv)</em>, serta <strong>angka</strong> dalam bentuk apa pun di atas kue.</p>
                <p class="notice-examples">Contoh ucapan yang diperbolehkan: "Selamat Wisuda", "Semangat Kerja", "Terima Kasih", "Congratulations", dll.</p>
            </div>
        </div>

        <div id="customTextInputsContainer">
            <!-- Rendered by JS per product -->
        </div>
    </div>
</div>
```

#### 2. Kolom Kanan: Terminal Pembayaran & Ringkasan Sticky
```html
<!-- Right Side: Sticky Order Summary, Payment & Checkout Action -->
<div class="checkout-sidebar-col">
    <div class="summary-card sticky-summary-card">
        <div class="summary-card-header">
            <h3 class="summary-card-title">Ringkasan Pesanan</h3>
            <span class="summary-badge-count" id="summaryItemCount">0 Produk</span>
        </div>
        
        <!-- 1. Product list container -->
        <div class="order-items-list" id="orderItemsSummary">
            <!-- Rendered by JS -->
        </div>

        <!-- 2. Section: Payment Method -->
        <div class="summary-section-divider"></div>
        <div class="summary-section-block">
            <div class="summary-section-header">
                <span class="summary-section-icon"><i class="fa-solid fa-credit-card"></i></span>
                <h4 class="summary-section-title">Metode Pembayaran</h4>
            </div>
            
            <div class="payment-options">
                <!-- Cash on Delivery -->
                <div class="payment-option-item active" onclick="selectPaymentMethod('cod')">...</div>
                <!-- Transfer Bank Midtrans Snap -->
                <div class="payment-option-item" onclick="selectPaymentMethod('transfer')">...</div>
                <!-- E-Wallet & QRIS -->
                <div class="payment-option-item" onclick="selectPaymentMethod('ewallet')">...</div>
            </div>
        </div>

        <!-- 3. Totals -->
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

        <!-- 4. Primary Checkout CTA Button: Placed at the very bottom -->
        <button type="button" class="btn-place-order" onclick="submitOrder()">
            <span>Buat Pesanan Sekarang</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>

        <!-- 5. Security & Quality Trust Badges -->
        <div class="checkout-trust-box">
            <div class="trust-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Pembayaran Aman & Terenkripsi</span>
            </div>
            <div class="trust-item">
                <i class="fa-solid fa-cake-candles"></i>
                <span>Pastry Segar Dibuat Setiap Hari</span>
            </div>
            <div class="trust-item">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Konfirmasi Pesanan Cepat</span>
            </div>
        </div>
    </div>
</div>
```

---

### B. Lembar Gaya CSS: `resources/css/pelanggan_custom.css`

1. **Rasio Grid 2 Kolom yang Proporsional:**
   ```css
   .checkout-grid {
       display: grid;
       grid-template-columns: 1.2fr 1fr;
       gap: 32px;
       align-items: start;
   }
   ```

2. **Grid Kurir 2x2 yang Rapi & Kompak:**
   ```css
   .shipping-grid {
       display: grid;
       grid-template-columns: repeat(2, 1fr);
       gap: 12px;
   }
   ```

3. **Penghapusan Scroll Trap pada Kartu Sticky:**
   Menghapus `max-height` dan `overflow-y: auto` kaku sehingga kartu menempel (*sticky*) alami di viewport tanpa scrollbar bertumpuk:
   ```css
   .sticky-summary-card {
       position: sticky;
       top: 90px;
       background: #ffffff;
       border: 1px solid #e8edf2;
       border-radius: 16px;
       padding: 24px;
       box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
   }
   ```

4. **Trust Box yang Elegan:**
   ```css
   .checkout-trust-box {
       display: flex;
       flex-direction: column;
       gap: 10px;
       margin-top: 20px;
       padding-top: 18px;
       border-top: 1px dashed #e2e8f0;
   }
   ```

---

### C. Automated Feature Test: `tests/Feature/CheckoutDesignTest.php`

Menyesuaikan asersi XPath DOM hierarchy untuk menguji kontrak Opsi A:
1. Kolom kiri (`.checkout-main-col`) memuat `.shipping-grid` (Langkah 2) dan **tidak memuat** `.payment-options`.
2. Kolom kanan (`.sticky-summary-card`) memuat `.payment-options` dan **tidak memuat** `.shipping-grid`.
3. Tombol submit pesanan (`.btn-place-order`) dipastikan berada di posisi paling bawah setelah rincian biaya (`.summary-totals`).
4. Seluruh ID dan Name atribut form valid.

```php
// Left column should contain address, shipping methods (Step 2), and custom cake card (Step 3)
$leftCol = $xpath->query('//div[contains(@class, "checkout-main-col")]')->item(0);
$this->assertNotNull($leftCol);
$leftShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $leftCol);
$this->assertEquals(1, $leftShipping->length, 'Shipping options must be in the left column (Step 2)');
$leftPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $leftCol);
$this->assertEquals(0, $leftPayment->length, 'Payment options must not be in the left column');

// Right column (Order Summary Card) contains product summary, payment options, totals, and the CTA button
$summaryCard = $xpath->query('//div[contains(@class, "sticky-summary-card")]')->item(0);
$this->assertNotNull($summaryCard);
$summaryShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $summaryCard);
$this->assertEquals(0, $summaryShipping->length, 'Shipping options are now in left column for balanced layout');
$summaryPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $summaryCard);
$this->assertEquals(1, $summaryPayment->length, 'Payment options must be inside the summary card');
$summaryCta = $xpath->query('.//button[contains(@class, "btn-place-order")]', $summaryCard);
$this->assertEquals(1, $summaryCta->length, 'Place order button must be inside the summary card');

// Verify CTA button appears after summary totals in the summary card
$totalsPos = strpos($html, 'class="summary-totals"');
$ctaPos = strpos($html, 'class="btn-place-order"');
$this->assertGreaterThan($totalsPos, $ctaPos, 'CTA button must be placed at the bottom after totals');
```

---

## 3. Hasil Pengujian & Verifikasi

### A. Uji Spesifik: `CheckoutDesignTest`
```bash
php artisan test --filter=CheckoutDesignTest
```
**Hasil:**
```text
   PASS  Tests\Feature\CheckoutDesignTest
  ✓ checkout page renders with redesigned layout and contracts     0.68s  

  Tests:    1 passed (38 assertions)
  Duration: 0.84s
```

### B. Kompilasi Aset Frontend (Vite)
```bash
npm run build
```
**Hasil:**
```text
vite v8.0.9 building client environment for production...
transforming...✓ 58 modules transformed.
rendering chunks...
computing gzip size...
public/build/assets/pelanggan_pages-Wo_ZeMWq.css  100.38 kB │ gzip: 17.21 kB
public/build/assets/pelanggan_pages-BYUocfiY.js    63.96 kB │ gzip: 13.25 kB
✓ built in 2.71s
```

### C. Pembersihan Cache View Blade
```bash
php artisan view:clear
```
**Hasil:**
```text
   INFO  Compiled views cleared successfully.
```

### D. Uji Regresi Penuh Seluruh Aplikasi
```bash
php artisan test
```
**Hasil:**
```text
  Tests:    91 passed (408 assertions)
  Duration: 19.24s
```
Seluruh 91 pengujian di seluruh modul sistem (pesanan, autentikasi, stok, diskon, banner, POS, midtrans) **lulus 100% tanpa regresi**.
