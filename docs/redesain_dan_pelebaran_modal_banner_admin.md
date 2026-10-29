# Dokumentasi Redesain, Pelebaran, dan Perapian Modal Banner Admin

## 1. Latar Belakang & Masalah Desain Awal
Pada halaman manajemen banner admin (`resources/views/Admin/kelola_carousel/banners.blade.php`), modal **Tambah Banner Baru** (`#addBannerModal`) dan **Edit Banner Promo** (`#editBannerModal{{ $b->id_banner }}`) sebelumnya memiliki sejumlah kendala visual dan kenyamanan penggunaan (*User Experience*):
1. **Ukuran Modal Sempit (`max-width: 700px`)**:
   Dengan batas lebar dialog hanya 700px dan pembagian grid 2 kolom (`col-lg-5` dan `col-lg-7`), kolom formulir kanan hanya memiliki lebar efektif ~370px. Hal ini membuat elemen input dan dropdown tampak berdempetan dan sempit.
2. **Nested Grey Cards Ganda di Kolom Kanan**:
   Kolom kanan dibungkus oleh dua kotak berlatar abu-abu sekunder (`background: #f8fafc; border: 1px solid #e2e8f0;`) dengan padding internal `p-2.5`. Hal ini membuang ~25px lebar horizontal secara cuma-cuma dan menimbulkan efek *boxed-in* berlebihan.
3. **Ikon Seksi Kurang Representatif**:
   Seksi informasi konten menggunakan ikon `mdi-format-text` yang menyerupai huruf besar tunggal "T", sehingga tampak tidak profesional dan kurang mencerminkan entri konten promosi.
4. **Ritme Spasi Vertikal Terlalu Rapat**:
   Margin bawah antar-input hanya `mb-2` (8px) dengan label `mb-1` (4px), sehingga bidang klik dan keterbacaan teks label menyatu dengan input di bawahnya.
5. **Emoji Mentah pada Pilihan Dropdown `<option>`**:
   Penggunaan emoji mentah seperti `🏠`, `🛍️`, `🎂`, `✨`, `🟢`, `⚪` di dalam `<option>` select menyebabkan perataan teks yang miring dan menabrak panah chevron native pada sistem operasi Windows.
6. **Proporsi Grid Baris Bawah (Urutan & Status)**:
   Pembagian seimbang 50:50 (`col-sm-6` & `col-sm-6`) membuat field *Urutan* (yang hanya berisi 1–2 digit angka) terlalu lebar, sedangkan dropdown *Status* (yang memuat teks status dan keterangan) menjadi sempit dan terpotong.

---

## 2. Rincian Solusi & Implementasi Desain

### A. Pelebaran Dialog Modal ke `860px` (`width: 94%`)
- Mengubah properti inline modal dialog dari `max-width: 700px; width: 95%;` menjadi:
  ```html
  <div class="modal-dialog modal-dialog-centered" style="max-width: 860px; margin: 1.75rem auto; width: 94%;">
  ```
- Memberikan tambahan ruang bersih sebesar **+160px**, memungkinkan rasio visual banner 16:9 dan form input dua seksi tampil lapang tanpa menabrak batas layar pada resolusi laptop maupun tablet.

### B. Penghapusan *Nested Grey Cards* pada Kolom Kanan
- Menghapus wrapper card abu-abu ganda (`background: #f8fafc; border: 1px solid #e2e8f0;`) di kolom kanan.
- Menggantikannya dengan pembatas tipografi seksi yang bersih dan modern:
  - Header Seksi 1: `<i class="mdi mdi-card-text-outline text-primary"></i> INFORMASI KONTEN`
  - Header Seksi 2: `<i class="mdi mdi-tune-vertical-variant text-primary"></i> PENEMPATAN & STATUS`
- Border bawah halus `border-bottom` dengan letter-spacing `0.5px` dan ukuran font `11px` uppercase memberikan hierarki yang kuat tanpa membuang ruang horizontal.

