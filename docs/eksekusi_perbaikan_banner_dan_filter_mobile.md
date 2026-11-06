# Dokumentasi Perubahan Kode: Perbaikan Desain Banner & Tata Letak Tombol Filter Mobile

**Tanggal Eksekusi:** 5 November 2026  
**Target Komponen:** Banner Promo (`.shop-promo-banner`) & Toolbar Filter (`.filter-toolbar`) pada Viewport Mobile  
**Halaman Terdampak:**  
1. `resources/views/Pelanggan/shop.blade.php` (Halaman Katalog Produk / Shop)  
2. `resources/views/Pelanggan/custom_products.blade.php` (Halaman Katalog Produk Kustom)  
**Lingkungan:** Windows 10 x64, Laragon, PHP 8.2+, MySQL 8.4 (`pa-zyhcorner`), Laravel 10, Vite 8  

---

## 1. Latar Belakang & Identifikasi Masalah

Berdasarkan pemeriksaan visual terhadap kedua tangkapan layar responsif mobile (**Gambar 1: Halaman Shop** dan **Gambar 2: Halaman Custom Products**):

1. **Masalah Desain Banner Promo (`.shop-promo-banner`):**
   - **Tinggi Terlalu Mendominasi:** Banner memiliki tinggi mencapai $\approx 260\text{px} - 280\text{px}$ pada layar mobile akibat `min-height: 200px`, padding vertikal besar ($28\text{px} \times 2$), dan teks deskripsi yang panjang. Banner ini memakan hampir 40% area layar ponsel sebelum pengguna melakukan *scroll*.
   - **Kepadatan Teks Berlebihan:** Teks deskripsi 3 baris (*"Wujudkan kue impian untuk perayaan spesial Anda. Bebas tentukan tema, desain, rasa, dan ukuran."*) membuat banner terasa berat dan padat.
   - **Visual Kue Tertutup:** Gradien pekat di latar belakang menutupi foto kue, sehingga daya tarik visual produk (*appetite appeal*) berkurang drastis.

2. **Masalah Tata Letak Tombol Filter (`.filter-toolbar`):**
   - **Pecah Menjadi 4 Baris Bertumpuk (*4-Row Stacking*):** Keempat tombol filter (`Semua Kategori`, `Semua Harga`, `Urutkan: Terbaru`, dan `Tampilkan: 16`) memiliki lebar masing-masing $\approx 140\text{px} - 165\text{px}$. Pada kontainer mobile yang hanya selebar $\approx 320\text{px} - 340\text{px}$, tombol kedua tidak muat berdampingan dan otomatis terdorong ke bawah, menciptakan 4 baris filter bertumpuk.
   - **Menyita Ruang Layar Secara Kritis:** 4 baris filter memakan ruang vertikal $\approx 200\text{px}$.
   - **Katalog Produk Tersembunyi (*Below The Fold*):** Akumulasi ketinggian header ($\approx 110\text{px}$), banner ($\approx 260\text{px}$), dan 4 baris filter ($\approx 200\text{px}$) melebihi $600\text{px}$, sehingga pengguna tidak dapat melihat satu pun produk tanpa melakukan *scroll* ke bawah.
   - **Overflow pada Popover Rentang Harga:** `.price-popover-card` memiliki lebar default $320\text{px}$ dengan posisi absolut di bawah tombol, menyebabkan popover meluap keluar dari batas layar ponsel ke arah kanan.

---

## 2. Solusi Desain yang Diterapkan

1. **Banner Promo Ramping & Menarik (*Compact Mobile Banner*):**
   - Mengurangi tinggi banner menjadi $\approx 120\text{px}$ dan padding menjadi `16px 18px`.
   - Mengatur ukuran judul `h2.spb-title` menjadi $17\text{px}$ dengan font bold.
   - Menyembunyikan paragraf deskripsi panjang di mobile (`.spb-desc { display: none !important; }`) demi efisiensi visual layar.
   - Menyesuaikan tombol CTA menjadi lebih proporsional (`padding: 6px 16px; font-size: 11.5px;`).
   - Memperbarui gradien ambient agar foto kue di sisi kanan terlihat terang, lezat, dan menggugah selera.

