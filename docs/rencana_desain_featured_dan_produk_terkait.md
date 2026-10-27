# Rencana Desain UI: Pembatasan Featured Products (4 Item) & Penyeimbangan Grid Produk Terkait (4 Item)

Dokumen ini disusun berdasarkan skill **`frontend-design-direction`** untuk merencanakan dan mengarahkan refaktorisasi tampilan katalog produk pada antarmuka pelanggan Zyh Corner.

---

## 1. Analisis Masalah & Observasi Antarmuka

### 1.1. Bagian "Featured Products" pada Beranda (`home.blade.php`)
- **Kondisi Sebelumnya**: Controller memuat 8 produk (`limit(8)`), yang dirender dalam 2 baris penuh (masing-masing 4 kolom).
- **Masalah Desain**:
  - Halaman beranda menjadi terlalu panjang ke bawah (*heavy scroll fatigue*) sebelum pengguna mencapai bagian promosi berikutnya (*Hot Deals*, *Best Sellers*, *Category Showcase*).
  - Pilihan produk unggulan kehilangan kesan eksklusivitas (*diluted focus*) karena terlalu banyak produk yang dicap sebagai "featured".
- **Solusi**: Batasi menjadi tepat **4 produk unggulan** (`limit(4)`). Pada layar desktop, 4 produk akan membentuk **1 baris tunggal yang rapi, bersih, dan berdampak visual kuat** (*single high-signal hero row*).

### 1.2. Bagian "Produk Terkait" pada Halaman Detail Produk (`detail.blade.php`)
- **Kondisi Sebelumnya**:
  - Grid produk terkait menggunakan tata letak 4 kolom (`grid-template-columns: repeat(4, 1fr)`).
  - Controller mengambil produk terkait hanya berdasarkan kategori produk yang sedang dibuka (`where('id_kategori', $product->id_kategori)`).
  - Karena sebagian besar kategori dalam database hanya memiliki 2–4 produk (misalnya Kategori 1 hanya memiliki 4 produk, sehingga dikurangi produk aktif menjadi 3 produk), grid sering kali **hanya menampilkan 3 kartu**, meninggalkan **1 slot kosong di sisi kanan**.
- **Masalah Desain**:
  - Tampilan terlihat timpang, tidak seimbang, dan terkesan seperti ada gambar yang gagal dimuat (*asymmetrical grid hole*).
- **Solusi**:
  - Pastikan kartu produk terkait selalu terisi lengkap **4 produk**.
  - Mekanisme query cerdas (*smart backfill strategy*): Jika produk dalam kategori yang sama kurang dari 4, sistem secara otomatis mengambil sisa kartu dari produk *showcase* aktif lainnya (tanpa menduplikasi produk yang sedang dilihat atau yang sudah terpilih), persis seperti logika *fallback* pada `hotDeals`.

---

## 2. Arahan Desain (Frontend Design Direction)

### 2.1. Desain Kontrak & Hirarki Visual
- **Purpose**:
  - *Featured Products*: Memberikan rekomendasi kurasi teratas di beranda dalam satu baris pandangan pertama (*first glance engagement*).
  - *Produk Terkait*: Memberikan alternatif produk sejenis atau rekomendasi relevan kepada pembeli yang sedang menimbang suatu produk, tanpa kekosongan grid.
- **Audience**: Pelanggan toko kue dan *bakery* yang menginginkan penjelajahan katalog yang intuitif dan estetik.
- **Tone**: *Clean, balanced, delightful, modern bakery store*.
- **Grid Symmetry**:
  - Desktop ($> 768\text{px}$): 4 kolom simetris ($1 \times 4$).
  - Tablet / Mobile ($\le 768\text{px}$): 2 kolom simetris ($2 \times 2$).
  - Zero Orphan Cards: Tidak ada kolom kosong menggantung.

---

## 3. Wireframe & Visual Grid Comparison

### 3.1. Featured Products (Beranda)
```
[Sebelumnya: 8 Produk - 2 Baris Menumpuk]
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ Produk 1  │ │ Produk 2  │ │ Produk 3  │ │ Produk 4  │
└───────────┘ └───────────┘ └───────────┘ └───────────┘
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ Produk 5  │ │ Produk 6  │ │ Produk 7  │ │ Produk 8  │
└───────────┘ └───────────┘ └───────────┘ └───────────┘

[Sesudahnya: 4 Produk - 1 Baris Fokus & Eksklusif]
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ Produk 1  │ │ Produk 2  │ │ Produk 3  │ │ Produk 4  │
└───────────┘ └───────────┘ └───────────┘ └───────────┘
```

### 3.2. Produk Terkait (Halaman Detail)
```
[Sebelumnya: 3 Produk - Slot Kolom ke-4 Kosong Melompong]
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌  -  -  -  ┐
│ Produk A  │ │ Produk B  │ │ Produk C  │ │ (KOSONG)  │
└───────────┘ └───────────┘ └───────────┘ └  -  -  -  ┘

[Sesudahnya: 4 Produk Lengkap dengan Smart Backfill]
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ Produk A  │ │ Produk B  │ │ Produk C  │ │ Produk D  │ (Rekomendasi Pelengkap)
└───────────┘ └───────────┘ └───────────┘ └───────────┘
```

---

## 4. Rencana Implementasi Teknis

1. **`app/Http/Controllers/Pelanggan/PelangganController.php` (`home()`)**:
   - Mengubah parameter `limit(8)` pada query `$featuredProducts` menjadi `limit(4)`.
2. **`app/Http/Controllers/Pelanggan/PelangganController.php` (`detail($id)`)**:
   - Mempertahankan prioritas produk dari kategori yang sama (`limit(4)`).
   - Menambahkan *fallback query* jika `$relatedProducts->count() < 4`: mengambil produk tambahan bertipe *showcase* yang aktif, mengecualikan ID produk yang sedang dibuka dan yang sudah ada di koleksi.
3. **Verifikasi**:
   - Menjalankan `npm run build` untuk memastikan aset Vite terkompilasi bersih.
   - Menjalankan `php artisan test` untuk memastikan 130 pengujian tetap hijau 100%.
