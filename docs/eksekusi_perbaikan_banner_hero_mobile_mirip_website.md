# Dokumentasi Eksekusi: Harmonisasi Banner Hero Mobile agar Mirip Mode Website (Desktop)

**Tanggal Eksekusi:** 5 November 2026  
**Target Komponen:** Banner Hero & Banner Promosi pada Viewport Mobile ($\le 768\text{px}$ dan $\le 576\text{px}$)  
**Halaman Terdampak:**  
1. `resources/views/Pelanggan/home.blade.php` (*Hero Slider Banner Canvas*)  
2. `resources/views/Pelanggan/shop.blade.php` (*Shop Promo Banner*)  
3. `resources/views/Pelanggan/custom_products.blade.php` (*Custom Cake Studio Banner*)  
**Metodologi Desain:** `skill://frontend-design-direction`  

---

## 1. Latar Belakang & Identifikasi Masalah

Sebelum pembaruan ini, terdapat perbedaan drastis antara tampilan banner pada **mode website (desktop)** dengan tampilan pada **mode mobile**:

### A. Halaman Home (`home.blade.php`)
* **Mode Website (Desktop):**  
  Menggunakan kanvas terpadu horizontal (*single unified canvas* `.hero-section`) dengan foto kue pastry di sisi kanan (`width: 58%`), teks di sisi kiri (*left-aligned*), serta lapisan gradien horizontal halus `::before` (*feathered spotlight melt*) yang membaurkan foto kue ke latar belakang teks secara mulus dan mewah.
* **Mode Mobile Lama:**  
  Media query `@media (max-width: 768px)` memaksa susunan kolom bertumpuk (`flex-direction: column !important;`). Foto kue dijadikan kotak terpisah setinggi $210\text{px}$ di bagian atas, sementara teks diletakkan di kotak putih terpisah di bawahnya dengan teks rata tengah (*center-aligned*). Efek gradien dan *spotlight melt* dimatikan total (`display: none !important;`). Akibatnya, banner mobile kehilangan karakter visual toko kue modern dan terlihat seperti dua kotak artikel blog terputus.

### B. Halaman Shop & Custom Products (`shop.blade.php` & `custom_products.blade.php`)
* **Mode Website (Desktop):**  
  Banner promosi horizontal berbentuk lanskap *billboard* dengan foto kue di sisi kanan latar belakang, gradien gelap miring 90°–135° di sisi kiri, badge pill, judul promo tebal, paragraf deskripsi, dan tombol CTA.
* **Mode Mobile Lama:**  
  Ketinggian ditekan menjadi $120\text{px}$ dan teks deskripsi promosi disembunyikan seluruhnya (`display: none !important;`). Hal ini menghilangkan konteks promosi dan membuat visual kue terpotong terlalu sempit.

---

## 2. Solusi Desain yang Diterapkan

Berdasarkan prinsip `frontend-design-direction`, antarmuka seluler diselaraskan agar mengadopsi struktur kanvas yang identik dengan versi website:

```
MODE WEBSITE (DESKTOP)                      MODE MOBILE BARU (MIRIP WEBSITE)
┌──────────────────────────────────────┐    ┌──────────────────────────────────┐
│ [Kanvas Terpadu Horizontal]          │    │ [Kanvas Lanskap Terpadu 215px]   │
│                                      │    │                                  │
│ [Teks Kiri]         [Foto Kue Kanan] │    │ [Teks Kiri]     [Foto Kue Kanan] │
│ • Badge Hijau       • Melebur mulus  │    │ • Badge Hijau   • Melebur mulus  │
│ • Judul Besar         via gradien    │    │ • Judul 17px      via gradien    │
│ • Callout Diskon      spotlight melt │    │ • Callout Diskon  spotlight melt │
│ • Deskripsi                          │    │ • Deskripsi 1-baris              │
│ • Tombol Shop Now                    │    │ • Tombol Shop Now [Dots Kanan]   │
└──────────────────────────────────────┘    └──────────────────────────────────┘
```

1. **Kanvas Lanskap Tunggal Terpadu (*Unified Landscape Canvas*):**
   - Menghapus pemisahan `flex-direction: column`. Kanvas `.hero-section` pada mobile diubah menjadi `display: flex; flex-direction: row; align-items: center; min-height: 215px; max-height: 240px;` dengan sudut lengkung $18\text{px}$.
2. **Foto Kue Full-Height di Sisi Kanan:**
   - `.hero-image` diposisikan absolut di sisi kanan (`width: 54%; height: 100%; top: 0; right: 0;`) dengan `object-fit: cover` dan `object-position: center right`, menampilkan kue beresolusi tinggi yang terang dan menggugah selera.
