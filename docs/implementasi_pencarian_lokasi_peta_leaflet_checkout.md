# Dokumentasi Implementasi Pencarian Lokasi Peta Leaflet pada Halaman Checkout

## 1. Ringkasan Fitur

Fitur pencarian lokasi peta Leaflet (*Map Geocoding Search Bar*) dan tombol deteksi lokasi instan (*Geolocation* "Gunakan Lokasi Saya") telah berhasil diintegrasikan pada formulir pengiriman di **Halaman Checkout** (`resources/views/Pelanggan/checkout.blade.php`).

Fitur ini melengkapi fungsionalitas peta penandaan titik antar (*Step 1: Alamat Pengiriman & Titik Lokasi*) sehingga pelanggan dapat:
1. Mencari nama jalan, komplek perumahan, gedung, atau tempat umum langsung di formulir checkout tanpa harus menggeser peta secara manual dari titik awal.
2. Memilih rekomendasi tempat dari dropdown pencarian, yang kemudian secara otomatis memindahkan kanvas peta Leaflet dengan animasi halus (*smooth flyTo*), menempatkan pin marker, dan mengisi input koordinat `latitude` dan `longitude`.
3. Menggunakan tombol *"Gunakan Lokasi Saya"* untuk mendeteksi koordinat GPS pengguna secara langsung via browser Geolocation API.
4. Membersihkan teks pencarian secara instan melalui tombol `×` atau otomatis saat beralih alamat tersimpan.

---

## 2. Arsitektur & Prinsip Desain

1. **100% Client-Side (Zero Backend Change)**:
   - Tidak ada modifikasi pada kode backend Laravel (`CheckoutController.php`, model `Order`, maupun rute API).
   - Nilai koordinat tetap diteruskan melalui input hidden form standar:
     ```html
     <input type="hidden" id="latitude" name="latitude" value="...">
     <input type="hidden" id="longitude" name="longitude" value="...">
     ```
   - Menggunakan layanan publik **OpenStreetMap (OSM) Nominatim Geocoding API** (`https://nominatim.openstreetmap.org/search?format=json&q=...&countrycodes=id&limit=5`) tanpa dependensi library eksternal berbayar.

2. **Kepatuhan Desain Flat & Minimalis (`/skill:frontend-design-direction`)**:
   - Menghindari penumpukan kartu (*no cards inside cards*); search bar diletakkan menyatu di dalam elemen `Row 4: Map & GPS Coordinate` tepat di atas kanvas peta Leaflet.
   - Menggunakan token warna resmi Zyh Corner: hijau solid `#00b207` (hover `#009406`), abu-abu gelap `#0f172a`, teks sekunder `#64748b`, border netral `#cbd5e1`/`#e2e8f0`, dan latar `#ffffff`/`#f8fafc`.
   - Tombol *"Gunakan Lokasi Saya"* didesain sebagai tombol outline ramping di baris judul label peta untuk menjaga kerapian vertikal.

3. **Status Konten & Interaksi Pengguna (Micro-Interactions)**:
   - **Idle State**: Input ramping setinggi `36px` dengan ikon pencarian abu-abu lembut dan tombol aksi "Cari" hijau solid.
   - **Loading State**: Spinner animasi FontAwesome `<i class="fa-solid fa-spinner fa-spin"></i>` dengan pesan *"Mencari titik lokasi di peta..."*.
   - **Empty State**: Keterangan ramah jika lokasi tidak ditemukan: *"Lokasi tidak ditemukan. Coba gunakan nama jalan atau daerah yang lebih umum."*.
   - **Outside-Click Dismiss**: Dropdown hasil pencarian otomatis tertutup saat pengguna mengeklik area lain di luar kotak pencarian atau saat berinteraksi dengan peta.
   - **Sinkronisasi Alamat Tersimpan**: Input pencarian peta otomatis di-reset bersih ketika pengguna beralih antar alamat tersimpan di dropdown buku alamat checkout.

---

## 3. Rincian Perubahan Kode (Code Changes)

### A. Modifikasi `resources/views/Pelanggan/checkout.blade.php`

1. **Pembaruan Header Label & Penambahan Search Bar di Row 4 Peta** (Baris 141–170):
   ```blade
   <!-- Row 4: Map & GPS Coordinate (Biteship Point B) -->
   <div class="form-group-custom">
       <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
           <label style="margin-bottom: 0;">
               <i class="fa-solid fa-map-location-dot"></i> Titik Lokasi Pengantaran di Peta
           </label>
           <button type="button" class="btn-locate-me-checkout" onclick="locateUserPositionCheckout()">
               <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
           </button>
       </div>

       <!-- Map Geocoding Search Bar -->
       <div class="map-search-container" id="checkoutMapSearchContainer">
           <div class="map-search-bar">
               <div class="map-search-input-wrap">
                   <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                   <input type="text" id="checkoutMapSearchInput" class="map-search-input" 
                          placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                          autocomplete="off"
                          onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnCheckoutMap(); }"
                          oninput="toggleClearCheckoutSearchBtn()">
                   <button type="button" class="btn-clear-map-search" id="checkoutClearSearchBtn" onclick="clearCheckoutMapSearch()" style="display: none;" title="Hapus pencarian">
                       <i class="fa-solid fa-xmark"></i>
                   </button>
               </div>
               <button type="button" class="btn-search-map" onclick="searchLocationOnCheckoutMap()">
                   Cari
               </button>
           </div>
           <div id="checkoutMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
       </div>

       <div class="map-wrapper">
           <div id="map"></div>
           <div class="map-note">
               <i class="fa-solid fa-location-crosshairs"></i>
               <span id="coordsDisplay">{{ ($defaultAddr && $defaultAddr->latitude) ? 'Titik koordinat tersimpan: ' . number_format($defaultAddr->latitude, 5) . ', ' . number_format($defaultAddr->longitude, 5) : 'Klik peta atau seret marker untuk menandai lokasi tepat Anda' }}</span>
           </div>
       </div>
       <input type="hidden" id="latitude" name="latitude" value="{{ $defaultAddr->latitude ?? '' }}">
       <input type="hidden" id="longitude" name="longitude" value="{{ $defaultAddr->longitude ?? '' }}">
   </div>
   ```

