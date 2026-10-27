# Dokumentasi Teknis: Penyederhanaan Metode Pengiriman & Penghapusan Label Harga Statis pada Checkout

## 1. Latar Belakang & Tujuan

Halaman Checkout (`/checkout` &mdash; `resources/views/Pelanggan/checkout.blade.php`) sebelumnya memiliki tampilan Step 2 (**Metode Pengiriman**) yang memuat detail berlebih (*visual noise*) seperti deskripsi panjang gabungan multi-ekspedisi (*"GoSend / Grab / Maxim Instant"*, *"Kuantar / Paxel / Grab Same Day"*, *"JNE / SiCepat / J&T Express"*), estimasi waktu yang padat (*"1-2 Jam"*, *"4-6 Jam"*, *"1-2 Hari"*), serta label harga statis yang belum mencerminkan jarak sebenarnya.

Sesuai instruksi dan pedoman **`frontend-design-direction`**, dilakukan pembaruan antarmuka untuk:
1. **Mengurangi detail berlebih**: Menghapus seluruh teks subtitle deskripsi ekspedisi yang bertele-tele dan label waktu yang mempersempit kartu.
2. **Menetapkan 4 metode kurir spesifik**:
   - **Gojek**
   - **Grab**
   - **Maxim** *(default checked)*
   - **Pick Up di Tempat** *(Ambil di Toko)*
3. **Menghapus label harga statis**: Seluruh label harga statis pada kartu kurir (`Rp. 20.000`, `Rp. 15.000`, `Rp. 12.000`) dihapus karena ongkos kirim nantinya akan disesuaikan secara dinamis berdasarkan kalkulasi jarak/lokasi antara alamat pelanggan dan gerai toko Zyh Corner. Khusus opsi *Pick Up di Tempat*, badge `Gratis` dipertahankan karena pengambilan di toko tidak membebankan biaya kurir.

---

## 2. Struktur Visual Baru (Mockup)

```
┌────────────────────────────────────────────────────────────────────────┐
│ 2. METODE PENGIRIMAN                                                   │
│    Pilih kurir pengantaran pesanan atau ambil langsung di outlet       │
├──────────────────────────────────┬─────────────────────────────────────┤
│ 🔘 🛵 Gojek                      │ 🔘 🛵 Grab                          │
├──────────────────────────────────┼─────────────────────────────────────┤
│ 🔘 🛵 Maxim (Default)            │ 🔘 🏬 Pick Up di Tempat     [Gratis]│
└──────────────────────────────────┴─────────────────────────────────────┘
```

---

## 3. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Pelanggan/checkout.blade.php`)

Mengubah kontainer `#shippingRatesContainer` pada Step 2:

```blade
<!-- Step 2: Shipping Courier Method -->
<div class="checkout-card">
    <div class="checkout-step-header">
        <span class="step-badge">2</span>
        <div>
            <h2 class="checkout-section-title">Metode Pengiriman</h2>
            <p class="checkout-section-subtitle">Pilih kurir pengantaran pesanan atau ambil langsung di outlet</p>
        </div>
    </div>

    <div id="shippingRatesContainer">
        <div class="shipping-grid">
            <!-- Gojek -->
            <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="20000" data-service="Gojek">
                <input type="radio" name="shippingGroup" value="Gojek">
                <div class="shipping-card-content-simple">
                    <span class="shipping-courier-logo">
                        <img src="{{ asset('image/couriers/gojek.svg') }}" alt="Gojek" />
                    </span>
                    <span class="shipping-name">Gojek</span>
                </div>
            </label>

            <!-- Grab -->
            <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="20000" data-service="Grab">
                <input type="radio" name="shippingGroup" value="Grab">
                <div class="shipping-card-content-simple">
                    <span class="shipping-courier-logo">
                        <img src="{{ asset('image/couriers/grab.svg') }}" alt="Grab" />
                    </span>
                    <span class="shipping-name">Grab</span>
                </div>
            </label>

            <!-- Maxim (Default Checked) -->
            <label class="shipping-card-option active" onclick="selectShippingOption(this)" data-cost="20000" data-service="Maxim">
                <input type="radio" name="shippingGroup" value="Maxim" checked>
                <div class="shipping-card-content-simple">
                    <span class="shipping-courier-logo">
                        <img src="{{ asset('image/couriers/maxim.svg') }}" alt="Maxim" />
                    </span>
                    <span class="shipping-name">Maxim</span>
                </div>
            </label>

            <!-- Pick Up di Tempat -->
            <label class="shipping-card-option" onclick="selectShippingOption(this)" data-cost="0" data-service="Pick Up">
                <input type="radio" name="shippingGroup" value="Pick Up">
                <div class="shipping-card-content-simple">
                    <span class="shipping-courier-logo store-logo">
                        <img src="{{ asset('image/logo_circle.png') }}" alt="Pick Up" />
                    </span>
                    <span class="shipping-name">Pick Up di Tempat</span>
                    <span class="shipping-badge free">Gratis</span>
                </div>
            </label>
        </div>
    </div>
</div>
```

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)

