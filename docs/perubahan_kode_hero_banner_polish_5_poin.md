# Dokumentasi Perubahan Kode: Peningkatan Desain Hero Banner Slideshow (5 Poin Polish)

Dokumen ini mencatat seluruh perubahan kode dan penyempurnaan desain pada komponen **Hero Banner Slideshow (Grid 65% : 35%)** di halaman beranda Zyh Corner (`resources/views/Pelanggan/home.blade.php`), lembar gaya CSS, dan skrip animasi JavaScript berdasarkan evaluasi kritis desain.

---

## 1. Latar Belakang & Poin-Poin Penyempurnaan

Berdasarkan audit desain frontend pada beranda nyata, diidentifikasi 5 area peningkatan visual:
1. **Poin A (Ritme Tipografi & Jeda Vertikal)**: Menghilangkan kekosongan antara callout diskon (`Sale up to 30% OFF`) dan tombol CTA *Shop Now* dengan menambahkan fallback deskripsi pendukung yang elegan.
2. **Poin B (Penempatan Panah Navigasi)**: Mencegah tombol panah kanan menutupi foto kue/pastry dengan menerapkan efek *reveal on hover* (`opacity: 0` saat idle, `opacity: 0.92` saat kursor berada di atas banner).
3. **Poin C (Penyelarasan Dot Indikator)**: Menata ulang container titik dot menjadi **Glassmorphic Floating Pill** yang sejajar dengan rata kiri konten teks (`left: 75px; bottom: 24px;`).
4. **Poin D (Ambient Seam & Subtle Inset Shadow)**: Memperhalus gradasi *radial spotlight seam* perbatasan 65%:35% dan menambahkan *subtle inset shadow* agar transisi foto berlatar gelap maupun terang menyatu alami.
5. **Poin E (Proporsi Layar Ponsel < 768px)**: Mengoptimalkan skala mobile (tinggi gambar 210px, judul 26px, padding ringkas) sehingga banner tampil utuh dalam *single viewport* ponsel tanpa terpotong ke bawah lipatan layar.

---

## 2. Berkas-Berkas yang Diubah

### A. `resources/views/Pelanggan/home.blade.php`
- **Markup Blade (`@section('content')`)**:
  - Menambahkan baris deskripsi pendukung otomatis saat callout diskon terdeteksi:
    ```php
    if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $calloutCandidate, $matches)) {
        $calloutPrefix = trim($matches[1]) ?: null;
        $calloutHighlight = trim($matches[2]);
        $descText = 'Dipanggang segar setiap pagi dengan bahan alami pilihan terbaik untuk momen spesial Anda.';
    } else {
        $descText = $calloutCandidate;
    }
    ```
- **CSS Stylesheet Internal**:
  - Mengatur `.hero-section` dengan `box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), inset 0 0 24px rgba(0, 0, 0, 0.02);`.
  - Mengatur ritme vertikal tipografi: `h1` (48px, line-height 1.15, margin-bottom 12px), `callout` (21px, margin-bottom 8px), `desc` (14.5px, margin-bottom 22px), `hero-btn` (margin-top 0).
  - Mengubah `.hero-slider-arrow` menjadi tersembunyi default (`opacity: 0; pointer-events: none;`) dan muncul anggun saat `.hero-slider-wrapper:hover` (`opacity: 0.92; pointer-events: auto; transform: translateY(-50%) scale(1);`).
  - Menata `.hero-slider-dots` sebagai kapsul kaca melayang di kiri bawah (`left: 75px; bottom: 24px; background: rgba(0, 0, 0, 0.20); backdrop-filter: blur(10px); border-radius: 30px;`).
  - Menyelaraskan `@media (max-width: 768px)`: tinggi gambar 210px, `h1` 26px, callout 17px, deskripsi 13px, tombol CTA 13.5px, dan panah tetap aktif di mobile (`opacity: 0.85`).

### B. `resources/css/pelanggan_pages.css`
- Menyelaraskan seluruh deklarasi kelas `.hero-section`, `.hero-text`, `.hero-badge-pill`, `.hero-text h1`, `.hero-sale-callout`, `.hero-text p.desc`, `.hero-btn`, `.hero-image::before`, `.hero-slider-arrow`, `.hero-slider-dots`, dan `.hero-dot`.
- Menyelaraskan breakpoint `@media (max-width: 768px)` agar bundel Vite menghasilkan tata letak mobile yang ringkas dan bebas konflik.

### C. `public/css/home.css`
- Menyelaraskan aturan panah *reveal on hover*, kapsul dot pill di `left: 75px`, dual radial spotlight seam, dan breakpoint mobile `@media (max-width: 768px)`.

### D. `resources/css/pelanggan_custom.css`
- Menyelaraskan style hero banner, tombol CTA, spotlight seam, dan media queries mobile 768px.

### E. `public/js/home.js`
- Mengganti animasi vertikal `y: -10` pada gambar cover penuh (yang berisiko menyisakan celah di batas bawah) menjadi efek pernapasan halus (*subtle breathing pulse* `scale: 1.025` dengan durasi 3.5 detik) yang aman untuk gambar *100% full-bleed*.
- Menambahkan animasi stagger masuk GSAP untuk elemen `.hero-sale-callout` dan `p.desc` saat perpindahan slide.

---

## 3. Hasil Pengujian Nyata (Browser Smoke Test Evidence)

Pengujian dilakukan menggunakan browser headless pada `http://127.0.0.1:8000/`:

### A. Desktop Viewport (1440 × 900 px)
- **Dimensi Kartu**: Lebar 1170 px, Tinggi 522 px.
- **Grid Asimetris**: Kolom teks 759.188 px (65%), Kolom gambar 408.812 px (35%).
- **Visibilitas Panah Idle**: `opacity: 0`, `pointerEvents: "none"` (Foto pastry tampil 100% bersih tanpa tertutup panah).
- **Visibilitas Panah Saat Hover**: `opacity: 0.92`, `pointerEvents: "auto"`.
- **Posisi Dot Pill**: `left: 75px`, `bottom: 24px` (Presisi sejajar dengan padding teks konten).
- **Tipografi & Konten**:
  - `h1`: 48 px.
  - `callout`: "Sale up to 30% OFF".
  - `desc`: "Dipanggang segar setiap pagi dengan bahan alami pilihan terbaik untuk momen spesial Anda."

### B. Mobile Viewport (390 × 844 px)
- **Dimensi Kartu Mobile**: Lebar 360 px, Tinggi total 508.125 px.
- **Tata Letak**: `display: flex; flex-direction: column-reverse;`.
- **Tinggi Foto Produk**: 210 px (cover penuh).
- **Ukuran Judul `h1`**: 26 px (line-height 1.22).
- **Jangkauan Vertikal**: Titik terbawah tombol CTA berada di 479 px dari batas atas kartu, pas dan utuh dalam satu layar ponsel tanpa harus digulir.
- **Kontrol Navigasi**: Panah navigasi aktif dengan `opacity: 0.85`, dot pill terpusat di `bottom: 12px`.
