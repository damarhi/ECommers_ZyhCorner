# Dokumentasi Eksekusi: Perbaikan Glitch Animasi Visi-Misi & Eliminasi Horizontal Overflow Halaman About Us

**Tanggal Eksekusi:** 5 November 2026  
**Target Halaman:** `resources/views/Pelanggan/about.blade.php`  
**Target Stylesheet:** `resources/css/pelanggan_pages.css`  
**Target Pengujian:** `tests/Feature/AboutUsTest.php`  
**Status Pengujian:** 221 Tests Passed (1196 Assertions) — 100% Lulus  
**Status Database MySQL:** Utuh & Tidak Terdampak (Users: 16, Products: 13, Orders: 25)

---

## 1. Latar Belakang & Gejala Permasalahan

Dua kendala visual dilaporkan pada tampilan halaman About Us (`/about`):
1. **Bagian Tujuan, Visi & Misi Menghilang Setelah Animasi:**
   Elemen kartu seksi filosofi (*Tujuan Usaha, Visi Kami, dan Misi Kami*) sempat muncul sebentar ketika halaman dimuat, namun langsung menghilang (*blank putih*) sesaat setelah animasi masuk selesai diputar.
2. **Adanya Bagian Putih di Sisi Kanan (Horizontal Scroll Overflow):**
   Pada tampilan layar peranti bergerak (*mobile viewport* $\le 768\text{px}$), layar dapat digeser sedikit ke samping kanan, memperlihatkan celah strip putih kosong (*white gap*) yang merusak tata letak responsif.

---

## 2. Analisis Akar Masalah (Root Cause Analysis)

### A. Penyebab Kartu Tujuan, Visi & Misi Menghilang
* **Benturan Tween GSAP vs Mesin CSS Transition:**  
  Pada skrip `resources/views/Pelanggan/about.blade.php`, elemen `.about-value-card` dianimasikan menggunakan GreenSock:
  ```javascript
  gsap.from('.about-value-card', {
      y: 25,
      opacity: 0,
      duration: 0.7,
      stagger: 0.15,
      ease: 'power2.out',
      delay: 0.2
  });
  ```
  Pada saat yang sama, berkas CSS `resources/css/pelanggan_pages.css` mendeklarasikan aturan transisi umum:
  ```css
  .about-value-card {
      transition: all 0.25s ease;
  }
  ```
* **Mekanisme Kegagalan:**  
  Sesuai prinsip teknis GreenSock (GSAP), transisi CSS pada properti yang dikontrol GSAP dilarang karena engine `transition: all` peramban mencoba menganimasikan ulang setiap frame interpolasi GSAP (60fps).  
  Ketika tween selesai atau diinterupsi oleh event rendering/scrolling peramban, elemen DOM terkunci (*stuck*) pada kondisi awal `opacity: 0`. Fenomena ini identik dengan insiden kartu produk kustom pada `docs/perbaikan_tampilan_produk_kustom_gsap_opacity.md`.

### B. Penyebab Celah Putih di Sisi Kanan (Horizontal Overflow)
* **Translasi Sumbu X pada Animasi Gambar GSAP:**  
  Elemen `.about-intro .intro-image` dianimasikan dengan perpindahan horizontal sumbu X:
  ```javascript
  gsap.from('.about-intro .intro-image', {
      x: 40, /* Sebelumnya x: 60 */
      opacity: 0,
      scale: 0.95,
      duration: 0.9,
      ease: 'power4.out',
      delay: 0.15
  });
  ```
* **Mekanisme Kegagalan:**  
  Perpindahan `x: 40` mendorong posisi gambar sejauh $40\text{px}$ ke arah kanan melewati batas lebar layar ponsel ($360\text{px} - 412\text{px}$).
* **Ketiadaan Pembatas Overflow pada Kontainer:**  
  Elemen pembungkus utama `.about-container` belum memiliki deklarasi `overflow-x: clip;` atau `overflow-x: hidden;`. Akibatnya, peramban memperlebar `scrollWidth` dokumen melebihi `clientWidth`, menghasilkan *horizontal scrollbar* dan celah putih di kanan.

---

## 3. Rincian Perubahan Kode Sumber

### A. Berkas `resources/views/Pelanggan/about.blade.php`
1. **Menghapus Pemanggilan `gsap.from('.about-value-card', ...)`:** Kartu inti Tujuan, Visi & Misi kini tampil instan, stabil, dan terbebas $100\%$ dari risiko tersangkut di `opacity: 0`.
2. **Mengubah Translasi Gambar Intro Menjadi Vertikal Sumbu Y:** Menghilangkan pergeseran `x: 40` dan menggantinya dengan translasi vertikal `y: 25` yang aman dari batas tepi kanan layar.
3. **Menambahkan `clearProps: 'all'`:** Memastikan GSAP menghapus bersih seluruh inline styles (`transform`, `opacity`) setelah animasi selesai diputar.

