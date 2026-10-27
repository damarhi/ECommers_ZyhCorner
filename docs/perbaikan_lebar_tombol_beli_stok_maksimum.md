# Dokumentasi Perbaikan Tata Letak: Menjaga Stabilitas Lebar Tombol "Beli Sekarang" Saat Batas Stok Tercapai

## 1. Analisis Akar Masalah (Root Cause Analysis)

Sebelum perbaikan, ketika kuantitas produk mencapai batas stok maksimum, teks peringatan berwarna merah (`#qtyStockWarning`: *"Maksimal stok tercapai (tersisa X unit)"*) muncul tepat di bawah kontrol kuantitas. Hal ini mengakibatkan lebar tombol **"Beli Sekarang"** mendadak mengecil (*shrinkage*).

### Penyebab Teknis:
1. **Pelebaran Kontainer Kolom Stepper (`.qty-stepper-wrapper`)**:
   - Kontainer stepper dibungkus dalam `<div class="qty-stepper-wrapper" style="display: flex; flex-direction: column;">` tanpa batasan lebar (`width` / `max-width`).
   - Lebar kontainer ini secara bawaan mengikuti elemen terlebar di dalamnya (*intrinsic max-content width*).
   - Pada kondisi normal (tanpa peringatan), lebarnya hanya ditentukan oleh kotak stepper kuantitas `.qty-box` (~135px).
   - Ketika peringatan stok muncul, teks merah sepanjang ~250px ditampilkan, sehingga seluruh pembungkus kolom membengkak dari **135px menjadi ~250px** (melebar ~115px).
2. **Desakan pada Tombol Pembelian (`.btn-buy`)**:
   - Tombol `.btn-buy` memiliki aturan Flexbox `flex: 1;`.
   - Artinya, tombol ini menempati sisa ruang horizontal yang tersedia pada baris `.pi-actions`.
   - Karena kontainer di sebelah kirinya membesar ~115px, maka ruang sisa otomatis berkurang ~115px. Akibatnya, tombol **"Beli Sekarang" menyusut drastis**.
3. **Efek Jitter (*Layout Shift*)**:
   - Ketika kuantitas dikurangi kembali dengan tombol `-`, teks peringatan hilang dan tombol tiba-tiba melebar kembali, menimbulkan pergeseran tata letak visual (*Cumulative Layout Shift*) yang mengganggu kenyamanan pengguna.

---

## 2. Arahan Desain (*Frontend Design Direction*)

- **Stabilitas Geometris Tombol Aksi Utama**: Tombol *Primary Action* ("Beli Sekarang") dan kontrol kuantitas stepper tidak boleh mengalami perubahan dimensi (*zero layout shift*) apa pun status validasi yang aktif.
- **Penyelarasan Vertikal Presisi**: Kotak kuantitas (`.qty-box`) dan tombol pembelian (`.btn-buy`) memiliki tinggi identik 48px dan wajib selalu sejajar rata tengah vertikal (`align-items: center`).
- **Pesan Peringatan Baris Penuh (*Full-width Sub-row*)**: Pesan batas stok diletakkan pada baris tersendiri di bawah kedua kontrol aksi menggunakan `flex-basis: 100%`, sehingga tidak pernah mengganggu pembagian ruang horizontal baris tombol.

---

## 3. Rincian Perubahan Kode

### A. Tampilan Detail Produk (`resources/views/Pelanggan/detail.blade.php`)
- Menghapus pembungkus kolom `.qty-stepper-wrapper` yang memicu kalkulasi lebar keliru.
- Mengembalikan `.pi-actions` ke perataan vertikal default (`align-items: center`).
- Menempatkan `<div id="qtyStockWarning" class="qty-stock-warning">` sebagai elemen baris baru di bawah tombol aksi.

