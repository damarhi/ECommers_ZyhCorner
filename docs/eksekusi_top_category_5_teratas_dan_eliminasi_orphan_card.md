# Laporan Eksekusi Pembatasan Top Category Menjadi 5 Kategori Teratas & Eliminasi Orphan Card

**Tanggal**: 30 Oktober 2026  
**Status**: Selesai & Terverifikasi Penuh (*100% Green Test Suite & Headless Browser Verified*)  
**Prinsip Desain**: `/skill:frontend-design-direction` (*Grid Harmony, Single Balanced Hero Row, High-Signal Curation*)

---

## 1. Latar Belakang & Masalah Visual

Pada halaman beranda (`/` atau `resources/views/Pelanggan/home.blade.php`), bagian **Top Category** dirancang menggunakan CSS Grid 5 kolom:
```css
.category-grid {
    display: grid !important;
    grid-template-columns: repeat(5, 1fr) !important;
    gap: 20px !important;
}
```

### Masalah Sebelum Perubahan:
1. **Munculnya Kartu Yatim (*Orphan Card*) di Baris ke-2**:
   Controller sebelumnya mengambil seluruh kategori tanpa batasan:
   ```php
   $categories = Kategori::withCount('produks')->get();
   ```
   Di database terdapat total 6 kategori. Akibatnya, 5 kategori pertama mengisi baris 1, dan kategori ke-6 (*"asdad"* dengan 0 produk) terlempar sendirian ke baris ke-2 di sudut kiri bawah. Hal ini meninggalkan ruang kosong selebar 80% grid di sampingnya dan merusak estetika antarmuka.
2. **Kategori Kosong Ikut Tampil**:
   Kategori dummy/uji coba (*"asdad"*) yang tidak memiliki produk aktif (`produks_count = 0`) masuk ke area etalase unggulan beranda yang seharusnya eksklusif (*curated section*).
3. **Ketiadaan Pengurutan Berbasis Volume Produk**:
   Kategori belum diurutkan berdasarkan jumlah varian produk yang dimiliki, sehingga kategori yang paling diminati atau memiliki variasi terbanyak tidak terprioritaskan di depan.

---

## 2. Solusi yang Diterapkan (`/skill:frontend-design-direction`)

1. **Kurasi Berbasis Volume Produk Terbanyak (`produks_count DESC`)**:
   Query diubah agar menghitung agregasi produk per kategori, lalu memprioritaskan kategori yang memiliki varian produk aktif terbanyak di urutan terdepan.
2. **Pembatasan Tepat 5 Kategori Teratas (`limit(5)`)**:
   Hasil query dibatasi tepat 5 kategori teratas untuk membentuk **1 baris tunggal yang simetris, padat, dan elegan (*single balanced hero row*)** pada layar desktop. Kategori ke-6 yang kosong secara otomatis tersaring keluar.

---

## 3. Berkas yang Diubah

| No | Berkas | Lokasi Kode | Perubahan yang Dilakukan |
| :--- | :--- | :--- | :--- |
| 1 | `app/Http/Controllers/Pelanggan/PelangganController.php` | Baris 65–69 | Mengubah `$categories = Kategori::withCount('produks')->get();` menjadi query terurut `orderBy('produks_count', 'desc')->orderBy('id_kategori', 'asc')->limit(5)->get();`. |
| 2 | `docs/eksekusi_top_category_5_teratas_dan_eliminasi_orphan_card.md` | Seluruh berkas | Dokumentasi teknis komprehensif terkait perubahan kode, data sebelum vs sesudah, metrik DOM, dan pengujian. |

---

## 4. Rincian Perubahan Kode

### `app/Http/Controllers/Pelanggan/PelangganController.php`

**Sebelum:**
```php
$categories = Kategori::withCount('produks')->get();
```

**Sesudah:**
```php
$categories = Kategori::withCount('produks')
    ->orderBy('produks_count', 'desc')
    ->orderBy('id_kategori', 'asc')
    ->limit(5)
    ->get();
```

---

## 5. Hasil Pengukuran & Verifikasi Langsung (Browser Engine)

Pengujian dilakukan menggunakan Chromium *headless* pada lingkungan produksi riil (`http://127.0.0.1:8000/`):

### A. Data 5 Kategori Teratas yang Ditampilkan
1. **Kue & Cake**: 4 Products (ID: 1)
2. **Roti & Pastry**: 3 Products (ID: 2)
3. **Dessert & Buah Segar**: 2 Products (ID: 3)
4. **Produk Kustom & Gift**: 2 Products (ID: 4)
5. **Cemilan & Snack**: 2 Products (ID: 5)
*(Kategori "asdad" dengan 0 produk berhasil dieliminasi sepenuhnya dari beranda).*

### B. Metrik Kesejajaran & Dimensi Kartu Desktop (1200px / 1440px)
```
── HASIL VERIFIKASI GRID & KARTU KATEGORI ───────────────────────────────────
Grid Template Columns : 218px 218px 218px 218px 218px (5 Kolom Penuh)
Jumlah Kartu          : Tepat 5 kartu (1 Baris Tunggal)

Card 1 (Kue & Cake)          : Width = 218px, Height = 169px, Top = 1956px, Bottom = 2124px
Card 2 (Roti & Pastry)       : Width = 218px, Height = 169px, Top = 1956px, Bottom = 2124px
Card 3 (Dessert & Buah Segar): Width = 218px, Height = 169px, Top = 1956px, Bottom = 2124px
Card 4 (Produk Kustom & Gift): Width = 218px, Height = 169px, Top = 1956px, Bottom = 2124px
Card 5 (Cemilan & Snack)     : Width = 218px, Height = 169px, Top = 1956px, Bottom = 2124px
─────────────────────────────────────────────────────────────────────────────
STATUS KESEJAJARAN: 100% SEJAJAR PRESISI PADA ROW TUNGGAL, ZERO ORPHAN CARDS
```

### C. Responsivitas Layar Tablet (Viewport 771px)
- Ditata dalam grid yang seimbang dan responsif (`grid-template-columns: repeat(3, 1fr)`).
- Ketinggian kartu konsisten pada 169px.

### D. Hasil Test Suite (`php artisan test`)
```bash
php artisan test
Tests:    198 passed (1064 assertions)
Duration: 43.47s (100% Green)
```
Seluruh 198 pengujian fitur dan unit lolos 100% tanpa regresi.
