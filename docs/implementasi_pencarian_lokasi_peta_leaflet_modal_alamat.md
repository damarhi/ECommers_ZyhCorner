# Dokumentasi Implementasi Pencarian Lokasi Peta Leaflet pada Modal Alamat Dasbor

## 1. Ringkasan Fitur

Fitur pencarian lokasi peta Leaflet (*Map Geocoding Search Bar*) telah berhasil diintegrasikan pada modal **Tambah Alamat Pengiriman** (`#modalAddAddress`) dan **Edit Alamat Pengiriman** (`#modalEditAddress`) di dasbor pelanggan (`resources/views/Pelanggan/dashboard.blade.php`).

Fitur ini memudahkan pelanggan untuk mencari nama jalan, perumahan, gedung, atau fasilitas umum langsung di atas peta interaktif. Ketika lokasi dipilih, peta secara otomatis melakukan animasi pergeseran (*smooth flyTo*), memindahkan pin marker ke titik lokasi yang dipilih, dan memperbarui nilai input koordinat `latitude` dan `longitude` secara instan.

---

## 2. Arsitektur & Prinsip Desain

1. **100% Client-Side (Zero Backend Change)**:
   - Tidak ada penambahan atau pengubahan kode backend (Controller, Model, atau Rute API Laravel).
   - Menggunakan layanan publik **OpenStreetMap (OSM) Nominatim Geocoding API** (`https://nominatim.openstreetmap.org/search?format=json&q=...&countrycodes=id&limit=5`).
   - Koordinat yang terpilih tetap disimpan melalui input hidden `#editAddrLatitude`/`#editAddrLongitude` dan `#newAddrLatitude`/`#newAddrLongitude` yang telah terintegrasi dengan endpoint `POST /customer/addresses` dan `PUT /customer/addresses/{id}`.

2. **Kepatuhan Desain Minimalis & Flat (`/skill:frontend-design-direction`)**:
   - Menghindari kartu di dalam kartu (*cards inside cards*).
   - Menggunakan palet token konsisten Zyh Corner: hijau utama `#00b207`, hover `#009406`, teks gelap `#0f172a`, teks sekunder `#64748b`, border tipis `#cbd5e1` / `#e2e8f0`, dan latar bersih `#ffffff` / `#f8fafc`.
   - Memiliki transisi halus dan micro-interaction ramah pengguna (tombol hapus pencarian instan `×`, dropdown hasil mengambang dengan elevasi lembut, dan animasi pergeseran peta Leaflet).

3. **Manajemen *Content States***:
   - **Idle State**: Kotak input pencarian ramping setinggi `36px` dengan ikon loop halus dan tombol aksi "Cari" hijau solid.
   - **Loading State**: Spinner animasi `<i class="fa-solid fa-spinner fa-spin"></i>` disertai teks informatif *"Mencari titik lokasi di peta..."*.
   - **Empty State**: Pesan ramah dan sopan jika lokasi tidak ditemukan *"Lokasi tidak ditemukan. Coba gunakan nama jalan atau daerah yang lebih umum."*.
   - **Success State**: Menampilkan daftar hingga 5 rekomendasi lokasi dengan nama tempat tebal dan detail wilayah sekunder.
   - **Click-Outside Dismiss**: Dropdown otomatis tertutup saat pengguna mengeklik area luar pencarian atau berinteraksi langsung dengan kanvas peta.

---

## 3. Rincian Perubahan Kode (Code Changes)

### A. Modifikasi `resources/views/Pelanggan/dashboard.blade.php`

