# Dokumentasi Perbaikan: Tata Letak Tombol Stok Habis dan Penghapusan Teks Sisa Unit Samping Tombol Beli

## 1. Latar Belakang & Masalah (*Problem & Background*)

Pada pembaruan sebelumnya, informasi sisa stok fisik telah dipindahkan secara bersih ke baris informasi produk tepat di bawah baris SKU (`Stok : Tersedia: X unit` dengan warna teks hijau `#00b207`, tanpa latar badge dan tanpa dot bulat).

Namun, masih ditemukan dua kendala pada area aksi utama (`.pi-actions`):
1. **Redundansi Informasi di Samping Tombol Beli**:
   Teks petunjuk sisa stok (`.pi-qty-hint`) seperti `[📦 Tersisa 9 unit]` atau `[⏰ Made by Order]` masih muncul di samping kontrol kuantitas dan tombol *"Beli Sekarang"*. Keberadaan teks ini membuat area tombol aksi menjadi sempit, padat, dan menduplikasi data yang sudah tertera jelas pada baris spesifikasi di atasnya.
2. **Tata Letak Canggung pada Produk Stok Habis**:
   Pada produk yang kehabisan stok fisik (`stok <= 0`), tombol non-aktif abu-abu bertuliskan *"Stok Habis"* sebelumnya berada berdampingan secara horizontal di sebelah kotak kuantitas yang dinonaktifkan (`[ - | 0 | + ]`). Tata letak ini kurang proporsional dan tidak mencerminkan hierarki visual e-commerce modern di mana tombol aksi berstatus disabled seharusnya tertata rapi di baris bawah kontrol input.

---

## 2. Struktur Visual Antarmuka (Sebelum vs Sesudah)

### A. Kondisi Produk Tersedia (`stok > 0` / `/product/1`)

```
[SEBELUM]
[ - | 1 | + ]   [📦 Tersisa 10 unit]   [ 🛍️ Beli Sekarang ]
                ^^^^^^^^^^^^^^^^^^^^
                (Redundan & Mempersempit Tombol)

[SESUDAH]
[ - | 1 | + ]   [ 🛍️ Beli Sekarang ]
(Tata letak horizontal bersih, proporsional, dan lega)
```

---

### B. Kondisi Produk Stok Habis (`stok <= 0` / `/product/3`)

```
[SEBELUM]
[ - | 0 | + ]   [❌ Stok Habis]   [ 🚫 Stok Habis ]
(Menumpuk horizontal secara tidak seimbang)

[SESUDAH]
┌─────────────┐
│ [ - | 0 | + ] │  <-- Kotak kuantitas disabled (background: #f5f5f5, value: 0)
└─────────────┘
┌───────────────────────────────────────┐
│ 🚫 Stok Habis                          │  <-- Tombol disabled tertata rapi di bawah
└───────────────────────────────────────┘      (lebar proporsional max-width: 360px)
```

---

## 3. Rincian Kode yang Diubah

### A. Template Blade (`resources/views/Pelanggan/detail.blade.php`)

Menghapus seluruh elemen `.pi-qty-hint` di dalam `.pi-actions`, dan menyematkan kelas kondisional `.is-out-of-stock` ketika produk tidak memiliki stok fisik:

```blade
<div class="pi-actions {{ $product->stok <= 0 && $product->jenis_produk !== 'kustom' && request()->query('custom') != 1 ? 'is-out-of-stock' : '' }}">
    @if (request()->query('custom') == 1 || $product->jenis_produk === 'kustom')
        {{-- State 1: Produk Kustom / Pre-Order --}}
        <div class="qty-box">
            <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok > 0 ? $product->stok : 999 }}" oninput="changeQty(0)">
            <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy" onclick="buyCustomNow()" style="background-color: #25d366 !important;">
            <i class="fa-brands fa-whatsapp"></i> Pesan via WhatsApp @if($product->stok <= 0) (Pre-Order) @endif
        </button>
        <div id="qtyStockWarning" class="qty-stock-warning" style="display: none;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
        </div>
    @elseif ($product->stok > 0)
        {{-- State 2: Produk Reguler Ready Stock --}}
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
    @else
        {{-- State 3: Produk Reguler Stok Habis (Out of Stock) --}}
        <div class="qty-box" style="opacity: 0.5; pointer-events: none; background-color: #f5f5f5;">
            <button class="qty-btn" disabled><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="0" id="qtyInput" disabled style="background-color: #f5f5f5;">
            <button class="qty-btn" disabled><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy disabled" disabled style="background-color: #e6e6e6 !important; color: #999999 !important; cursor: not-allowed;">
            <i class="fa-solid fa-ban"></i> Stok Habis
        </button>
    @endif
</div>
```

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)

