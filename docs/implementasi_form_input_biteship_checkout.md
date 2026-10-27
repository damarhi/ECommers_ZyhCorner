# Dokumentasi Perubahan Kode: Implementasi Form Input Biteship pada Halaman Checkout

**Tanggal Pelaksanaan:** 24 Oktober 2026  
**Status:** Berhasil Diterapkan, Diuji, & Terkompilasi  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Ringkasan Eksekutif & Tujuan Perubahan

Untuk mempersiapkan sistem checkout Zyh Corner terhubung dengan platform logistik agregator **Biteship API**, antarmuka formulir checkout (`/checkout`) telah dilengkapi dengan form input data pengiriman yang lengkap sesuai standar API Biteship (Poin A, B, dan C, tanpa opsi tambahan jadwal/asuransi di Poin D):

1. **Poin A — Identitas Kontak Penerima (*Destination Contact*):**
   - **Nama Lengkap Penerima** (`#recipientName`, `name="recipient_name"`): Terisi otomatis dengan nama akun pengguna aktif, namun dapat disesuaikan jika dikirim sebagai hadiah ke orang lain.
   - **Nomor Telepon / WhatsApp** (`#recipientPhone`, `name="recipient_phone"`): Terisi otomatis dari `no_hp` pengguna untuk konfirmasi kurir instan/same day.
2. **Poin B — Geolocation & Validasi Area (*Destination Location*):**
   - **Pencarian Wilayah / Kelurahan / Kecamatan** (`#areaSearchInput`, `name="destination_area_name"`): Dilengkapi autocomplete dropdown interaktif (`#areaDropdownResults`) dan hidden ID (`#destinationAreaId`) untuk pencocokan tarif Biteship (`destination_area_id`).
   - **Kode Pos 5 Digit** (`#postalCode`, `name="postal_code"`): Otomatis terisi saat kelurahan/kecamatan dipilih dari daftar autocomplete.
   - **Alamat Lengkap** (`#streetAddress`) & **Pin Koordinat Peta** (`#map`, `#latitude`, `#longitude`, `#coordsDisplay`): Tetap dipertahankan untuk kurir instan (GoSend, Grab, Maxim).
   - **Catatan Pengiriman** (`#orderNotes`): Instruksi patokan lokasi untuk kurir.
3. **Poin C — Pilihan Kurir Terstruktur Biteship (*Courier Selection*):**
   - Mengelompokkan kurir ke dalam format 4 kategori Biteship di Langkah 2 (`#shippingRatesContainer`):
     - **Instant Courier** (GoSend / Grab / Maxim Instant — 1-2 Jam — Rp. 20.000)
     - **Same Day Service** (Kuantar / Paxel / Grab Same Day — 4-6 Jam — Rp. 15.000)
     - **Ekspedisi Reguler** (JNE / SiCepat / J&T Express — 1-2 Hari — Rp. 12.000)
     - **Ambil di Toko (Pick Up)** (Gratis — Rp. 0)
   - Setiap kartu opsi kurir menyimpan atribut `data-cost` dan secara dinamis memperbarui baris **Biaya Pengiriman** (`#summaryShipping`) serta **Total Pembayaran** (`#summaryTotal`) di kartu ringkasan pesanan kolom kanan.

---

## 2. Rincian Berkas & Kode yang Diubah

### A. Template Blade: `resources/views/Pelanggan/checkout.blade.php`

1. **Penambahan Grid Input Penerima & Wilayah di Langkah 1:**
   ```html
   <!-- Row 1: Recipient Identity (Biteship Point A) -->
   <div class="form-row-2">
       <div class="form-group-custom">
           <label for="recipientName">
               <i class="fa-solid fa-user"></i> Nama Penerima
           </label>
           <input type="text" id="recipientName" name="recipient_name" class="form-input-custom" value="{{ Auth::user()->nama ?? '' }}" placeholder="Nama lengkap penerima pesanan" required>
       </div>
       <div class="form-group-custom">
           <label for="recipientPhone">
               <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
           </label>
           <input type="tel" id="recipientPhone" name="recipient_phone" class="form-input-custom" value="{{ Auth::user()->no_hp ?? '' }}" placeholder="Contoh: 081234567890" required>
       </div>
   </div>

   <!-- Row 2: Area / District Autocomplete & Postal Code (Biteship Point B) -->
   <div class="form-row-2">
       <div class="form-group-custom area-search-wrapper">
           <label for="areaSearchInput">
               <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
           </label>
           <input type="text" id="areaSearchInput" name="destination_area_name" class="form-input-custom" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
           <input type="hidden" id="destinationAreaId" name="destination_area_id" value="">
           <div id="areaDropdownResults" class="area-dropdown-results"></div>
       </div>
       <div class="form-group-custom">
           <label for="postalCode">
               <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
           </label>
           <input type="text" id="postalCode" name="postal_code" class="form-input-custom" maxlength="5" inputmode="numeric" placeholder="Contoh: 28282">
       </div>
   </div>
   ```