### C. Standardisasi Ritme Spasi dan Input Form
- **Margin Antar Field**: Ditingkatkan dari `mb-2` (8px) menjadi `mb-3` (14–16px) dan `mb-3.5` (20px) antar seksi.
- **Label**: Font size `12px` dengan `font-weight: 600`, warna teks `#1e293b`, dan `margin-bottom: 6px` (`mb-1.5`).
- **Field Input & Select**: Tinggi konsisten `38px`, sudut membulat `border-radius: 8px`, border elegan `#cbd5e1`, dan ukuran font `12.5px`.
- **Tanda Opsional**: Diformat rapi menggunakan teks abu-abu sekunder `(Opsional)`.

### D. Pembersihan Emoji pada Opsi Dropdown
- Menghilangkan emoji mentah pada tag `<option>` dan menggantinya dengan deskripsi kontekstual profesional:
  - Posisi Banner:
    - `home_hero` -> `Beranda Utama (Home Hero Slider)`
    - `shop_header` -> `Halaman Showcase (Shop Header)`
    - `custom_header` -> `Halaman Produk Kustom (Custom Header)`
    - `popup_promo` -> `Pop-up Promo Spesial`
  - Status Banner:
    - `Aktif` -> `Aktif (Ditayangkan)`
    - `Nonaktif` -> `Nonaktif (Draf / Arsip)`

### E. Penataan Proporsional Baris Bawah (Urutan & Status)
- Mengubah grid baris bawah dari `col-sm-6` / `col-sm-6` menjadi:
  - `col-sm-5` untuk field **Urutan** (cukup lapang untuk angka dan steppers).
  - `col-sm-7` untuk dropdown **Status Tayang** (cukup luas untuk membaca teks status tanpa menabrak panah dropdown).

### F. Penyelarasan Kolom Kiri (Visual Pratinjau & Upload Dropzone)
- Wadah pratinjau rasio 16:9 (`max-height: 175px`) dengan latar belakang bersih, badge posisi transparan di sudut kiri atas ber-filter `backdrop-filter: blur(4px)`.
- Dropzone upload file dengan border putus-putus (*dashed*) `#cbd5e1`, hover feedback hijau `#00b207`, ikon cloud upload modern, dan label deskripsi yang proporsional.
- Tombol reset/batal gambar tersembunyi secara default dan muncul otomatis ketika ada file baru yang dipilih (`btnResetAddImg` dan `btnResetImg{{ $b->id_banner }}`).

---

## 3. Berkas yang Diubah

| No | Berkas | Bagian / Baris | Perubahan |
|----|--------|----------------|-----------|
| 1 | `resources/views/Admin/kelola_carousel/banners.blade.php` | Baris 368–530 | Redesain & pelebaran `#editBannerModal{{ $b->id_banner }}` (Modal Edit Banner Promo) |
| 2 | `resources/views/Admin/kelola_carousel/banners.blade.php` | Baris 575–735 | Redesain & pelebaran `#addBannerModal` (Modal Tambah Banner Baru) |

---

## 4. Perbandingan Kode (Sebelum vs Sesudah)

### A. Modal Dialog Sizing & Header
```html
<!-- SEBELUM -->
<div class="modal-dialog modal-dialog-centered" style="max-width: 700px; margin: 1.75rem auto; width: 95%;">
    <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; ...">
        <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
            <span class="d-inline-flex ... rounded-circle" style="width: 34px; height: 34px;">
                <i class="mdi mdi-image-plus-outline" style="font-size: 18px;"></i>
            </span>
            ...
        </div>
    </div>
</div>

<!-- SESUDAH -->
<div class="modal-dialog modal-dialog-centered" style="max-width: 860px; margin: 1.75rem auto; width: 94%;">
    <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 14px; ...">
        <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-white flex-shrink-0">
            <span class="d-inline-flex ... rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="mdi mdi-image-plus-outline" style="font-size: 20px;"></i>
            </span>
            ...
        </div>
    </div>
</div>
```

