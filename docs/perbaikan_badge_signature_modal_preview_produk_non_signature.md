# Perbaikan Lencana Signature pada Modal Preview Produk Non-Signature (Quick View)

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Pada tampilan katalog produk (halaman Shop `/shop`), produk biasa seperti **Cheese Sago Cookies** (dengan `is_signature = false` atau `0` di database) tidak memiliki lencana *Zyh Signature* pada kartu katalog produk. Namun, ketika pengguna menekan tombol *Quick View* (ikon mata) untuk melihat modal *preview* produk, lencana **"ZYH SIGNATURE"** dengan ikon mahkota justru muncul di bagian atas nama produk berdampingan dengan kategori *CEMILAN & SNACK*.

### Akar Masalah (*Root Cause*)
Masalah ini disebabkan oleh **konflik spesifisitas CSS (*CSS Specificity & `!important` declaration*)**:

1. Elemen lencana signature pada modal di `resources/views/Layout/layout_pelanggan.blade.php` memiliki ID `#pmSignatureBadge` dan kelas `.signature-badge.eyebrow-signature.pm-signature-badge`.
2. Di berkas stylesheet (`resources/css/pelanggan_pages.css`, `resources/css/pelanggan.css`, dan `public/css/pelanggan.css`), aturan kelas `.pm-signature-badge` dan `.eyebrow-signature` didefinisikan dengan properti:
   ```css
   display: inline-flex !important;
   ```
3. Ketika JavaScript `openProductModal(btn)` di `resources/js/pelanggan_pages.js` memeriksa status signature:
   ```javascript
   if (data.isSignature === '1' || data.signature === '1') {
       pmSignatureBadge.style.display = 'inline-flex';
   } else {
       pmSignatureBadge.style.display = 'none';
   }
   ```
   Eksekusi `pmSignatureBadge.style.display = 'none'` menghasilkan atribut inline `style="display: none;"` (tanpa `!important`).
4. Berdasarkan aturan prioritas CSS (*CSS Cascading & Specificity*), deklarasi inline tanpa `!important` kalah dari deklarasi author stylesheet yang menggunakan `!important`. Akibatnya, browser tetap memaksakan `display: inline-flex !important`, sehingga lencana selalu terlihat pada **setiap produk** tanpa mempedulikan nilai `is_signature`.

---

## 2. Pedoman Perbaikan (`frontend-design-direction`)

Mengacu pada prinsip **`frontend-design-direction`**:
1. **Eksklusivitas Brand (*Brand Integrity & Hierarchy*)**: Lencana signature adalah tanda kehormatan resep unggulan (*curated master recipe*), sehingga hanya boleh dirender untuk produk yang benar-benar terdaftar sebagai signature di sistem.
2. **Robust Visibility Contract**: Menghilangkan ketergantungan rapuh pada inline style biasa, menghilangkan `display: ... !important` destruktif dari kelas dasar, dan menambahkan utility status kelas khusus `.is-hidden` yang memiliki prioritas mutlak (`display: none !important;`).
3. **Zero Cumulative Layout Shift (CLS)**: Struktur `.pm-eyebrow-row` tetap stabil saat badge disembunyikan tanpa menggeser margin atau perataan teks nama produk.

---

## 3. Berkas yang Diubah

| No | Berkas | Deskripsi Perubahan |
|---|---|---|
| 1 | `resources/css/pelanggan_pages.css` | Menghapus `!important` dari `display: inline-flex` pada `.pm-signature-badge` dan `.eyebrow-signature`, serta menambahkan aturan kelas `.pm-signature-badge.is-hidden { display: none !important; }`. |
| 2 | `resources/css/pelanggan.css` | Penyelarasan aturan CSS dasar yang identik dengan `pelanggan_pages.css`. |
| 3 | `public/css/pelanggan.css` | Penyelarasan aset statis publik CSS pelanggan. |
| 4 | `resources/js/pelanggan_pages.js` | Mengubah logika `openProductModal(btn)` agar menggunakan `style.setProperty('display', 'none'/'inline-flex', 'important')` sekaligus mengaktifkan/menonaktifkan kelas `.is-hidden`. |
| 5 | `resources/views/Layout/layout_pelanggan.blade.php` | Menambahkan kelas awal `is-hidden` dan inline style `display: none !important;` pada elemen `#pmSignatureBadge`. |
| 6 | `tests/Feature/SignatureProductsTest.php` | Menambahkan pengujian otomatis `test_shop_and_modal_render_correct_signature_data_attribute_and_hidden_initial_state()`. |

---

## 4. Rincian Perubahan Kode

### 4.1. `resources/css/pelanggan_pages.css`