2. **Tata Letak Filter 1 Baris Horizontal (*Horizontal Scrollable Pill Track*):**
   - Mengubah `.filter-toolbar` pada mobile ($\le 576\text{px}$) menjadi 1 baris fleksibel dengan `overflow-x: auto` dan scrollbar bawaan disembunyikan (`scrollbar-width: none`).
   - Seluruh tombol filter (`.filter-pill-trigger`) berbaris rapi dalam satu baris dengan ketinggian hanya $36\text{px}$ (menghemat ruang vertikal dari $\approx 200\text{px}$ menjadi hanya $40\text{px}$).

3. **Interaksi Native Mobile Bottom Sheet dengan Backdrop:**
   - Mengubah tampilan dropdown pilihan (`.modern-filter-dropdown-menu` dan `.price-popover-card`) pada mobile menjadi *bottom sheet* yang meluncur mulus dari bagian bawah layar ponsel (`position: fixed; bottom: 0;`).
   - Menambahkan lapisan latar belakang semi-transparan (*backdrop blur overlay*) `rgba(15, 23, 42, 0.45)`.
   - Menambahkan header *bottom sheet* (`.mobile-sheet-header`) yang dilengkapi tombol tutup silang (`.sheet-close-btn`).
   - Menyempurnakan logika event click di JavaScript sehingga mengetuk area *backdrop* atau tombol silang akan langsung menutup *bottom sheet* secara mulus.

---

## 3. Rincian Perubahan Kode Sumber

### A. Template Blade Pelanggan

#### 1. `resources/views/Pelanggan/shop.blade.php`
- Menambahkan komponen header mobile `.mobile-sheet-header` beserta `.sheet-close-btn` pada dropdown Kategori:
  ```html
  <div class="modern-filter-dropdown-menu">
      <div class="mobile-sheet-header">
          <span class="mobile-sheet-title"><i class="fa-solid fa-layer-group text-success me-2"></i> Pilih Kategori</span>
          <button type="button" class="sheet-close-btn" onclick="event.stopPropagation(); this.closest('.filter-dropdown-container').classList.remove('open');" title="Tutup">
              <i class="fa-solid fa-xmark"></i>
          </button>
      </div>
      <ul class="modern-dropdown-list">
          ...
      </ul>
  </div>
  ```
- Menambahkan tombol tutup pada `.price-popover-header`:
  ```html
  <div class="price-popover-header">
      <div class="price-popover-title">
          <i class="fa-solid fa-sliders"></i>
          <span>Atur Rentang Harga</span>
      </div>
      <button type="button" class="sheet-close-btn" onclick="event.stopPropagation(); document.getElementById('priceDropdownContainer').classList.remove('open');" title="Tutup">
          <i class="fa-solid fa-xmark"></i>
      </button>
  </div>
  ```
- Menambahkan `.mobile-sheet-header` pada dropdown **Urutkan Produk** dan **Jumlah Tampilan**.

#### 2. `resources/views/Pelanggan/custom_products.blade.php`
- Menambahkan struktur header mobile `.mobile-sheet-header` dan tombol tutup `.sheet-close-btn` yang identik pada dropdown Kategori, Rentang Harga, Urutkan Produk, dan Jumlah Tampilan.

---

### B. JavaScript Interaksi Pelanggan