Menambahkan tata letak vertikal (*column*) dan aturan batas lebar maksimal untuk tombol disabled pada kondisi stok habis:

```css
/* Quantity + Actions Base */
.pi-actions { display: flex; align-items: center; gap: 12px; margin-top: 25px; flex-wrap: wrap; }

/* Modifier Khusus Kondisi Stok Habis: Elemen Berada di Bawah Stepper Kuantitas */
.pi-actions.is-out-of-stock {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
}

.pi-actions.is-out-of-stock .btn-buy {
    width: 100%;
    max-width: 360px;
    flex: none;
}
```

---

### C. Pengujian Otomatis (`tests/Feature/ProductDetailStockInfoTest.php`)

Menyesuaikan assertions agar mengunci ketiadaan teks hint sampingan serta memastikan kelas modifier tata letak vertikal `is-out-of-stock` selalu dirender pada kondisi stok habis:

```php
public function test_detail_page_renders_abundant_stock_information(): void
{
    // ...
    $response->assertStatus(200);
    $response->assertSee('Tersedia: 8 unit');
    $response->assertSee('Ketersediaan Stok');
    $response->assertSee('8 unit (Ready Stock / Siap Kirim)');
    $response->assertDontSee('Tersisa 8 unit'); // Memastikan hint sampingan terhapus
}

public function test_detail_page_renders_low_stock_warning_information(): void
{
    // ...
    $response->assertStatus(200);
    $response->assertSee('Sisa 2 unit lagi (Segera Habis!)');
    $response->assertSee('Sisa 2 unit (Segera Habis)');
    $response->assertDontSee('Tersisa 2 unit'); // Memastikan hint sampingan terhapus
}

public function test_detail_page_renders_out_of_stock_information(): void
{
    // ...
    $response->assertStatus(200);
    $response->assertSee('Stok Habis');
    $response->assertSee('Habis (Sedang Diproduksi Ulang)');
    $response->assertSee('is-out-of-stock'); // Memastikan tata letak vertikal aktif
}

public function test_detail_page_renders_custom_product_preorder_information(): void
{
    // ...
    $response->assertStatus(200);
    $response->assertSee('Pre-Order / Dibuat Sesuai Pesanan');
    $response->assertDontSee('Made by Order'); // Memastikan hint sampingan terhapus
    $response->assertSee('Pre-Order (Dibuat Sesuai Pesanan)');
}
```

---

## 4. Hasil Verifikasi & Pengujian

### A. Pengujian Otomatis (PHPUnit)
- Perintah: `php artisan test`
- Hasil: **110 passed (522 assertions)** &mdash; 100% hijau (*pass*), 0 regresi.

### B. Pengujian Visual Headless Browser
1. **Produk Stok Tersedia (`/product/1`)**:
   - `hasQtyHint`: `false` (tidak ada badge sampingan).
   - `.pi-actions` hanya berisi 2 elemen aktif: `.qty-box` dan `.btn-buy` berdampingan rapi secara horizontal.
2. **Produk Stok Habis (`/product/3`)**:
   - `hasQtyHint`: `false`.
   - `flexDirection`: `"column"`.
   - `qty-box`: `top: 685.375px`, `left: 787.25px`, `width: 135px`, `height: 48px`.
   - `btn-buy.disabled`: `top: 747.375px`, `left: 787.25px`, `width: 360px`, `height: 48px`.
   - Koordinat `left` persis sejajar, dan jarak vertikal antar elemen sebesar $14\text{ px}$.

---

## 5. Ringkasan File yang Diubah

| No | Berkas | Perubahan |
| :--- | :--- | :--- |
| 1 | `resources/views/Pelanggan/detail.blade.php` | Menghapus elemen `.pi-qty-hint` di samping tombol aksi dan menambahkan modifier `.is-out-of-stock`. |
| 2 | `resources/css/pelanggan_custom.css` | Menambahkan aturan styling `.pi-actions.is-out-of-stock` dengan `flex-direction: column` dan `max-width: 360px`. |
| 3 | `tests/Feature/ProductDetailStockInfoTest.php` | Mengunci kontrak tampilan bahwa teks hint sampingan tidak dirender lagi dan modifier `is-out-of-stock` aktif saat stok 0. |
| 4 | `docs/penambahan_informasi_stok_detail_dan_modal_preview.md` | Memperbarui dokumentasi fitur stok secara keseluruhan. |
| 5 | `docs/perbaikan_tata_letak_tombol_stok_habis_dan_penghapusan_qty_hint.md` | Berkas dokumentasi perbaikan khusus untuk perubahan ini. |
