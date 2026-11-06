# Dokumentasi Eksekusi: Perbaikan Tata Letak Mobile Halaman Detail Pesanan Pelanggan (`/order-history/{id}`)

## 1. Ringkasan Eksekusi

Berdasarkan audit layout mobile menggunakan **Headless Chrome CDP DOM Geometry Measurement** dan tangkapan layar antarmuka pengguna (`local://image-3f232dce59f9d996.webp`), tata letak halaman Detail Pesanan (`/order-history/{id}`, berkas: `resources/views/Pelanggan/order_detail.blade.php`) dioptimalkan untuk perangkat mobile (`<= 768px`) mengikuti standar **/skill:frontend-design-direction** dengan **menjamin tampilan Desktop/Website (`> 768px` / `1440×900`) tetap 100% identik tanpa perubahan**.

---

## 2. Masalah Antarmuka Mobile Sebelumnya (*Baseline*)

| Komponen | Geometri Awal (375×812) | Masalah Visual & UX |
| :--- | :--- | :--- |
| **Tabel Rincian Produk (`.products-table`)** | Lebar `494px` di kontainer `349px` (`tableHorizontalOverflow = 145px`) | Tabel 4 kolom memaksa scroll horizontal. Judul produk terhimpit selebar `53px` sehingga teks `"Black Forest Cake Classic"` terpotong menjadi 4 baris vertikal (*gepeng*). Subtotal tersembunyi di luar layar. |
| **Header & Baris Aksi (`.details-header-flex`)** | Tinggi `125px`, tombol stagger | Tombol `Lacak Pesanan` (`136×36px`) dan `Batalkan Pesanan` (`153×34px`) tingginya berbeda 2px, memiliki `margin-left: auto`, dan terdorong ke kanan secara canggung. |
| **3 Kartu Ringkasan (`.summary-cards-row`)** | 3 kartu memakan tinggi `774px` | Jarak dan padding vertikal terlalu renggang, padding kolom negatif menimbulkan batas tidak rata di tepi layar ponsel. |
| **Header Stepper Tracking** | Tinggi `113px`, 3 baris | Judul, live status chip, dan estimasi tiba bertumpuk 3 baris terpisah karena padding internal kartu terlalu tebal (`22px 24px`). |

---

## 3. Solusi & Perubahan yang Diterapkan

### A. Transformasi Tabel Menjadi Mobile Product Cards (*Game Changer*)
* Pada desktop (`> 768px`), tabel 4 kolom `.products-table` tetap tampil normal via `.desktop-products-table-wrap { display: block; }`.
* Pada layar mobile (`<= 768px`), `.desktop-products-table-wrap` disembunyikan (`display: none !important`), digantikan dengan format **`.mobile-products-list`**:
  * **Thumbnail:** Wadah gambar `60×60px` dengan sudut membulat `8px`, border tipis dan `object-fit: cover`.
  * **Judul Produk:** Diberikan ruang penuh selebar **`223px`** dalam 1 baris rapi tanpa kata terputus.
  * **Pill Tulisan Kue:** Badge kustom jika pembeli meminta tulisan khusus pada kue.
  * **Baris Bawah Receipt:** Kiri menampilkan badge kuantitas dan harga satuan (`x3 @ Rp. 100.000`), kanan menampilkan Subtotal tebal (`Rp. 300.000`).
* **Hasil:** `tableHorizontalOverflow` turun dari **`145px`** menjadi **`0px`** (*zero overflow*).

### B. Header & Tombol Aksi Sejajar Simetris
* Judul `Pesanan #IZYJR6` dan badge status sejajar rapi dengan `justify-content: space-between`.
* Tombol aksi (`Lacak Pesanan`, `Batalkan Pesanan`, atau `Bayar Sekarang`) dibuat berdampingan penuh (`flex: 1 1 calc(50% - 4px)`), tinggi seragam identik **`38px`**, sudut membulat `8px`, dan teks rata tengah.

### C. Perampingan 3 Kartu Ringkasan
* Padding internal kartu disederhanakan dari `18px 16px` menjadi `14px 14px`.
* Margin row diatur `0` dengan `row-gap: 12px`, mengeliminasi offset margin negatif Bootstrap pada mobile.
* Kartu alamat, kurir, dan pembayaran tampil padat dan seimbang.

