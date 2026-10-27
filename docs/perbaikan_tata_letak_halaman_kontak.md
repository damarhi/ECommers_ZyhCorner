# Dokumentasi Perbaikan Tata Letak Halaman Kontak (`/contact`)

## 1. Analisis Masalah & Akar Penyebab (*Root Cause Analysis*)

### A. Gejala Visual
Pada tampilan halaman kontak (`/contact`), antarmuka mengalami anomali serius:
1. Formulir pengiriman pesan *"Just Say Hello"* terjepit di kolom sebelah kiri di bawah informasi alamat, email, dan telepon, dengan lebar sempit yang membuat input bertumpuk canggung.
2. Google Maps iframe terangkat ke atas menjadi kolom kanan mendampingi kartu kontak, alih-alih membentang penuh (*full-width*) di bagian bawah halaman.
3. Kerapian visual dan keseimbangan layout halaman rusak total.

### B. Akar Masalah Teknis (DOM Syntax Error)
Penyebab utama adalah **tag penutup HTML yang hilang (*unclosed `<div>` tag*)** pada berkas `resources/views/Pelanggan/contact.blade.php`:

```html
<!-- KODE SEBELUMNYA (BARIS 19-26) -->
<div class="contact-info-section">
    <div class="contact-info-icon">
        <i class="fa-solid fa-location-dot"></i>
    </div>
    <div class="contact-info-text">
        <h3>Alamat Toko</h3>
        <p>{{ $storeSettings['store_address'] ?? '...' }}</p>
</div> <!-- Hanya 1 tag </div> penutup untuk 2 tag <div> yang dibuka -->
```

Karena `<div class="contact-info-section">` tidak ditutup di baris 26:
- Tag penutup pada baris 58 (`</div>`) yang semestinya menutup kartu induk `.contact-info-card` justru menutup `.contact-info-section`.
- Elemen form `.contact-form-card` (*Just Say Hello*) secara keliru masuk sebagai anak di dalam `.contact-info-card` (kolom 1fr / ~325px).
- Tag penutup `.contact-grid` terpakai menutup `.contact-info-card`, sehingga `.contact-map-wrapper` masuk sebagai kolom kedua di `.contact-grid` (menempati jatah 2.5fr).
- Kontainer `.contact-container` terpotong sebelum footer.

---

## 2. Rincian Perubahan Kode

### A. Perbaikan Markup `resources/views/Pelanggan/contact.blade.php`
1. Menambahkan tag penutup `</div>` yang hilang pada `.contact-info-section` Alamat Toko:
   ```html
   <div class="contact-info-section">
       <div class="contact-info-icon">
           <i class="fa-solid fa-location-dot"></i>
       </div>
       <div class="contact-info-text">
           <h3>Alamat Toko</h3>
           <p>{{ $storeSettings['store_address'] ?? '...' }}</p>
       </div>
   </div>
   ```
2. Memastikan penutupan tag berjenjang rapi:
   - `.contact-info-card` ditutup sebelum `.contact-form-card`.
   - `.contact-grid` menampung 2 kolom sejajar: `.contact-info-card` (1fr) dan `.contact-form-card` (2.5fr).
   - `.contact-map-wrapper` diletakkan di bawah `.contact-grid` sebagai blok mandiri di dalam `.contact-container`.
3. Memperbaiki selector GSAP animation dari `.contact-map-container` menjadi `.contact-map-wrapper` agar animasi entrance berjalan halus.

### B. Penyempurnaan CSS `resources/css/pelanggan_pages.css`
1. **Peningkatan Desain Kartu**:
   - `border-radius: 16px` dan border halus `#eef0f2`.
   - Shadow lembut `box-shadow: 0 8px 30px rgba(0,0,0,0.04)`.
2. **Ikon Kontak Interaktif**:
   - Ikon kontak dibungkus dalam lingkaran berlatar sage organik `rgba(0, 178, 7, 0.08)` berukuran 58x58px.
   - Efek hover halus: latar menjadi hijau solid `#00b207`, ikon putih, dan transformasi skala 1.08x dengan bayangan halus.
3. **Responsivitas**:
   - Desktop ($\ge 992\text{px}$): Grid 2 kolom (`1fr : 2.5fr`) + Peta Full-Width.
   - Tablet (768px – 991px): Kartu info beralih ke orientasi horizontal.
   - Mobile (< 768px): Seluruh grid dan form row beralih ke satu kolom vertikal yang ramah sentuhan (*thumb-friendly*).

---

## 3. Bukti Verifikasi Tata Letak (Headless Browser)

Pengujian dilakukan menggunakan browser Chromium headless pada resolusi Desktop (1280x900) dan Mobile (390x844):

### A. Metrik Koordinat Desktop
| Elemen | Posisi X | Posisi Y | Lebar | Tinggi | Status |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `.contact-info-card` | 55 px | 347 px | 325.7 px | 617.75 px | Kolom Kiri Sejajar |
| `.contact-form-card` | 410.7 px | 347 px | 814.3 px | 616.76 px | Kolom Kanan Sejajar |
| `.contact-map-wrapper` | 55 px | 1004.75 px | 1170 px | 452 px | Bawah Full-Width |

- `isSideBySide`: **true** (kedua kartu berada pada koordinat $Y = 347\text{ px}$ dengan tinggi seimbang ~617 px).
- `isMapBelow`: **true** (peta membentang penuh di bawah grid kontak dengan jarak 40px).

### B. Metrik Koordinat Mobile
| Elemen | Posisi Y | Lebar | Status |
| :--- | :---: | :---: | :--- |
| `.contact-info-card` | 310 px | 360 px (100%) | Tumpukan Teratas |
| `.contact-form-card` | 965.75 px | 360 px (100%) | Tumpukan Tengah |
| `.contact-map-wrapper` | 1739.89 px | 360 px (100%) | Tumpukan Bawah |

- `isStacked`: **true** (elemen tersusun rapi dari atas ke bawah tanpa overlap atau teks terpotong).

### C. Pengujian Fungsional Form Kontak
- Pengujian submit form dengan data:
  - **Nama**: `Siti Rahma`
  - **Email**: `siti.rahma@example.com`
  - **Subjek**: `Tanya Pengiriman Luar Kota`
  - **Pesan**: `Halo, apakah Zyh Corner melayani pengiriman hampers cake ke luar kota Pekanbaru? Terima kasih.`
- **Hasil**: Permintaan AJAX `POST /contact/send` merespon dengan HTTP 200, SweetAlert2 *"Pesan Terkirim!"* muncul, dan data berhasil masuk ke tabel `contact_messages`.

---

## 4. Kesimpulan
Tata letak halaman kontak (`/contact`) telah pulih sepenuhnya, seimbang, responsif, dan terverifikasi secara visual maupun fungsional.