2. **Penyempurnaan Opsi Kurir Biteship di Langkah 2:**
   ```html
   <div id="shippingRatesContainer">
       <div class="shipping-grid">
           <label class="shipping-card-option active" onclick="selectShippingOption(this)" data-cost="20000" data-service="Instant Courier">
               <input type="radio" name="shippingGroup" value="Maxim" checked>
               <div class="shipping-card-content">
                   <div class="shipping-card-top">
                       <span class="shipping-icon"><i class="fa-solid fa-motorcycle"></i></span>
                       <span class="shipping-name">Instant Courier</span>
                       <span class="shipping-badge">1-2 Jam</span>
                   </div>
                   <div class="shipping-card-meta">
                       <span class="shipping-desc">GoSend / Grab / Maxim Instant</span>
                       <span class="shipping-cost-value">Rp. 20.000</span>
                   </div>
               </div>
           </label>
           <!-- Opsi Same Day (Rp. 15.000), Ekspedisi Reguler (Rp. 12.000), dan Pick Up (Gratis) -->
       </div>
   </div>
   ```

3. **Sinkronisasi Biaya Ongkir di Ringkasan Pesanan (Kolom Kanan):**
   ```html
   <div class="summary-totals">
       <div class="summary-total-row">
           <span class="label">Subtotal Produk</span>
           <span class="value" id="summarySubtotal">Rp. 0</span>
       </div>
       <div class="summary-total-row">
           <span class="label">Biaya Pengiriman</span>
           <span class="value" id="summaryShipping">Rp. 20.000</span>
       </div>
       <div class="summary-total-row grand-total">
           <span class="label">Total Pembayaran</span>
           <span class="value price" id="summaryTotal">Rp. 0</span>
       </div>
   </div>
   ```

4. **Penambahan Autocomplete Wilayah & Kalkulasi Total pada JavaScript:**
   ```javascript
   window.currentShippingCost = 20000;

   window.selectShippingOption = function(cardEl) {
       if (!cardEl) return;
       document.querySelectorAll('.shipping-card-option').forEach(el => el.classList.remove('active'));
       cardEl.classList.add('active');
       const radio = cardEl.querySelector('input[type="radio"]');
       if (radio) radio.checked = true;
       
       const costAttr = cardEl.getAttribute('data-cost');
       const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
       window.currentShippingCost = isNaN(cost) ? 0 : cost;
       window.updateOrderTotalsWithShipping();
   };

   window.updateOrderTotalsWithShipping = function() {
       let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
       let subtotalVal = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
       const subtotalEl = document.getElementById('summarySubtotal');
       const shippingEl = document.getElementById('summaryShipping');
       const totalEl = document.getElementById('summaryTotal');
       
       if (subtotalEl) subtotalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal);
       
       const cost = window.currentShippingCost !== undefined ? window.currentShippingCost : 20000;
       if (shippingEl) {
           if (cost === 0) {
               shippingEl.className = 'value free-badge';
               shippingEl.textContent = 'Gratis';
           } else {
               shippingEl.className = 'value';
               shippingEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(cost);
           }
       }
       if (totalEl) totalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal + cost);
   };
   ```

---

### B. Lembar Gaya CSS: `resources/css/pelanggan_custom.css`

1. **Grid 2 Kolom untuk Form Fields (`.form-row-2`):**
   ```css
   .form-row-2 {
       display: grid;
       grid-template-columns: 1fr 1fr;
       gap: 16px;
       margin-bottom: 20px;
   }
   .form-row-2 .form-group-custom {
       margin-bottom: 0;
   }
   ```

2. **Styling Popover Autocomplete Wilayah:**
   ```css
   .area-search-wrapper {
       position: relative;
   }
   .area-dropdown-results {
       position: absolute;
       top: calc(100% + 4px);
       left: 0;
       right: 0;
       background: #ffffff;
       border: 1px solid #e2e8f0;
       border-radius: 8px;
       max-height: 220px;
       overflow-y: auto;
       z-index: 1000;
       box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
       display: none;
   }
   .area-dropdown-item {
       padding: 9px 12px;
       cursor: pointer;
       border-bottom: 1px solid #f8fafc;
       font-size: 13px;
       color: #1e293b;
       transition: background 0.15s, color 0.15s;
   }
   .area-dropdown-item:hover {
       background: #f0fdf4;
       color: #00b207;
   }
   .area-dropdown-item .area-detail {
       font-size: 11.5px;
       color: #64748b;
       margin-top: 2px;
   }
   ```