2. **Integrasi Pembersihan Search Bar saat Ganti Alamat**:
   - Memanggil `clearCheckoutMapSearch()` pada `selectSavedAddress` dan `selectNewAddress`.

---

### B. Modifikasi `resources/css/pelanggan_custom.css`

Menambahkan styling komponen pencarian peta dan tombol *"Gunakan Lokasi Saya"* (Baris 465–640):
```css
/* Map Search Bar Minimalist */
.map-search-container {
    position: relative;
    margin-bottom: 8px;
    z-index: 10;
}

.map-search-bar {
    display: flex;
    gap: 6px;
    align-items: center;
}

.map-search-input-wrap {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
}

.map-search-icon {
    position: absolute;
    left: 11px;
    font-size: 13px;
    color: #94a3b8;
    pointer-events: none;
}

.map-search-input {
    width: 100%;
    height: 36px;
    padding: 0 30px 0 32px;
    font-size: 13px;
    color: #0f172a;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    font-family: inherit;
}

.map-search-input:focus {
    border-color: #00b207;
    box-shadow: 0 0 0 2px rgba(0, 178, 7, 0.12);
}

.map-search-input::placeholder {
    color: #94a3b8;
    font-size: 12.5px;
}

.btn-clear-map-search {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 13px;
    padding: 2px 6px;
    border-radius: 4px;
}

.btn-clear-map-search:hover {
    color: #475569;
}

.btn-search-map {
    height: 36px;
    padding: 0 14px;
    background: #00b207;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.15s;
}

.btn-search-map:hover {
    background: #009406;
}

.btn-search-map:active {
    transform: scale(0.98);
}

.btn-locate-me-checkout {
    background: #ffffff;
    border: 1px solid #00b207;
    color: #00b207;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
    padding: 4px 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s;
}

.btn-locate-me-checkout:hover {
    background: #00b207;
    color: #ffffff;
}

.map-search-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    max-height: 220px;
    overflow-y: auto;
    z-index: 1000;
}

.map-search-loading {
    padding: 10px 14px;
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.map-search-empty {
    padding: 10px 14px;
    font-size: 12.5px;
    color: #dc2626;
    line-height: 1.4;
}

.map-search-item {
    padding: 9px 12px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background-color 0.15s;
}

.map-search-item:last-child {
    border-bottom: none;
}

.map-search-item:hover {
    background: #f8fafc;
}

.map-search-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}

.map-search-item-title i {
    color: #00b207;
    font-size: 11px;
}

.map-search-item-sub {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 2px;
    padding-left: 17px;
    line-height: 1.35;
}
```

---

### C. Modifikasi `resources/js/pelanggan_pages.js`

1. **Perbaikan Koordinat Awal pada `initCheckoutMap()`** (Baris 2085–2097):
   Peta membaca nilai koordinat tersimpan di `#latitude` dan `#longitude` sebagai prioritas inisialisasi awal, dengan fallback ke koordinat default Zyh Corner (`0.40384, 101.85588`).

2. **Penambahan Fungsi Pencarian & Geolocation Checkout** (Baris 2146–2318):
   - `window.toggleClearCheckoutSearchBtn()`
   - `window.clearCheckoutMapSearch()`
   - `window.searchLocationOnCheckoutMap()`
   - `window.selectCheckoutMapSearchResult(lat, lon, title)`
   - `window.locateUserPositionCheckout()`
   - Event listener `click` pada `document` untuk menutup dropdown hasil saat klik di luar kontainer pencarian.

---

## 4. Hasil Verifikasi & Validasi

1. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build
   ```
   *Hasil*: Selesai dalam 529ms tanpa ada error atau peringatan (`pelanggan_pages-B5XIFx7T.js` dan `pelanggan_pages-CWO6K8Zg.css`).

2. **Pengujian Kontrak Desain Checkout (PHPUnit)**:
   ```bash
   php artisan test tests/Feature/CheckoutDesignTest.php
   ```
   *Hasil*: **1 passed (59 assertions)** 100% Green.

3. **Pengujian Manajemen Alamat & Dasbor (PHPUnit)**:
   ```bash
   php artisan test tests/Feature/CustomerAddressManagementTest.php tests/Feature/CustomerDashboardTest.php
   ```
   *Hasil*: **14 passed (65 assertions)** 100% Green.

4. **Pengujian Regresi Seluruh Suite Aplikasi (PHPUnit)**:
   ```bash
   php artisan test
   ```
   *Hasil*: **194 passed (1034 assertions)** 100% Green tanpa ada regresi pada modul apa pun.

---

## 5. Kesimpulan

Fitur pencarian lokasi peta Leaflet dan tombol deteksi lokasi instan telah aktif pada halaman Checkout dengan antarmuka yang bersih, minimalis, dan konsisten dengan halaman Dasbor Pelanggan, tanpa mengubah struktur backend aplikasi.