```diff
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             if (typeof gsap !== 'undefined') {
                 gsap.from('.about-intro .intro-text h2, .about-intro .intro-text p, .about-intro .about-section-badge', {
-                    y: 30,
+                    y: 25,
                     opacity: 0,
                     duration: 0.8,
                     stagger: 0.12,
-                    ease: 'power3.out'
+                    ease: 'power3.out',
+                    clearProps: 'all'
                 });
 
                 gsap.from('.about-intro .intro-image', {
-                    x: 40,
+                    y: 25,
                     opacity: 0,
-                    scale: 0.95,
-                    duration: 0.9,
-                    ease: 'power4.out',
-                    delay: 0.15
-                });
-
-                gsap.from('.about-value-card', {
-                    y: 25,
-                    opacity: 0,
-                    duration: 0.7,
-                    stagger: 0.15,
-                    ease: 'power2.out',
-                    delay: 0.2
+                    scale: 0.98,
+                    duration: 0.85,
+                    ease: 'power3.out',
+                    delay: 0.15,
+                    clearProps: 'all'
                 });
             }
         });
     </script>
```

---

### B. Berkas `resources/css/pelanggan_pages.css`
1. **Menambahkan Proteksi Horizontal Overflow:** Menambahkan `overflow-x: clip;` pada `.about-container` agar tidak ada elemen anak yang dapat meluber ke luar sumbu horizontal.
2. **Spesifikasi Properti Transisi CSS Kartu:** Mengubah `transition: all 0.25s ease;` menjadi transisi eksplisit hanya untuk properti interaksi hover (`transform`, `border-color`, `box-shadow`), sehingga tidak menyentuh properti `opacity`.

```diff
 .about-container {
     max-width: 1200px;
     margin: 0 auto;
     padding: 60px 15px;
     display: flex;
     flex-direction: column;
     gap: 80px;
     font-family: 'Poppins', sans-serif;
+    overflow-x: clip;
 }
 
 ...
 
 .about-value-card {
     background: #ffffff;
     border: 1px solid #e4ece5;
     border-radius: 16px;
     padding: 18px 20px;
     box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
-    transition: all 0.25s ease;
+    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
     position: relative;
 }
```

---

### C. Berkas `tests/Feature/AboutUsTest.php`
Menambahkan proteksi regresi otomatis untuk menjamin skrip GSAP tidak kembali menganimasikan `.about-value-card` di masa depan:

```diff
         $response->assertStatus(200);
         $response->assertSee('About Us');
         $response->assertSee('100% Trusted Organic Food Store');
+        // Ensure core value cards are not animated by GSAP to prevent opacity: 0 freeze
+        $response->assertDontSee("gsap.from('.about-value-card'");
```

---

## 4. Hasil Verifikasi

1. **Kompilasi Aset Produksi (Vite):**
   * Perintah: `npm run build`
   * Hasil: Berhasil terkompilasi dalam 557ms tanpa galat.
   * Modul terkompilasi: `public/build/assets/pelanggan_pages-wo0Rr-Bq.css` dan `pelanggan_pages-WRA3-Y-V.js`.
2. **Pengujian Otomatis (PHPUnit / Laravel Pest):**
   * Pengujian Khusus: `php artisan test tests/Feature/AboutUsTest.php` (1 passed, 4 assertions).
   * Pengujian Menyeluruh: `php artisan test` (221 passed, 1196 assertions).
   * Status: **100% PASSING**.
3. **Integritas Database MySQL Produksi (`pa-zyhcorner`):**
   * Users: **16**
   * Products: **13**
   * Orders: **25**
   * Seluruh pengujian berjalan menggunakan SQLite in-memory, tidak ada data MySQL yang dimodifikasi.
4. **Pembersihan Aset Sementara:**
   * File upload sementara pengujian di `public/uploads/avatars/` dan `public/uploads/returs/` telah dibersihkan.

---

## 5. Kesimpulan
Dengan eliminasi benturan `opacity` GSAP dan penguncian `overflow-x: clip`, bagian **Tujuan, Visi & Misi** kini selalu tampil instan dan stabil tanpa resiko menghilang setelah animasi. Selain itu, pergeseran gambar intro yang kini murni vertikal memastikan **tidak ada lagi celah putih di sisi kanan** pada layar peranti bergerak.