3. **Styling Meta Tarif Kurir pada Kartu Opsi:**
   ```css
   .shipping-card-meta {
       display: flex;
       align-items: center;
       justify-content: space-between;
       gap: 8px;
       margin-top: 4px;
   }
   .shipping-cost-value {
       font-size: 12px;
       font-weight: 700;
       color: #00b207;
       flex-shrink: 0;
   }
   .shipping-cost-value.free {
       color: #16a34a;
   }
   ```

---

### C. Backend Controller: `app/Http/Controllers/Pelanggan/CartCheckoutController.php`

Menangani parameter input Biteship yang dikirimkan saat checkout:
```php
$address = $request->input('address');
$notes = $request->input('notes');
$paymentMethod = $request->input('payment_method', 'COD');
$shippingMethod = $request->input('shipping_method', 'Maxim');
$recipientName = $request->input('recipient_name');
$recipientPhone = $request->input('recipient_phone');
$postalCode = $request->input('postal_code');
$shippingCost = (int) $request->input('shipping_cost', 0);

if (Auth::check()) {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $customerName = !empty($recipientName) ? $recipientName : $user->nama;
    $email = $user->email;

    // Sinkronisasi data kontak & alamat
    if ($address) {
        if (!$user->alamat) $user->update(['alamat' => $address]);
        if ($recipientPhone && !$user->no_hp) $user->update(['no_hp' => $recipientPhone]);
        
        $customer = Customer::where('user_id', $user->user_id)->first();
        if ($customer) {
            if (!$customer->location) $customer->update(['location' => $address]);
            if ($recipientPhone && !$customer->contact) $customer->update(['contact' => $recipientPhone]);
        } else {
            Customer::create([
                'user_id' => $user->user_id,
                'name' => $customerName,
                'email' => $user->email,
                'contact' => $recipientPhone ?: $user->no_hp,
                'location' => $address
            ]);
        }
    }
} else {
    $customerName = !empty($recipientName) ? $recipientName : 'Guest Customer';
    $email = null;
}

$fullAddress = $address;
if ($postalCode && !str_contains($fullAddress, $postalCode)) {
    $fullAddress .= ' (Kode Pos: ' . $postalCode . ')';
}

$finalNotes = $notes;
if ($recipientPhone) {
    $contactNote = '[Penerima: ' . $customerName . ' (' . $recipientPhone . ')]';
    $finalNotes = $finalNotes ? ($finalNotes . ' ' . $contactNote) : $contactNote;
}

$pesanan = Pesanan::create([
    'order_number' => $orderNumber,
    'jenis_penjualan' => 'Online',
    'payment_method' => $paymentMethod,
    'shipping_method' => $shippingMethod,
    'customer_name' => $customerName,
    'email_pembeli' => $email,
    'alamat_pengiriman' => $fullAddress,
    'catatan_pesanan' => $finalNotes,
    'payment_status' => 'Pending',
    'order_status' => $orderStatus,
    'total' => $total + $shippingCost,
    'items' => $items,
]);
```

---

### D. Automated Feature Test: `tests/Feature/CheckoutDesignTest.php`

Menambahkan verifikasi kontrak form untuk seluruh input baru Biteship:
```php
$response->assertSee('id="recipientName"', false);
$response->assertSee('id="recipientPhone"', false);
$response->assertSee('id="areaSearchInput"', false);
$response->assertSee('id="destinationAreaId"', false);
$response->assertSee('id="postalCode"', false);
$response->assertSee('id="areaDropdownResults"', false);
$response->assertSee('id="shippingRatesContainer"', false);
$response->assertSee('name="recipient_name"', false);
$response->assertSee('name="recipient_phone"', false);
$response->assertSee('name="postal_code"', false);
```

---

## 3. Hasil Pengujian & Kompilasi Aset

1. **Uji Fitur Khusus: `CheckoutDesignTest`**
   ```bash
   php artisan test --filter=CheckoutDesignTest
   # PASS (48 assertions) - 9.37s
   ```
2. **Kompilasi Aset Frontend (Vite):**
   ```bash
   npm run build
   # public/build/assets/pelanggan_pages-GpL8V0pE.css (101.36 kB)
   # public/build/assets/pelanggan_pages-BYUocfiY.js (63.96 kB)
   # Built in 9.61s
   ```
3. **Pembersihan Cache View Blade:**
   ```bash
   php artisan view:clear
   # INFO Compiled views cleared successfully.
   ```
4. **Uji Regresi Penuh Seluruh Modul:**
   ```bash
   php artisan test
   # Tests: 91 passed (418 assertions) - 47.76s
   ```
   Seluruh 91 pengujian di modul pesanan, autentikasi, stok, diskon, dan administrasi toko **lulus 100% tanpa regresi**.
