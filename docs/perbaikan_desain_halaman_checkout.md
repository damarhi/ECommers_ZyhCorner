# Dokumentasi Perbaikan Desain & Restrukturisasi Halaman Checkout

**Tanggal:** 23 Oktober 2026  
**Status:** Selesai, Terintegrasi & Terverifikasi  
**Terkait:**  
- `resources/views/Pelanggan/checkout.blade.php`  
- `resources/css/pelanggan_custom.css`  
- `resources/js/pelanggan_pages.js`  
- `tests/Feature/CheckoutDesignTest.php`  

---

## 1. Latar Belakang & Masalah

Halaman checkout pelanggan sebelumnya memiliki beberapa kekurangan struktural, hierarki antarmuka, dan estetika yang mengganggu kenyamanan pengguna:

1. **Typo Judul Kolom Ringkasan**:
   Judul ringkasan pesanan pada kartu kanan sebelumnya tertulis `"Order Summery"` (typo bahasa Inggris), tidak konsisten dengan bahasa antarmuka aplikasi berbahasa Indonesia dan tampak tidak profesional.
2. **Fragmentasi Kartu Kanan & Peletakan Tombol CTA yang Membingungkan**:
   Kolom kanan terpecah menjadi 3 kartu terpisah (*Order Summary*, *Metode Pengiriman*, dan *Metode Pembayaran*). Tombol submit pesanan (`.btn-place-order`) justru diletakkan di dalam kartu Metode Pengiriman dan bukan di bagian akhir setelah memilih pembayaran, menyebabkan hierarki visual yang membingungkan.
3. **Struktur Form HTML Rusak (*Unclosed Tag*)**:
   Terdapat tag unclosed `<div class="form-group-custom">` pada baris form catatan pesanan di `checkout.blade.php`, merusak kerapian rendering DOM.
4. **Elemen Pilihan yang Kaku & Monoton**:
   Pilihan kurir pengiriman dan metode pembayaran hanya berupa radio button vertikal biasa tanpa kartu interaktif (*radio cards*), tanpa penanda badge populer/gratis, dan tanpa logo bank/e-wallet.
5. **Daftar Produk yang Terlalu Sempit**:
   Thumbnail produk berukuran kecil ($45\times 45\text{px}$) dengan teks judul yang dipotong secara agresif (`max-width: 150px`), ketiadaan penanda total kuantitas produk, serta format teks ucapan kue kustom yang belum terintegrasi estetis.

---

## 2. Arah Desain & Solusi (*Frontend Design Direction*)

Mengacu pada prinsip arsitektur UX *e-commerce checkout flow* modern (seperti standar Shopify, Tokopedia, dan Apple Store), halaman ditata ulang dengan alur 2 kolom yang terstruktur:

### A. Kolom Kiri: Alur Pemenuhan Pesanan Berurutan (4 Langkah)
Setiap langkah dipandu dengan indikator nomor sirkular hijau (`.step-badge` berlatar `#00b207` dan efek bayangan lembut):
1. **Langkah 1: Alamat Pengiriman & Titik Lokasi**
   - Field alamat jalan lengkap dengan input focus glow hijau.
   - Peta Leaflet interaktif (`#map`) dengan wrapper border rapi dan indikator koordinat (`#coordsDisplay`).
   - Field catatan pengiriman (`#orderNotes`).
2. **Langkah 2: Tulisan di Atas Kue [Opsional] (`#customCakeTextCard`)**
   - Ditampilkan kondisional bila keranjang berisi kue berfitur custom text.
   - Dilengkapi *notice warning banner* bergaya amber lembut mengenai ketentuan teks ucapan yang diperbolehkan.
   - Wadah input per produk (`#customTextInputsContainer`).
3. **Langkah 3: Metode Pengiriman**
   - Disajikan dalam tata letak kartu grid 2-kolom (`.shipping-grid`).
   - Opsi: **Maxim Instant** (badge *Populer*), **Kuantar Same Day**, **Iron Courier**, dan **Ambil di Toko (Pick Up)** (badge hijau *Gratis*).
   - Efek kartu interaktif: saat dipilih, kartu mendapatkan border hijau `#00b207`, latar lembut `#f0fdf4`, dan bayangan bercahaya.
4. **Langkah 4: Metode Pembayaran**
   - **Cash on Delivery (COD)**: Badge tunai untuk pembayaran di tempat.
   - **Transfer Bank & Virtual Account**: Dilengkapi preview badge bank (BCA, BRI, Mandiri) dan sub-opsi pemilihan rekening bank (*bank chips grid*).
   - **E-Wallet & QRIS**: Dilengkapi badge merah QRIS Universal dan sub-opsi e-wallet (GoPay, OVO, ShopeePay, DANA).

### B. Kolom Kanan: *Sticky Order Summary Card* Terpadu
- **Posisi Sticky**: Mengikuti posisi layar saat pengguna menggulir halaman panjang (`position: sticky; top: 90px`).
- **Judul Terkoreksi & Badge Jumlah**: Judul `"Ringkasan Pesanan"` dilengkapi pill badge dinamis (`#summaryItemCount`).
- **Thumbnail Produk Menarik**: Ukuran gambar $52\times 52\text{px}$ dengan border halus, judul leluasa, dan tag teks ucapan kue bergaya pil biru (*pen-nib icon*).
- **Rincian Biaya Transparan**: Rincian subtotal produk, pengiriman gratis, dan total pembayaran dengan tipografi tebal hijau $20\text{px}$.
- **Tombol Utama *Place Order***: Tombol *"Buat Pesanan Sekarang"* dengan ikon panah, efek hover elevasi, dan bayangan hijau tegas (`box-shadow: 0 4px 14px rgba(0, 178, 7, 0.35)`).
- **Trust Badges Kepercayaan**: Ikon jaminan keamanan transaksi terenkripsi, pastry segar harian, dan konfirmasi cepat.