#### `resources/js/pelanggan_pages.js` (Baris 2100–2116)
Memperbaiki deteksi klik di luar (*outside click / backdrop click*):
```javascript
    // Modern Dropdown Popovers Toggle & Outside Click
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.filter-pill-trigger');
        if (trigger) {
            const container = trigger.closest('.filter-dropdown-container');
            const wasOpen = container?.classList.contains('open');
            document.querySelectorAll('.filter-dropdown-container.open').forEach(c => {
                if (c !== container) c.classList.remove('open');
            });
            if (container) {
                container.classList.toggle('open', !wasOpen);
            }
            return;
        }
        if (!e.target.closest('.modern-filter-dropdown-menu') && !e.target.closest('.price-popover-card')) {
            document.querySelectorAll('.filter-dropdown-container.open').forEach(c => c.classList.remove('open'));
        }
    });
```

---

### C. Lembar Gaya CSS Terpusat & Sinkronisasi

Ditambahkan ke:
- `resources/css/pelanggan_pages.css`
- `public/css/pelanggan.css`
- `resources/css/pelanggan.css`
- `resources/css/pelanggan_custom.css`

#### 1. Aturan Dasar Desktop:
```css
.mobile-sheet-header {
    display: none;
}

.sheet-close-btn {
    display: none;
    background: #f1f5f9;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.sheet-close-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
```

#### 2. Aturan Responsif Mobile (`@media (max-width: 576px)` dan `@media (max-width: 480px)`):
```css
/* Mobile Promo Banner Slim & Compact */
.shop-promo-banner {
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    padding: 16px 18px !important;
    min-height: 120px !important;
    margin-top: 10px !important;
    margin-bottom: 14px !important;
    border-radius: 14px !important;
    background-color: #0d1710 !important;
}
.shop-promo-banner::before {
    background: linear-gradient(135deg, rgba(12, 22, 15, 0.94) 0%, rgba(12, 22, 15, 0.72) 55%, rgba(12, 22, 15, 0.25) 100%) !important;
}
.shop-promo-banner .spb-content {
    max-width: 100% !important;
}
.spb-pill-badge {
    font-size: 9.5px !important;
    padding: 3px 10px !important;
    margin-bottom: 6px !important;
    gap: 5px !important;
    letter-spacing: 0.4px !important;
}
.spb-pill-badge i {
    font-size: 9px !important;
}
.shop-promo-banner h2,
.spb-content h2,
.spb-title {
    font-size: 17px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    letter-spacing: -0.3px !important;
    margin-bottom: 4px !important;
}
.shop-promo-banner p,
.spb-content p,
.spb-desc {
    display: none !important; /* Deskripsi disembunyikan untuk banner mobile ramping */
}
.spb-btn {
    padding: 6px 16px !important;
    font-size: 11.5px !important;
    gap: 6px !important;
    margin-top: 6px !important;
    box-shadow: 0 4px 12px rgba(0, 178, 7, 0.25) !important;
}
.single-banner-card {
    padding: 16px 18px !important;
    min-height: 130px !important;
    border-radius: 14px !important;
}
.single-banner-card h2 {
    font-size: 18px !important;
    margin-bottom: 8px !important;
}
.banner-grid-card {
    min-height: 150px !important;
    padding: 14px 16px !important;
    border-radius: 14px !important;
}
.banner-grid-card h4,
.banner-grid-card h5 {
    font-size: 15px !important;
    margin-bottom: 6px !important;
}

/* Mobile Filter Toolbar: 1-Row Horizontal Scrollable Track */
.filter-toolbar {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: flex-start !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    gap: 8px !important;
    margin-top: 0 !important;
    margin-bottom: 14px !important;
    padding: 4px 2px 8px 2px !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
    width: 100% !important;
}
.filter-toolbar::-webkit-scrollbar {
    display: none !important;
}
.filter-group {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    gap: 8px !important;
    flex-shrink: 0 !important;
}
.filter-dropdown-container {
    position: relative !important;
    flex-shrink: 0 !important;
}
.filter-pill-trigger {
    height: 36px !important;
    padding: 0 13px !important;
    font-size: 12px !important;
    gap: 6px !important;
    border-radius: 50px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}
.filter-pill-trigger .trigger-icon {
    font-size: 11px !important;
}
.filter-pill-trigger .trigger-chevron {
    font-size: 9.5px !important;
    margin-left: 2px !important;
}

/* Mobile Bottom Sheet for Category & Sort Dropdowns */
.filter-dropdown-container.open::before {
    content: '' !important;
    position: fixed !important;
    inset: 0 !important;
    background: rgba(15, 23, 42, 0.45) !important;
    backdrop-filter: blur(2px) !important;
    -webkit-backdrop-filter: blur(2px) !important;
    z-index: 1999 !important;
    pointer-events: auto !important;
}
.modern-filter-dropdown-menu {
    position: fixed !important;
    top: auto !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    border-radius: 20px 20px 0 0 !important;
    border: none !important;
    box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.2) !important;
    padding: 16px 16px calc(16px + env(safe-area-inset-bottom, 0px)) 16px !important;
    z-index: 2000 !important;
    transform: translateY(100%) !important;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.filter-dropdown-container.open .modern-filter-dropdown-menu {
    transform: translateY(0) !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.modern-dropdown-list {
    max-height: 55vh !important;
}
.modern-dropdown-item {
    padding: 12px 14px !important;
    font-size: 14px !important;
    border-radius: 12px !important;
}

/* Mobile Bottom Sheet for Price Range Popover */
.price-popover-card {
    position: fixed !important;
    top: auto !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 20px 20px 0 0 !important;
    border: none !important;
    box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.2) !important;
    padding: 20px 16px calc(20px + env(safe-area-inset-bottom, 0px)) 16px !important;
    z-index: 2000 !important;
    transform: translateY(100%) !important;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.filter-dropdown-container.open .price-popover-card {
    transform: translateY(0) !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.price-popover-header {
    margin-bottom: 12px !important;
}
.price-presets-grid {
    gap: 6px !important;
}
.price-preset-chip {
    font-size: 11.5px !important;
    padding: 7px 10px !important;
}
.mobile-sheet-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding-bottom: 12px !important;
    margin-bottom: 12px !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.mobile-sheet-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
}
.sheet-close-btn {
    display: inline-flex !important;
}
```