Menata kartu opsi pengiriman menjadi struktur satu baris (*single-line horizontal pill*) yang rata tengah secara vertikal:

```css
.shipping-card-option {
    position: relative;
    display: flex;
    align-items: center; /* Rata tengah vertikal */
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    cursor: pointer;
    background: #ffffff;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    user-select: none;
    min-height: 48px; /* Safe touch target */
}

.shipping-card-option:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.shipping-card-option.active {
    border-color: #00b207;
    background: #f0fdf4;
}

.shipping-card-option input[type="radio"] {
    accent-color: #00b207;
    width: 17px;
    height: 17px;
    margin-top: 0;
    margin-right: 10px;
    flex-shrink: 0;
    cursor: pointer;
}

.shipping-card-content-simple {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    min-width: 0;
}

    .shipping-courier-logo {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    .shipping-courier-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }
    .shipping-courier-logo.store-logo {
        border-radius: 50%;
        border: 1px solid #cbd5e1;
    }

.shipping-card-content-simple .shipping-name {
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
}

.shipping-card-content-simple .shipping-badge.free {
    margin-left: auto;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
    background: #dcfce7;
    color: #15803d;
}
```

---

### C. Sinkronisasi Interaktivitas JavaScript (`resources/js/pelanggan_pages.js`)

Memastikan fungsi global `window.selectShippingOption` membaca atribut `data-cost` dan memicu pembaruan ringkasan pesanan via `updateOrderTotalsWithShipping()`:

```javascript
window.selectShippingOption = function(cardEl) {
    if (!cardEl) return;
    document.querySelectorAll('.shipping-card-option').forEach(el => el.classList.remove('active'));
    cardEl.classList.add('active');
    const radio = cardEl.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;

    const costAttr = cardEl.getAttribute('data-cost');
    const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
    window.currentShippingCost = isNaN(cost) ? 0 : cost;
    if (typeof window.updateOrderTotalsWithShipping === 'function') {
        window.updateOrderTotalsWithShipping();
    }
};
```

---

### D. Automated Testing & Kontrak Regresi (`tests/Feature/CheckoutDesignTest.php`)

Menambahkan kontrak pengujian untuk memverifikasi opsi baru dan mencegah kembalinya teks bertele-tele:

```php
// Step 2 Simplified Shipping Methods
$response->assertSee('value="Gojek"', false);
$response->assertSee('value="Grab"', false);
$response->assertSee('value="Maxim"', false);
$response->assertSee('value="Pick Up"', false);
$response->assertSee('Pick Up di Tempat', false);
$response->assertDontSee('GoSend / Grab / Maxim Instant', false);
$response->assertDontSee('Kuantar / Paxel / Grab Same Day', false);
$response->assertDontSee('JNE / SiCepat / J&T Express', false);
$response->assertDontSee('Ekspedisi Reguler', false);
$response->assertDontSee('Same Day Service', false);
```

---

## 4. Hasil Verifikasi

1. **Browser Headless Chromium Evaluation**:
   - 4 opsi terender rapi dalam 2 kolom grid: Gojek, Grab, Maxim, Pick Up di Tempat.
   - Mengklik *Pick Up di Tempat* otomatis memperbarui ongkos kirim pada ringkasan pesanan menjadi `Gratis`.
   - Mengklik *Gojek*, *Grab*, atau *Maxim* mengaktifkan kartu dan memperbarui status radio button secara tepat.
   - Tidak ada teks harga statis pada kartu pengiriman, siap dihubungkan dengan penghitungan dinamis berbasis jarak.

2. **Automated Testing Suite**:
   - `php artisan test --filter=CheckoutDesignTest`: **PASS** (59 assertions).
   - Seluruh test suite (`php artisan test`): **106 passed (505 assertions)** &mdash; 100% lulus tanpa kegagalan atau regresi.