3. **Mengaktifkan Kembali Gradien Spotlight & Melt di Mobile:**
   - Menghidupkan kembali lapisan `::before` dengan gradien multi-stop:
     ```css
     background: linear-gradient(90deg, #edf2ee 0%, #edf2ee 42%, rgba(237, 242, 238, 0.94) 54%, rgba(237, 242, 238, 0.60) 70%, rgba(237, 242, 238, 0) 100%) !important;
     ```
     Sisi kiri memiliki warna solid penjamin kontras teks WCAG AA, sementara sisi kanan melebur lembut ke dalam foto kue.
4. **Hierarki Tipografi Rata Kiri (*Left-Aligned Hierarchy*):**
   - Mengembalikan teks ke sisi kiri (`width: 66%; align-items: flex-start; text-align: left;`).
   - Badge pill: font $9.5\text{px}$, padding $3\text{px } 9\text{px}$.
   - Judul $H1$: font $17\text{px}$, line-height $1.22$, 2-baris rapi (*line-clamp: 2*).
   - Callout Diskon: font $12\text{px}$ bold dengan aksen oranye/merah.
   - Paragraf Deskripsi: font $11\text{px}$ dengan batas 1 baris ringkas (*line-clamp: 1*) untuk menjaga konteks promosi tanpa memakan ruang vertikal.
   - Tombol CTA: tinggi $30\text{px}$, padding $6\text{px } 14\text{px}$, font $11.5\text{px}$.
5. **Indikator Slider Dots Elegan di Kanan Bawah:**
   - `.hero-slider-dots` diposisikan di sisi kanan bawah kanvas (`bottom: 10px; right: 14px; left: auto;`) dengan latar belakang *frosted glass* semi-transparan (`background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(6px);`). Hal ini menciptakan keseimbangan visual (tombol aksi di kiri, navigasi slide di kanan).
6. **Harmonisasi Banner Shop & Custom Products:**
   - Ketinggian ditingkatkan secara proporsional menjadi `min-height: 145px;` dengan gradien miring 90° (`linear-gradient(90deg, rgba(12, 22, 15, 0.95) 0%, rgba(12, 22, 15, 0.88) 46%, rgba(12, 22, 15, 0.52) 72%, rgba(12, 22, 15, 0.12) 100%)`).
   - Teks deskripsi diaktifkan kembali dengan batas 1 baris ringkas (*line-clamp: 1*), warna putih lembut `rgba(255, 255, 255, 0.82)`.

---

## 3. Rincian Perubahan Kode Sumber

### A. `resources/css/pelanggan_pages.css`
```css
@media (max-width: 768px) {
    .hero-slider-wrapper {
        margin-top: 16px !important;
        margin-bottom: 24px !important;
    }

    .hero-section {
        position: relative !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        min-height: 215px !important;
        height: 215px !important;
        max-height: 240px !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        border-radius: 18px !important;
        background-color: #edf2ee !important;
        text-align: left !important;
    }

    .hero-section::before {
        content: '' !important;
        display: block !important;
        position: absolute !important;
        inset: 0 !important;
        z-index: 2 !important;
        background: linear-gradient(90deg, #edf2ee 0%, #edf2ee 42%, rgba(237, 242, 238, 0.94) 54%, rgba(237, 242, 238, 0.60) 70%, rgba(237, 242, 238, 0) 100%) !important;
        pointer-events: none !important;
    }

    .hero-text,
    .hero-content {
        position: relative !important;
        z-index: 3 !important;
        width: 66% !important;
        max-width: 66% !important;
        height: 100% !important;
        padding: 16px 14px 16px 18px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: flex-start !important;
        text-align: left !important;
        box-sizing: border-box !important;
    }

    .hero-badge-pill {
        font-size: 9.5px !important;
        font-weight: 700 !important;
        padding: 3px 9px !important;
        margin-bottom: 6px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        letter-spacing: 0.4px !important;
    }

    .hero-text h1 {
        font-size: 17px !important;
        font-weight: 700 !important;
        line-height: 1.22 !important;
        letter-spacing: -0.3px !important;
        margin-bottom: 5px !important;
        color: #1a1a1a !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    .hero-sale-callout {
        font-size: 12px !important;
        font-weight: 700 !important;
        margin-bottom: 6px !important;
        justify-content: flex-start !important;
        gap: 4px !important;
    }

    .hero-text p.desc {
        font-size: 11px !important;
        line-height: 1.35 !important;
        color: #64748b !important;
        margin: 0 0 8px 0 !important;
        max-width: 100% !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 1 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    .hero-btn {
        padding: 6px 14px !important;
        font-size: 11.5px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        box-shadow: 0 3px 10px rgba(0, 178, 7, 0.22) !important;
    }

    .hero-image,
    .hero-showcase-bg {
        position: absolute !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: auto !important;
        width: 54% !important;
        height: 100% !important;
        min-height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 1 !important;
        overflow: hidden !important;
        display: block !important;
    }

    .hero-image-frame {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        display: block !important;
        overflow: hidden !important;
    }

    .hero-image img,
    .hero-image-frame img {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center right !important;
        display: block !important;
        max-height: none !important;
    }

    .hero-slider-arrow {
        display: none !important;
    }

    .hero-slider-dots {
        position: absolute !important;
        bottom: 10px !important;
        right: 14px !important;
        left: auto !important;
        transform: none !important;
        padding: 3px 8px !important;
        z-index: 4 !important;
        background: rgba(255, 255, 255, 0.75) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        border-radius: 20px !important;
        display: flex !important;
        align-items: center !important;
        gap: 3px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
    }

    .hero-dot {
        width: 5px !important;
        height: 5px !important;
        margin: 0 !important;
    }

    .hero-dot.active {
        width: 14px !important;
        border-radius: 4px !important;
    }
}
```