### D. Optimasi Header Tracking & Garis Stepper
* Header pelacakan dibuat ringkas dengan padding internal `16px 14px`.
* Garis penghubung vertikal (`.step-trail-line`) dihitung presisi `height: calc(100% + 16px)` menghubungkan node lingkaran stepper secara rapi.

---

## 4. Daftar Berkas & Kode yang Diubah

### 1. `resources/views/Pelanggan/order_detail.blade.php`
* **Penambahan CSS Seam Desktop/Mobile & `@media (max-width: 768px)`:**
  ```css
  /* Desktop / Mobile Products Display Seam */
  .desktop-products-table-wrap {
      display: block;
  }
  .mobile-products-list {
      display: none;
  }
  .mobile-product-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }
  .mobile-product-card-top {
      display: flex;
      align-items: flex-start;
      gap: 12px;
  }
  .mobile-product-img-wrap {
      width: 60px;
      height: 60px;
      border-radius: 8px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid #e2e8f0;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2px;
  }
  .mobile-product-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 6px;
  }
  .mobile-product-details {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
  }
  .mobile-product-title {
      font-size: 14px;
      font-weight: 600;
      color: #0f172a;
      line-height: 1.35;
      word-break: break-word;
      overflow-wrap: anywhere;
  }
  .mobile-custom-text-pill {
      margin-top: 3px;
      align-self: flex-start;
      font-size: 11px;
      padding: 2px 7px;
  }
  .mobile-product-card-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding-top: 8px;
      border-top: 1px dashed #f1f5f9;
      flex-wrap: wrap;
  }
  .mobile-product-qty-price {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12.5px;
      color: #475569;
  }
  .mobile-qty-pill {
      background: #f1f5f9;
      color: #0f172a;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 12px;
  }
  .mobile-unit-price {
      color: #64748b;
      font-size: 12px;
  }
  .mobile-product-subtotal-wrap {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin-left: auto;
  }
  .mobile-subtotal-label {
      font-size: 11.5px;
      color: #64748b;
  }
  .mobile-subtotal-val {
      font-size: 14px;
      font-weight: 700;
      color: #00b207;
  }

  @media (max-width: 768px) {
      .dashboard-wrapper {
          margin: 16px auto 32px;
          padding: 0 12px;
      }
      .order-top-nav-bar {
          margin-bottom: 12px;
      }
      .details-header-flex {
          flex-direction: column;
          align-items: stretch;
          gap: 12px;
          margin-bottom: 16px;
          padding-bottom: 14px;
      }
      .details-title-meta {
          width: 100%;
          gap: 6px;
      }
      .details-title-row {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 100%;
          gap: 8px;
          flex-wrap: wrap;
      }
      .details-title {
          font-size: 18px;
          letter-spacing: -0.2px;
      }
      .order-status-pill {
          font-size: 11.5px;
          padding: 3px 10px;
      }
      .details-meta-sub {
          font-size: 12px;
          color: #64748b;
          gap: 6px;
      }
      .details-header-actions {
          width: 100%;
          margin-left: 0;
      }
      .header-actions-group {
          display: flex;
          width: 100%;
          margin-left: 0;
          gap: 8px;
          flex-wrap: wrap;
      }
      .header-actions-group > * {
          flex: 1 1 calc(50% - 4px);
          min-width: 130px;
          height: 38px;
          padding: 0 12px;
          font-size: 12.5px;
          text-align: center;
          justify-content: center;
          border-radius: 8px;
          box-sizing: border-box;
          margin: 0 !important;
      }
      .header-actions-group > *:only-child {
          flex: 1 1 100%;
      }
      .summary-cards-row {
          margin-left: 0;
          margin-right: 0;
          margin-bottom: 16px;
          row-gap: 12px;
      }
      .summary-cards-row > [class*="col-"] {
          padding-left: 0;
          padding-right: 0;
          margin-bottom: 0 !important;
      }
      .summary-card {
          padding: 14px 14px;
          border-radius: 10px;
      }
      .summary-card-title {
          font-size: 11.5px;
          margin-bottom: 10px;
          padding-bottom: 6px;
      }
      .summary-card-address {
          font-size: 13px;
          line-height: 1.45;
          margin-bottom: 10px;
      }
      .summary-card-footer {
          padding-top: 10px;
          margin-top: 10px;
      }
      .summary-calc-row {
          padding-bottom: 6px;
          font-size: 12.5px;
      }
      .summary-card-footer.summary-total-footer {
          padding: 8px 12px;
          margin-top: 8px;
      }
      .tracking-card {
          padding: 16px 14px;
          margin-bottom: 16px;
          border-radius: 10px;
      }
      .tracking-card-header {
          flex-direction: column;
          align-items: flex-start;
          gap: 8px;
          margin-bottom: 16px;
          padding-bottom: 12px;
      }
      .tracking-title-wrap {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 100%;
          gap: 8px;
          flex-wrap: wrap;
      }
      .tracking-title {
          font-size: 14px;
      }
      .tracking-est-pill {
          font-size: 12px;
          padding: 4px 10px;
          width: 100%;
          box-sizing: border-box;
          justify-content: flex-start;
      }
      .stepper-container {
          flex-direction: column;
          align-items: flex-start;
          padding: 4px 6px;
          gap: 16px;
      }
      .step-item {
          flex-direction: row;
          width: 100%;
          max-width: 100%;
          align-items: flex-start;
          justify-content: flex-start;
          gap: 12px;
          text-align: left;
          padding: 0;
          position: relative;
      }
      .step-node-wrap {
          width: 28px;
          height: auto;
          flex-shrink: 0;
          justify-content: center;
          position: relative;
      }
      .step-circle {
          width: 28px;
          height: 28px;
          font-size: 12px;
      }
      .step-trail-line {
          top: 28px;
          left: 13px;
          width: 0;
          height: calc(100% + 16px);
          transform: none;
      }
      .step-trail-line.trail-solid {
          border-top: none;
          border-left: 2px solid #00b207;
      }
      .step-trail-line.trail-dashed {
          border-top: none;
          border-left: 2px dashed #cbd5e1;
      }
      .step-content {
          align-items: flex-start;
          text-align: left;
          margin-top: 2px;
          flex: 1;
          min-width: 0;
      }
      .step-label {
          margin-top: 0;
          min-height: auto;
          text-align: left;
          justify-content: flex-start;
          font-size: 13.5px;
      }
      .step-sub-wrap {
          justify-content: flex-start;
          min-height: auto;
          margin-top: 2px;
      }
      .step-sub-meta {
          justify-content: flex-start;
          font-size: 12px;
      }
      .products-table-card {
          border-radius: 10px;
          margin-bottom: 20px;
      }
      .products-card-header {
          padding: 12px 14px;
          flex-direction: row;
          justify-content: space-between;
          align-items: center;
          gap: 8px;
          flex-wrap: wrap;
      }
      .products-card-title {
          font-size: 13.5px;
      }
      .products-card-total {
          font-size: 12.5px;
      }
      .products-card-total strong {
          font-size: 14px;
      }
      .desktop-products-table-wrap {
          display: none !important;
      }
      .mobile-products-list {
          display: flex !important;
          flex-direction: column;
          gap: 10px;
          padding: 12px 14px 16px;
      }
  }
  ```