Menambahkan komponen search bar minimalis tepat di atas kontainer `.modal-map-wrapper` pada Group 3:
1. **Pada `modalEditAddress`** (baris 474–493):
   ```blade
   <!-- Map Geocoding Search Bar -->
   <div class="map-search-container" id="editAddrMapSearchContainer">
       <div class="map-search-bar">
           <div class="map-search-input-wrap">
               <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
               <input type="text" id="editAddrMapSearchInput" class="map-search-input" 
                      placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                      autocomplete="off"
                      onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('edit'); }"
                      oninput="toggleClearSearchBtn('edit')">
               <button type="button" class="btn-clear-map-search" id="editAddrClearSearchBtn" onclick="clearMapSearch('edit')" style="display: none;" title="Hapus pencarian">
                   <i class="fa-solid fa-xmark"></i>
               </button>
           </div>
           <button type="button" class="btn-search-map" onclick="searchLocationOnMap('edit')">
               Cari
           </button>
       </div>
       <div id="editAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
   </div>
   ```

2. **Pada `modalAddAddress`** (baris 628–647):
   ```blade
   <!-- Map Geocoding Search Bar -->
   <div class="map-search-container" id="newAddrMapSearchContainer">
       <div class="map-search-bar">
           <div class="map-search-input-wrap">
               <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
               <input type="text" id="newAddrMapSearchInput" class="map-search-input" 
                      placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                      autocomplete="off"
                      onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('add'); }"
                      oninput="toggleClearSearchBtn('add')">
               <button type="button" class="btn-clear-map-search" id="newAddrClearSearchBtn" onclick="clearMapSearch('add')" style="display: none;" title="Hapus pencarian">
                   <i class="fa-solid fa-xmark"></i>
               </button>
           </div>
           <button type="button" class="btn-search-map" onclick="searchLocationOnMap('add')">
               Cari
           </button>
       </div>
       <div id="newAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
   </div>
   ```

---

### B. Modifikasi `resources/css/dashboard.css`

Menambahkan aturan CSS minimalis dan responsif untuk komponen pencarian lokasi peta:
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

### C. Modifikasi `resources/js/dashboard.js`

1. **Implementasi Fungsi Pencarian & Interaksi Geocoding**:
   - `toggleClearSearchBtn(type)`: Mengatur visibilitas tombol hapus pencarian (`×`).
   - `clearMapSearch(type)`: Membersihkan input teks pencarian dan menutup dropdown hasil.
   - `searchLocationOnMap(type)`: Memanggil API OSM Nominatim dengan parameter bahasa Indonesia dan prioritas wilayah Indonesia.
   - `selectMapSearchResult(type, lat, lon, title)`: Menggeser peta dengan `flyTo`, memperbarui posisi pin marker, mengisi input hidden koordinat, dan memperbarui label status koordinat.

2. **Click-Outside Dismiss Listener**:
   Menutup dropdown hasil pencarian jika pengguna mengeklik di luar kontainer pencarian.

3. **Reset State saat Buka/Tutup Modal**:
   Memanggil `window.clearMapSearch('edit')` pada `openEditAddress` dan `closeEditAddress`, serta `window.clearMapSearch('add')` pada `openAddAddressModal` dan `closeAddAddressModal`.

---

## 4. Hasil Verifikasi & Validasi Pengujian

1. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build
   ```
   *Hasil*: Berhasil dikompilasi dalam 527ms tanpa peringatan error (`dashboard-B0gMHvTy.js` dan `dashboard-0LVHmhQX.css`).

2. **Pengujian Regresi Fitur Dasbor (PHPUnit)**:
   ```bash
   php artisan test tests/Feature/CustomerDashboardTest.php
   ```
   *Hasil*: 6 tests passed (33 assertions) 100% Green.

3. **Pengujian Seluruh Suite Aplikasi (PHPUnit)**:
   ```bash
   php artisan test
   ```
   *Hasil*: **194 passed (1034 assertions)** 100% Green tanpa ada kerusakan atau regresi pada modul lain.

---

## 5. Kesimpulan

Pencarian lokasi di dalam peta Leaflet modal alamat telah aktif, berkinerja cepat tanpa kuota berbayar, memiliki antarmuka yang bersih dan flat menyatu dengan desain Zyh Corner, dan tidak mengubah logika backend server.