---

## 3. Kompatibilitas Kontrak Form & Backend

Seluruh atribut `id`, `name`, dan `class` yang menjadi kontrak backend `POST /checkout` (`CartCheckoutController@processCheckout`) dan Midtrans Snap dipertahankan 100%:

| Atribut / ID Kontrak | Tipe Elemen | Deskripsi / Peran |
| :--- | :--- | :--- |
| `streetAddress` | `input[type="text"]` | Alamat jalan tujuan pengiriman |
| `map` | `div#map` | Container Leaflet Map |
| `latitude`, `longitude` | `input[type="hidden"]` | Koordinat GPS lokasi pengantaran |
| `coordsDisplay` | `span#coordsDisplay` | Teks status penandaan koordinat peta |
| `orderNotes` | `textarea#orderNotes` | Catatan khusus pesanan |
| `customCakeTextCard` | `div#customCakeTextCard` | Kontainer kondisional kartu tulisan kue |
| `customTextInputsContainer` | `div#...` | Wadah injeksi input teks kue via JS |
| `shippingGroup` | `input[name="shippingGroup"]` | Opsi kurir (`Maxim`, `Kuantar`, `Iron`, `Pick Up`) |
| `paymentGroup` | `input[name="paymentGroup"]` | Opsi pembayaran (`COD`, `Transfer`, `E-Wallet`) |
| `bankSelect` | `input[name="bankSelect"]` | Pilihan rekening transfer bank |
| `walletSelect` | `input[name="walletSelect"]` | Pilihan penyedia e-wallet |
| `orderItemsSummary` | `div#orderItemsSummary` | Wadah injeksi rincian produk checkout |
| `summarySubtotal` | `span#summarySubtotal` | Tampilan nilai subtotal produk |
| `summaryTotal` | `span#summaryTotal` | Tampilan total akhir transaksi |
| `btn-place-order` | `button.btn-place-order` | Pemicu eksekusi submit pesanan |

---

## 4. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Pelanggan/checkout.blade.php`)
- Menata ulang grid menjadi 2 kolom: `.checkout-main-col` (Langkah 1–4) dan `.checkout-sidebar-col` (Sticky Summary).
- Memperbaiki typo `"Order Summery"` $\rightarrow$ `"Ringkasan Pesanan"`.
- Menutup tag HTML yang bocor pada baris textarea catatan.
- Memindahkan tombol submit ke bagian bawah *Sticky Summary Card*.
- Menambahkan fungsi fallback `window.selectShippingOption(cardEl)`.

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)
- Mengganti gaya usang baris 7 s.d. 356 dengan:
  - Gaya kartu bernomor `.checkout-step-header` & `.step-badge`.
  - Tata letak responsif `.checkout-grid` (rasio `1.55fr 1fr` pada desktop, beralih ke `1fr` pada tablet/mobile).
  - Gaya interaktif `.shipping-card-option` dan `.payment-option-item` dengan transisi aktif `#00b207`.
  - Tata letak sticky `.sticky-summary-card`, `.summary-totals`, dan `.checkout-trust-box`.
  - Penataan thumbnail produk $52\times 52\text{px}$ dan chip teks ucapan kue kustom.

### C. Logika JavaScript (`resources/js/pelanggan_pages.js`)
- Memperbarui fungsi `window.renderOrderSummaryCheckout()`:
  - Menghitung total kuantitas produk dan memperbarui badge `#summaryItemCount`.
  - Merender thumbnail produk yang lebih besar dan rapi.
  - Menampilkan badge teks ucapan kustom kue (`item.custom_text`).
- Menambahkan fungsi `window.selectShippingOption(cardEl)` untuk menandai kartu kurir yang aktif dan mengaktifkan radio input terkait.
- Menyempurnakan `window.selectPaymentMethod(method)` untuk transisi sub-opsi bank dan e-wallet.

### D. Pengujian Otomatis (`tests/Feature/CheckoutDesignTest.php`)
Membuat feature test baru untuk memverifikasi secara otomatis:
- Akses halaman checkout mengembalikan HTTP 200 untuk pelanggan terotentikasi.
- Judul terkoreksi `"Ringkasan Pesanan"` muncul dan `"Order Summery"` tidak ada.
- Verifikasi keberadaan teks 4 langkah pemenuhan pesanan.
- Verifikasi lengkap 19 elemen id/name kontrak form dan backend.
- Verifikasi keberadaan trust badges.

---

## 5. Hasil Pengujian & Verifikasi

1. **Kompilasi Frontend (`npm run build`)**:
   ```bash
   vite v8.0.9 building client environment for production...
   ✓ 58 modules transformed.
   public/build/assets/pelanggan_pages-C1bYzs14.css  99.91 kB
   public/build/assets/pelanggan_pages-BYUocfiY.js   63.96 kB
   ✓ built in 461ms
   ```
2. **Pembersihan Cache View (`php artisan view:clear`)**:
   ```bash
   INFO Compiled views cleared successfully.
   ```
3. **Hasil PHPUnit Test Suite (`php artisan test`)**:
   ```bash
   Tests:    91 passed (400 assertions)
   Duration: 9.12s
   ```
   Seluruh 91 test cases (termasuk 30 assertions pada `CheckoutDesignTest`) lulus 100% tanpa regresi.