### B. Form Kolom Kanan (Pembersihan Nested Cards & Tipografi Seksi)
```html
<!-- SEBELUM: Menggunakan nested cards berlatar abu-abu -->
<div class="col-lg-7 d-flex flex-column gap-2.5">
    <div class="p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        <div class="text-uppercase text-muted font-weight-bold mb-2 pb-1 border-bottom" style="font-size: 10px;">
            <i class="mdi mdi-format-text me-1 text-primary"></i> Informasi Konten
        </div>
        <div class="mb-2">...</div>
        <div>...</div>
    </div>
    <div class="p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        <div class="text-uppercase text-muted font-weight-bold mb-2 pb-1 border-bottom" style="font-size: 10px;">
            <i class="mdi mdi-tune me-1 text-primary"></i> Penempatan & Status
        </div>
        ...
    </div>
</div>

<!-- SESUDAH: Flat, bersih, dan lapang dengan pemisah garis bawah tipografi -->
<div class="col-lg-7 d-flex flex-column">
    <!-- Seksi 1: Informasi Konten -->
    <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
        <i class="mdi mdi-card-text-outline text-primary" style="font-size: 15px;"></i>
        <span>Informasi Konten</span>
    </div>
    <div class="mb-3">...</div>
    <div class="mb-3.5">...</div>

    <!-- Seksi 2: Penempatan & Status -->
    <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
        <i class="mdi mdi-tune-vertical-variant text-primary" style="font-size: 15px;"></i>
        <span>Penempatan & Status</span>
    </div>
    <div class="mb-3">...</div>
    <div class="mb-3">...</div>
    <div class="row g-2.5">
        <div class="col-sm-5">...</div>
        <div class="col-sm-7">...</div>
    </div>
</div>
```

### C. Pembersihan Emoji dari Dropdown Option
```html
<!-- SEBELUM -->
<select name="posisi_banner" class="form-control form-select ...">
    <option value="home_hero">🏠 Home Hero Slider (Beranda Utama)</option>
    <option value="shop_header">🛍️ Header Showcase (Halaman Shop)</option>
    <option value="custom_header">🎂 Header Kustom (Halaman Custom Products)</option>
    <option value="popup_promo">✨ Pop-up Promo Eksklusif</option>
</select>
<select name="status" class="form-control form-select ...">
    <option value="Aktif">🟢 Aktif (Tayang)</option>
    <option value="Nonaktif">⚪ Nonaktif (Draft)</option>
</select>

<!-- SESUDAH -->
<select name="posisi_banner" class="form-control form-select ...">
    <option value="home_hero">Beranda Utama (Home Hero Slider)</option>
    <option value="shop_header">Halaman Showcase (Shop Header)</option>
    <option value="custom_header">Halaman Produk Kustom (Custom Header)</option>
    <option value="popup_promo">Pop-up Promo Spesial</option>
</select>
<select name="status" class="form-control form-select ...">
    <option value="Aktif">Aktif (Ditayangkan)</option>
    <option value="Nonaktif">Nonaktif (Draf / Arsip)</option>
</select>
```

---

## 5. Verifikasi & Hasil Pengujian

1. **Blade Syntax & Template Cache**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil*: `Compiled views cleared successfully. Blade templates cached successfully.` (0 error).

2. **Feature Test Khusus Banner (`AdminBannerTest`)**:
   ```bash
   php artisan test --filter=AdminBannerTest
   ```
   *Hasil*: **9 passed (36 assertions)**, termasuk isolasi modal tidak bersarang (*modal isolation*), pembuatan banner, pembaruan banner, toggle status, dan filter posisi.

3. **Seluruh Test Suite Sistem**:
   ```bash
   php artisan test
   ```
   *Hasil*: **160 passed (814 assertions)** lulus 100% tanpa ada regresi.