```css
/* SEBELUM: */
.pm-signature-badge {
    position: static !important;
    top: auto !important;
    left: auto !important;
    display: inline-flex !important;
    ...
}

.eyebrow-signature,
.signature-badge.eyebrow-signature {
    position: static !important;
    display: inline-flex !important;
    ...
}

/* SESUDAH: */
.pm-signature-badge {
    position: static !important;
    top: auto !important;
    left: auto !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fde68a;
    padding: 2px 8px;
    border-radius: 20px;
    box-shadow: none;
    line-height: 1.2;
}

.pm-signature-badge.is-hidden {
    display: none !important;
}

.eyebrow-signature,
.signature-badge.eyebrow-signature {
    position: static !important;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    ...
}
```

---

### 4.2. `resources/js/pelanggan_pages.js`

```javascript
// SEBELUM:
const pmSignatureBadge = document.getElementById('pmSignatureBadge');
if (pmSignatureBadge) {
    if (data.isSignature === '1' || data.signature === '1') {
        pmSignatureBadge.style.display = 'inline-flex';
    } else {
        pmSignatureBadge.style.display = 'none';
    }
}

// SESUDAH:
const pmSignatureBadge = document.getElementById('pmSignatureBadge');
if (pmSignatureBadge) {
    const isSig = (data.isSignature === '1' || data.isSignature === 'true' || data.isSignature === true || data.signature === '1');
    if (isSig) {
        pmSignatureBadge.style.setProperty('display', 'inline-flex', 'important');
        pmSignatureBadge.classList.remove('is-hidden');
    } else {
        pmSignatureBadge.style.setProperty('display', 'none', 'important');
        pmSignatureBadge.classList.add('is-hidden');
    }
}
```

---

### 4.3. `resources/views/Layout/layout_pelanggan.blade.php`

```html
<!-- SEBELUM: -->
<span class="signature-badge eyebrow-signature pm-signature-badge" id="pmSignatureBadge" style="display: none;">
    <i class="fa-solid fa-crown"></i> Zyh Signature
</span>

<!-- SESUDAH: -->
<span class="signature-badge eyebrow-signature pm-signature-badge is-hidden" id="pmSignatureBadge" style="display: none !important;">
    <i class="fa-solid fa-crown"></i> Zyh Signature
</span>
```

---

### 4.4. `tests/Feature/SignatureProductsTest.php`

Menambahkan test case baru untuk menguji kontrak atribut `data-is-signature` pada halaman toko dan status inisial lencana modal:

```php
public function test_shop_and_modal_render_correct_signature_data_attribute_and_hidden_initial_state(): void
{
    $kategori = Kategori::create([
        'nama_kategori' => 'Cemilan & Snack',
        'gambar_kategori' => 'snack.jpg'
    ]);

    $regularProduct = Produk::create([
        'nama_produk' => 'Cheese Sago Cookies',
        'id_kategori' => $kategori->id_kategori,
        'jenis_produk' => 'showcase',
        'harga' => 45000,
        'status_produk' => '6',
        'is_signature' => false,
    ]);

    $signatureProduct = Produk::create([
        'nama_produk' => 'Black Forest Cake Classic',
        'id_kategori' => $kategori->id_kategori,
        'jenis_produk' => 'showcase',
        'harga' => 125000,
        'status_produk' => '10',
        'is_signature' => true,
    ]);

    $response = $this->get(route('shop'));
    $response->assertStatus(200);

    // Produk reguler wajib memiliki data-is-signature="0"
    $response->assertSee('data-name="Cheese Sago Cookies"', false);
    $response->assertSee('data-is-signature="0"', false);

    // Produk signature wajib memiliki data-is-signature="1"
    $response->assertSee('data-name="Black Forest Cake Classic"', false);
    $response->assertSee('data-is-signature="1"', false);

    // Lencana signature pada modal layout wajib memiliki kelas is-hidden dan inline style !important
    $response->assertSee('id="pmSignatureBadge"', false);
    $response->assertSee('class="signature-badge eyebrow-signature pm-signature-badge is-hidden"', false);
    $response->assertSee('style="display: none !important;"', false);
}
```

---

## 5. Hasil Pengujian & Verifikasi

1. **Vite Compilation**:
   ```bash
   npm run build
   ```
   *Output*: Berhasil (`built in 693ms`).

2. **Automated Feature Test**:
   ```bash
   php artisan test --filter=SignatureProductsTest
   ```
   *Output*: **10 passed** (48 assertions).

3. **Full Application Test Suite**:
   ```bash
   php artisan test && git clean -fd public/uploads/
   ```
   *Output*: **221 passed** (1195 assertions) — 100% lulus tanpa kegagalan atau regresi.