* **Pembaruan Struktur DOM HTML Produk:**
  Tabel dibungkus dengan `<div class="table-responsive desktop-products-table-wrap">` dan ditambahkan elemen mobile card:
  ```html
  <div class="mobile-products-list">
      @forelse($items as $item)
          <div class="mobile-product-card">
              <div class="mobile-product-card-top">
                  <div class="mobile-product-img-wrap">
                      @if($item['product']->foto_1)
                          <img src="{{ asset('storage/' . $item['product']->foto_1) }}" alt="{{ $item['product']->nama_produk }}">
                      @else
                          <img src="{{ asset('image/logo_circle.png') }}" alt="{{ $item['product']->nama_produk }}">
                      @endif
                  </div>
                  <div class="mobile-product-details">
                      <div class="mobile-product-title">{{ $item['product']->nama_produk }}</div>
                      @if(!empty($item['custom_text']))
                          <div class="custom-cake-text-pill mobile-custom-text-pill">
                              <i class="fa-solid fa-pen-nib me-1"></i> Tulisan Kue: "{{ $item['custom_text'] }}"
                          </div>
                      @endif
                  </div>
              </div>
              <div class="mobile-product-card-bottom">
                  <div class="mobile-product-qty-price">
                      <span class="mobile-qty-pill">x{{ $item['qty'] }}</span>
                      <span class="mobile-unit-price">@ Rp. {{ number_format($item['price'], 0, ',', '.') }}</span>
                  </div>
                  <div class="mobile-product-subtotal-wrap">
                      <span class="mobile-subtotal-label">Subtotal:</span>
                      <span class="mobile-subtotal-val">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                  </div>
              </div>
          </div>
      @empty
          <div class="products-table-empty">
              <i class="fa-solid fa-box-open"></i>
              Tidak ada rincian produk untuk pesanan ini.
          </div>
      @endforelse
  </div>
  ```

