# Dokumentasi Perubahan Kode: Pelebaran Card Produk & Efek Hover Tombol Checkout

**Tanggal:** 23 Oktober 2026  
**Status:** Selesai & Terverifikasi  
**Terkait:** `/cart` (`resources/views/Pelanggan/userChart.blade.php`), `resources/css/pelanggan_custom.css`, `resources/js/pelanggan_pages.js`

---

## 1. Latar Belakang & Masalah
1. **Munculnya Scrollbar Horizontal pada Card Produk**:
   Pada tata letak berdampingan sebelumnya, `.cart-container` dibatasi pada lebar $1200\text{px}$ dan rasio grid `1fr 380px`. Hal ini menyebabkan lebar terlihat (*clientWidth*) kartu produk hanya $746\text{px}$, sedangkan tabel produk membutuhkan ruang sebesar $760\text{px}$. Selisih sempit sebesar $14\text{px}$ tersebut memicu browser menampilkan *horizontal scrollbar* (slidebar geser) di bagian bawah kartu produk.
2. **Ketiadaan Efek Hover pada Tombol Checkout**:
   Tombol *"Proceed to Checkout"* dirender dengan atribut inline `style="background: #024220; ..."` di JavaScript, yang secara spesifikasi CSS menimpa (*override*) dan mengabaikan selektor pseudo-class `:hover` dari file stylesheet eksternal.

---

## 2. Arah Desain & Solusi (*Frontend Design Direction*)
1. **Bebas Scrollbar (*Zero-Scrollbar Friction*)**:
   - Memperluas `.cart-container` menjadi `max-width: 1280px` dengan padding samping yang proporsional.
   - Menyesuaikan pembagian grid menjadi `1fr 360px` (kolom Cart Totals $360\text{px}$).
   - Hasilnya, lebar kartu produk meningkat dari $746\text{px}$ menjadi **$838\text{px}$**, memberikan ruang cadangan lega sebesar $+78\text{px}$ sehingga tabel produk muat sepenuhnya tanpa *horizontal scrollbar* (`hasHorizontalScrollbar: false`).
2. **Efek Hover Interaktif (*Tactile Feedback*)**:
   - Menghapus atribut inline warna latar pada JavaScript dan mendelegasikannya ke kelas `.btn-checkout-green`.
   - Mengimplementasikan efek hover elegan:
     - Warna latar bergeser dari hijau botol `#024220` ke hijau hutan gelap `#012d16`.
     - *Elevasi tombol*: `transform: translateY(-2px);` disertai bayangan yang lebih tegas `box-shadow: 0 8px 24px rgba(2, 66, 32, 0.35)`.
     - Ikon panah kanan (`i.fa-arrow-right`) bergeser halus sejauh `4px` ke kanan (`transform: translateX(4px)`).
     - *Press state* saat ditekan (`:active`): `transform: translateY(0); box-shadow: 0 3px 10px rgba(2, 66, 32, 0.2)`.

---

## 3. Rincian Perubahan Kode

### A. Berkas `resources/css/pelanggan_custom.css`
Memperbarui dimensi container keranjang, layout grid 2-kolom, dan menambahkan styling hover untuk tombol checkout:

```css
/* Cart Layout */
.cart-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 50px 24px 80px;
    width: 100%;
    box-sizing: border-box;
}
.cart-title {
    text-align: center;
    font-size: 32px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 40px;
}

.cart-grid,
.cart-layout-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 32px;
    align-items: start;
}

/* Button Checkout & Micro-interactions */
.btn-checkout-green {
    display: inline-flex;
    width: 100%;
    background-color: #024220;
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 15px 24px;
    font-size: 15.5px;
    font-weight: 600;
    cursor: pointer;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(2, 66, 32, 0.22);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}
.btn-checkout-green i {
    transition: transform 0.25s ease;
}
.btn-checkout-green:hover {
    background-color: #012d16;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(2, 66, 32, 0.35);
}
.btn-checkout-green:hover i {
    transform: translateX(4px);
}
.btn-checkout-green:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(2, 66, 32, 0.2);
}
```

---

### B. Berkas `resources/js/pelanggan_pages.js`
Menghapus atribut `style="..."` inline pada tombol checkout di fungsi `renderCartPage()`:

```javascript
// Sebelum:
<a href="${window.layoutConfig?.checkoutRoute || '/checkout'}" class="btn-checkout-green" style="display: flex; justify-content: center; align-items: center; width: 100%; background: #024220; color: #ffffff; border-radius: 50px; padding: 14px 20px; font-size: 15px; font-weight: 600; text-decoration: none; transition: background 0.2s; box-shadow: 0 4px 14px rgba(2, 66, 32, 0.2);">
    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
</a>

// Sesudah:
<a href="${window.layoutConfig?.checkoutRoute || '/checkout'}" class="btn-checkout-green">
    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
</a>
```

---

## 4. Hasil Verifikasi Nyata (Headless Browser)

### A. Uji Ketiadaan Scrollbar pada Desktop ($1440 \times 900$)
- **Lebar Kontainer**: $1280\text{px}$
- **Lebar Kolom Kiri (*Card Produk*)**: $838\text{px}$
- **Kebutuhan Lebar Tabel (*ScrollWidth*)**: $838\text{px}$
- **Status Scrollbar Horizontal**: `hasHorizontalScrollbar: false` (**TERBUKTI TIDAK ADA SCROLLBAR**)
- **Lebar Kolom Kanan (*Cart Totals*)**: $358\text{px}$

### B. Uji Efek Hover Tombol Checkout
- **Status Normal**:
  - Warna: `rgb(2, 66, 32)`
  - Bayangan: `rgba(2, 66, 32, 0.22) 0px 4px 14px`
- **Status Hover (`:hover`)**:
  - Warna Latar: `rgb(1, 45, 22)`
  - Elevasi: `transform: translateY(-2px)`
  - Bayangan Lebih Pekat: `rgba(2, 66, 32, 0.35) 0px 8px 24px`
  - Ikon Panah Geser: `transform: translateX(4px)`

### C. Uji Responsivitas Mobile ($390 \times 844$)
- Kolom kartu produk dan kartu Cart Totals otomatis bertumpuk 1 kolom vertikal selebar $358\text{px}$ tanpa *overflow* atau benturan batas layar.

### D. Build & Cache
- `npm run build` sukses dalam $467\text{ms}$.
- `php artisan view:clear` sukses membersihkan compiled view.
