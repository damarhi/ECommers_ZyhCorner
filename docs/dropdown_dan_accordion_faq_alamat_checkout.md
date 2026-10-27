# Dokumentasi Teknis: Dropdown Alamat Tersimpan & FAQ-Style Collapsible Address Accordion pada Halaman Checkout

## 1. Ringkasan Eksekutif

Pembaruan ini meredesain tata letak pemilihan alamat pengiriman pada halaman Checkout (`/checkout` &mdash; `resources/views/Pelanggan/checkout.blade.php`) dengan dua fokus utama:
1. **Dropdown Alamat Tersimpan yang Simpel**: Mengganti tampilan grid kartu alamat yang memakan banyak ruang vertikal menjadi satu elemen dropdown (`<select id="savedAddressSelect">`) yang elegan, intuitif, dan responsif.
2. **FAQ-Style Collapsible Accordion**: Menyembunyikan 10+ field formulir rincian alamat (nama penerima, nomor WhatsApp, pencarian kelurahan/kecamatan, kode pos, alamat jalan, peta Leaflet GPS, dan toggle simpan) ke dalam struktur *accordion* yang mengadopsi pola FAQ Zyh Corner. Form rincian alamat disembunyikan (*collapsed*) secara default untuk menghemat lebih dari 500px ruang vertikal layar, dan akan membuka dengan animasi rotasi panah $180^\circ$ saat diklik oleh pengguna atau saat memilih opsi *"Tulis Alamat Baru"*.

---

## 2. Berkas & Komponen yang Diubah

### A. Template Blade (`resources/views/Pelanggan/checkout.blade.php`)

1. **Dropdown Selector Alamat Tersimpan**:
   ```blade
   <div class="saved-address-dropdown-wrapper">
       <select id="savedAddressSelect" class="form-select-custom saved-address-select" onchange="onSavedAddressDropdownChange(this)">
           @foreach($userAddresses as $idx => $addr)
               <option value="{{ $addr->id }}" 
                       data-address="{{ json_encode($addr) }}"
                       {{ ($defaultAddr && $defaultAddr->id === $addr->id) ? 'selected' : '' }}>
                   {{ $addr->label }}{{ $addr->is_default ? ' (Utama)' : '' }} &mdash; {{ $addr->recipient_name }} ({{ $addr->street_address }}{{ $addr->destination_area_name ? ', ' . $addr->destination_area_name : '' }}{{ $addr->postal_code ? ', ' . $addr->postal_code : '' }})
               </option>
           @endforeach
           <option value="new">+ Tulis Alamat Baru</option>
       </select>
   </div>
   ```

2. **FAQ-Style Accordion Card**:
   ```blade
   <div class="checkout-address-accordion {{ $hasSavedAddresses ? '' : 'open' }}" id="checkoutAddressAccordion">
       <div class="checkout-faq-q" onclick="toggleAddressAccordion()" id="addressAccordionHeader">
           <div class="checkout-accordion-summary" id="addressSummaryPreview">
               <div class="address-summary-pill">
                   <i class="fa-solid fa-location-dot" style="color: #00b207;"></i>
                   <span class="summary-name" id="summaryRecipientName">{{ $defaultAddr->recipient_name ?? (Auth::user()->nama ?? 'Penerima') }}</span>
                   <span class="summary-sep">&bull;</span>
                   <span class="summary-address" id="summaryStreetAddress">{{ $defaultAddr->street_address ?? (Auth::user()->alamat ?? 'Tulis rincian alamat...') }}</span>
                   <span class="summary-badge-gps" id="summaryGpsBadge" style="{{ ($defaultAddr && $defaultAddr->latitude) ? '' : 'display: none;' }}">
                       <i class="fa-solid fa-crosshairs"></i> GPS Terpasang
                   </span>
               </div>
           </div>
           <div class="checkout-accordion-toggle-btn">
               <span class="accordion-toggle-label" id="accordionToggleLabel">{{ $hasSavedAddresses ? 'Detail Alamat' : 'Tutup Detail' }}</span>
               <span class="checkout-faq-icon" id="addressAccordionIcon">
                   <i class="fa-solid fa-chevron-down"></i>
               </span>
           </div>
       </div>

       <div class="checkout-faq-a" id="addressAccordionBody">
           <div class="checkout-faq-a-inner">
               <!-- Seluruh input formulir lengkap (Nama, No HP, Area Autocomplete, Kode Pos, Jalan, Leaflet Map) -->
           </div>
       </div>
   </div>
   ```

3. **Field Catatan Pengiriman Luar Accordion**:
   Field `#orderNotes` ditempatkan di luar accordion agar pelanggan dapat selalu memberikan catatan kurir tanpa perlu membuka detail alamat.

4. **Logika JavaScript & Interaktivitas**:
   - `toggleAddressAccordion(forceOpen)`: Mengatur kelas `.open`, animasi rotasi panah, dan pemanggilan `checkoutMap.invalidateSize()` secara otomatis setelah transisi selesai.
   - `onSavedAddressDropdownChange(selectEl)`: Menghubungkan dropdown dengan fungsi `selectSavedAddress(addr)` dan `selectNewAddress()`.
   - `updateSummaryPreview(name, street, hasGps)`: Memperbarui badge ringkasan alamat di bar FAQ secara real-time.
   - Auto-expand saat validasi gagal: Jika pengguna menekan tombol order dan ada input form alamat yang belum valid, accordion otomatis terbuka sebelum `form.reportValidity()` dipanggil.

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css` & `resources/css/pelanggan_pages.css`)

Komponen visual yang ditambahkan:
- `.saved-address-dropdown-wrapper`: Pembungkus dropdown dengan panah kustom SVG.
- `.saved-address-select`: Elemen `<select>` dengan border radius 8px, padding nyaman, dan efek fokus border `#00b207`.
- `.checkout-address-accordion`: Kartu berbingkai hairline dengan transisi border dan bayangan lembut saat `.open`.
- `.checkout-faq-q`: Header accordion berlatar `#f8fafc` dengan kursor pointer.
- `.checkout-faq-icon`: Ikon lingkaran yang berotasi $180^\circ$ saat state `.open`.
- `.address-summary-pill`: Badge ringkasan satu baris yang memuat nama penerima, penggalan alamat jalan, dan status GPS.
- `.checkout-faq-a`: Transisi mulus `max-height` (0.35s cubic-bezier).

---

### C. Modifikasi Skrip Peta Leaflet (`resources/js/pelanggan_pages.js`)

Mengekspos instans peta global `window.checkoutMap = checkoutMap;` dan marker `window.checkoutMarker = checkoutMarker;` agar sinkronisasi ukuran peta (`invalidateSize()`) saat accordion dibuka berjalan mulus tanpa kendala rendering tiles.

---

## 3. Matriks Pengujian & Verifikasi Mutu

1. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build && php artisan view:clear
   ```
   **Hasil**: Sukses 100% (628ms).

2. **Automated Unit & Feature Testing (PHPUnit)**:
   ```bash
   php artisan test --filter=CustomerAddressManagementTest
   php artisan test --filter=CheckoutDesignTest
   php artisan test
   ```
   **Hasil**:
   - `CustomerAddressManagementTest`: **8 passed (32 assertions)**.
   - `CheckoutDesignTest`: **1 passed (48 assertions)**.
   - Total Suite: **106 passed (494 assertions)** &mdash; 100% tes di aplikasi lulus tanpa regresi.