### 2. `resources/css/pelanggan_pages.css` & `resources/css/pelanggan_custom.css`
* Menambahkan deklarasi sinkron `.desktop-products-table-wrap` dan `.mobile-products-list` beserta seluruh sub-elemen mobile card agar kompilasi Vite dan aset publik konsisten.

---

## 5. Pengukuran DOM Geometry Headless Chrome CDP

```
┌────────────────────────────────────────────────────────────────────────┐
│ DOM Geometry Comparison: Sebelum vs Sesudah Optimasi                   │
├───────────────────────┬─────────────────────────┬──────────────────────┤
│ Metrik                │ Sebelum                 │ Sesudah              │
├───────────────────────┼─────────────────────────┼──────────────────────┤
│ Mobile pageOverflow   │ 0px                     │ 0px                  │
│ Table Horiz Overflow  │ 145px (Bocor!)          │ 0px (Sempurna)       │
│ Lebar Judul Produk    │ 53px (Gepeng 4 baris)   │ 223px (1 baris utuh) │
│ Tinggi Tombol Aksi    │ 36px vs 34px (Miring)   │ 38px vs 38px (Identik│
│ Tata Letak Tombol     │ Rata kanan canggung     │ 50%-50% Full-width   │
│ Desktop (1440×900)    │ Sesuai baseline         │ 100% Identik         │
└───────────────────────┴─────────────────────────┴──────────────────────┘
```

### Hasil Rinci per Viewport

1. **Mobile Standar (`375×812`)**:
   - `pageOverflow`: **`0px`**
   - `headerActionsGroup`: `left: 12px, right: 363px, width: 351px, height: 38px`
   - `desktopTblWrap`: `display: none, width: 0`
   - `mobileList`: `display: flex, width: 349px`
   - `mobileProductTitle`: `width: 223px, height: 19px` (teks terbaca utuh dalam 1 baris)
2. **Mobile Android Flagship (`412×812`) & iPhone Pro Max (`440×812`)**:
   - `pageOverflow`: **`0px`**
   - `headerActionsGroup`: tersusun harmonis dan simetris di seluruh lebar kartu
3. **Desktop (`1440×900`)**:
   - `desktopTblWrap`: `display: block, width: 866px, height: 174px` (tabel 4 kolom desktop tetap utuh)
   - `mobileList`: `display: none, width: 0, height: 0`
   - 3 kartu ringkasan, stepper horizontal 5-tahap, dan layout sidebar tetap 100% seperti semula.

---

## 6. Verifikasi & Quality Gate

1. **Vite Asset Build**:
   ```bash
   npm run build
   ```
   *Status:* `Exit Code: 0`, file CSS & JS terkompilasi dalam 550ms.

2. **Design Evidence Checker**:
   ```bash
   node check-evidence.js docs/evidence/order-detail-evidence.json
   ```
   *Status:* `Exit Code: 0` (`Evidence record complete`).

3. **PHPUnit / Artisan Test Suite**:
   ```bash
   php artisan test --filter=Customer
   ```
   *Status:* **48 passed (287 assertions), 0 failures**.