### B. Penyelarasan Banner Shop & Custom Products (`pelanggan_pages.css`, `pelanggan.css`, `pelanggan_custom.css`)
```css
/* Mobile Promo Banner Slim & Compact - Mirip Mode Website */
.shop-promo-banner {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    padding: 14px 18px !important;
    min-height: 145px !important;
    margin-top: 10px !important;
    margin-bottom: 14px !important;
    border-radius: 14px !important;
    background-color: #0d1710 !important;
    background-position: center right !important;
    background-size: cover !important;
}
.shop-promo-banner::before {
    background: linear-gradient(90deg, rgba(12, 22, 15, 0.95) 0%, rgba(12, 22, 15, 0.88) 46%, rgba(12, 22, 15, 0.52) 72%, rgba(12, 22, 15, 0.12) 100%) !important;
}
.shop-promo-banner .spb-content {
    max-width: 68% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    text-align: left !important;
    z-index: 2 !important;
}
.spb-pill-badge {
    font-size: 9.5px !important;
    padding: 2.5px 9px !important;
    margin-bottom: 5px !important;
    gap: 5px !important;
    letter-spacing: 0.4px !important;
}
.spb-title {
    font-size: 17px !important;
    font-weight: 700 !important;
    line-height: 1.22 !important;
    letter-spacing: -0.3px !important;
    margin-bottom: 4px !important;
}
.spb-desc {
    display: -webkit-box !important;
    -webkit-line-clamp: 1 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    font-size: 11px !important;
    line-height: 1.35 !important;
    color: rgba(255, 255, 255, 0.82) !important;
    margin-bottom: 7px !important;
}
.spb-btn {
    padding: 6px 14px !important;
    font-size: 11.5px !important;
    gap: 5px !important;
    margin-top: 0 !important;
    box-shadow: 0 4px 12px rgba(0, 178, 7, 0.25) !important;
}
```

### C. Sinkronisasi Stylesheet Terkait
Aturan CSS baru disinkronkan secara konsisten ke 5 berkas pendukung:
1. `public/css/css_home.css`
2. `public/css/home.css`
3. `resources/css/pelanggan_custom.css`
4. `resources/css/pelanggan.css`
5. `public/css/pelanggan.css`

---

## 4. Hasil Kompilasi & Pengujian

### A. Kompilasi Aset Produksi Vite 8
```bash
npm run build
```
*Hasil:* Berhasil (`✓ built in 761ms`). Bundle baru `public/build/assets/pelanggan_pages-_YSt1_1u.css` (184.62 kB) ter-generate tanpa peringatan error.

### B. Suite Pengujian Otomatis Laravel (PHPUnit)
```bash
php artisan test
```
*Hasil:* **221 passed (1195 assertions)** dalam durasi 51.65 detik.  
Status: **100% Lulus (0 failure, 0 error)**.

### C. Integritas Basis Data MySQL `pa-zyhcorner`
* `Users`: **16 baris** (utuh)
* `Products`: **13 baris** (utuh)
* `Orders`: **25 baris** (utuh)
* Direktori `public/uploads/`: Bersih dari berkas temporer pengujian.

---

## 5. Kesimpulan & Manfaat Pengguna (UX)

1. **Konsistensi Brand Identity Antara Desktop & Mobile:** Banner di ponsel kini tidak lagi terasa seperti "versi terpotong" atau kartu blog kaku, melainkan kanvas lanskap profesional berkesinambungan layaknya versi desktop.
2. **Keterbacaan & Appetite Appeal Tinggi:** Foto kue di sisi kanan tampil jernih, terang, dan menggugah selera tanpa terhalang warna hitam pekat, sementara seluruh informasi teks di sisi kiri terjamin terbaca tajam berkat gradien melt pelindung.
3. **Efisiensi Ruang Layar Ponsel:** Ketinggian kanvas hero ($215\text{px}$) dan promo banner ($145\text{px}$) sangat pas di layar seluler, memastikan katalog produk tetap terlihat tanpa perlu menggulir jauh ke bawah.