---

## 4. Hasil Kompilasi & Verifikasi Pengujian

### A. Kompilasi Aset Vite 8
```bash
npm run build
```
- Status: **Berhasil (✓ built in 19.32s)**
- Aset terkompilasi:
  - `public/build/assets/pelanggan_pages-CrW3uHJY.css` (180.54 kB │ gzip: 30.33 kB)
  - `public/build/assets/pelanggan_pages-WRA3-Y-V.js` (74.54 kB │ gzip: 15.67 kB)

### B. Suite Pengujian Otomatis Laravel (PHPUnit)
```bash
php artisan test
```
- Hasil: **221 passed (1195 assertions)**
- Status: **100% Lulus (0 kegagalan, 0 regresi)**
- Durasi: **83.97s**

### C. Integritas Database MySQL `pa-zyhcorner`
- `Users`: **16 baris** (utuh)
- `Products`: **13 baris** (utuh)
- `Orders`: **25 baris** (utuh)
- Direktori `public/uploads/` bersih dari berkas dummy pengujian.

---

## 5. Kesimpulan & Dampak Pengalaman Pengguna (UX)

Dengan diterapkannya pembaruan ini:
1. **Penghematan Ruang Vertikal $\approx 305\text{px}$:**
   - Banner berkurang $\approx 145\text{px}$.
   - Filter toolbar berkurang $\approx 160\text{px}$ (dari 4 baris menjadi 1 baris).
2. **Katalog Produk Langsung Tampak (*Above The Fold*):** Pengguna ponsel kini dapat langsung melihat kartu produk dan harga segera setelah halaman dimuat tanpa harus melakukan *scroll*.
3. **Pengalaman Mobile Native:** Interaksi filter kategori dan harga kini menggunakan pola *bottom sheet* standar industri seluler dengan *backdrop blur* dan tombol tutup, memberikan kenyamanan sentuhan jempol (*thumb-friendly*) yang optimal.