```blade
<!-- SEBELUM -->
<div class="pi-actions" style="align-items: flex-start;">
    @elseif ($product->stok > 0)
        <div class="qty-stepper-wrapper" style="display: flex; flex-direction: column; align-items: flex-start;">
            <div class="qty-box">
                <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
                <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
            </div>
            <span id="qtyStockWarning" class="qty-stock-warning" style="display: none; color: #dc2626; font-size: 11.5px; font-weight: 500; margin-top: 5px; line-height: 1.2;">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
            </span>
        </div>
        <button class="btn-buy" onclick="buyNow()">
            <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
        </button>

<!-- SESUDAH -->
<div class="pi-actions">
    @elseif ($product->stok > 0)
        <div class="qty-box">
            <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
            <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy" onclick="buyNow()">
            <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
        </button>
        <div id="qtyStockWarning" class="qty-stock-warning" style="display: none;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
        </div>
```

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)
- Mengunci dimensi kotak stepper `.qty-box` dengan `width: 135px; flex-shrink: 0;` agar tidak dapat disusutkan.
- Menambahkan aturan kelas `.qty-stock-warning` dan `.pm-stock-warning` dengan `flex-basis: 100%; width: 100%;` serta animasi halus `fadeInStockWarning`:

```css
.qty-box {
    display: flex; align-items: center; border: 1.5px solid #e6e6e6; border-radius: 50px;
    overflow: hidden; height: 48px; width: 135px; flex-shrink: 0;
}
.btn-buy {
    flex: 1; min-width: 150px; height: 48px; background: #00b207; color: white; border: none;
    border-radius: 50px; font-weight: 600; font-size: 15px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.3s;
}
.btn-buy:hover { background: #009906; }
.qty-stock-warning, .pm-stock-warning {
    flex-basis: 100%; width: 100%; color: #dc2626; font-size: 12px; font-weight: 500;
    margin-top: 4px; line-height: 1.3; animation: fadeInStockWarning 0.2s ease-in-out;
}
@keyframes fadeInStockWarning {
    from { opacity: 0; transform: translateY(-3px); }
    to { opacity: 1; transform: translateY(0); }
}
```

---

### C. Modal Quick View (`resources/views/Layout/layout_pelanggan.blade.php`)
- Menerapkan arsitektur layout yang sama pada modal Quick View:
  - Menghapus `.pm-qty-wrapper`.
  - Mengatur `.pm-actions` dengan `display: flex; flex-wrap: wrap; align-items: center; gap: 12px;`.
  - Meletakkan `#pmStockWarning` di baris penuh tersendiri dengan kelas `.pm-stock-warning`.

---

### D. Skrip Logika Kuantitas (`resources/js/pelanggan_pages.js`)
- Memperbarui pemunculan elemen peringatan dari `inline-block` menjadi `block`:
  - `window.changeQty`: Menampilkan `#qtyStockWarning` sebagai `display: block`.
  - `window.updateModalQty`: Menampilkan `#pmStockWarning` sebagai `display: block`.
  - `window.addToCart`: Menampilkan `#qtyStockWarning` dan `#pmStockWarning` sebagai `display: block`.

---

## 4. Hasil Verifikasi & Uji Kualitas

1. **Vite Asset Build**:
   ```bash
   npm run build
   ```
   *Hasil:* Sukses (`built in 503ms`).
2. **Pembersihan Cache Tampilan**:
   ```bash
   php artisan view:clear
   ```
   *Hasil:* Sukses (`Compiled views cleared successfully`).
3. **Pengujian Regresi Otomatis**:
   ```bash
   php artisan test
   ```
   *Hasil:* **90 passed (370 assertions)**, 0 failed.
4. **Verifikasi Stabilitas Tata Letak**:
   - Lebar tombol "Beli Sekarang" tetap konstan 100% dari sisa baris, baik saat peringatan stok tersembunyi maupun muncul.
   - Tidak ada pergeseran vertikal (*vertical shift*) pada tombol aksi karena keduanya tetap sejajar vertikal pada tinggi 48px.
   - Pada layar mobile (< 480px), kontrol kuantitas, tombol beli, dan teks peringatan tersusun rapi secara bertingkat (*stacked*) dengan lebar 100%.
