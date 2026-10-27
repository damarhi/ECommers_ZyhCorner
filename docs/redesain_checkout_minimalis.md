# Dokumentasi Perubahan Kode: Redesain Halaman Checkout Menjadi Minimalis

**Tanggal Pelaksanaan:** 23 Oktober 2026  
**Status:** Selesai, Terverifikasi, dan Lulus Seluruh Unit & Feature Tests  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Latar Belakang & Prinsip Desain Minimalis

Penyempurnaan halaman Checkout Pelanggan (`/checkout`) dilakukan dengan filosofi **Clean, Calm, and Friction-Free Minimalist UI**. Tujuannya adalah mengeliminasi kebisingan visual (*visual noise*) agar fokus pengguna tertuju secara jernih pada penyelesaian pesanan pastry & cake mereka tanpa distraksi.

### Prinsip Utama yang Diterapkan:
1. **Hairline Precision Borders:** Menggantikan border tebal ganda dengan garis netral ramping `1px solid #e2e8f0` dan `1px solid #cbd5e1`.
2. **Subtle Elevation & Ambient Shadows:** Menghilangkan bayangan drop-shadow pekat bernilai tebal, digantikan dengan *ambient light shadow* (`0 1px 3px rgba(15, 23, 42, 0.03)` pada kartu formulir dan `0 4px 16px -2px rgba(15, 23, 42, 0.04)` pada summary card).
3. **Refined Micro-Badges & Step Indicators:** Mengganti lingkaran badge hijau pekat yang tebal dengan indikator langkah bersahaja (`28px` rounded-square dengan latar lembut `#f0fdf4`, teks `#00b207`, dan border `1px solid #bbf7d0`).
4. **Soft-Warm Notice Alert:** Mengubah banner peringatan ketentuan ucapan kue yang awalnya berwarna kuning mustard pekat menjadi kotak edukasi yang hangat, bersih, dan bersahabat (`background: #fefce8; border: 1px solid #fef08a; border-left: 3px solid #eab308;`).
5. **Clean Micro-Copy Kurir:** Menata deskripsi kurir pengiriman menjadi ringkas dan padat 1 baris (contoh: "Pengantaran kilat 30–60 menit").
6. **Zero Scroll Trap:** Mempertahankan kebebasan scroll alami pada kartu ringkasan pesanan sticky di sisi kanan.

---

## 2. Rincian Perubahan Kode

### A. Template Blade: `resources/views/Pelanggan/checkout.blade.php`

1. **Penyederhanaan Label & Input Alamat:**
   - Menghapus pewarnaan inline yang berlebihan pada ikon label.
   - Menambahkan class `.label-optional` untuk teks `(Opsional)` agar tampil tenang dan konsisten.
   - Merapikan markup `.map-note` pada peta Leaflet.

   ```html
   <div class="form-group-custom">
       <label for="streetAddress">
           <i class="fa-solid fa-location-dot"></i> Alamat Lengkap
       </label>
       <input type="text" id="streetAddress" class="form-input-custom" value="{{ Auth::user()->alamat ?? '' }}" placeholder="Detail jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required>
   </div>

   <div class="form-group-custom">
       <label>
           <i class="fa-solid fa-map-location-dot"></i> Titik Lokasi Pengantaran di Peta
       </label>
       <div class="map-wrapper">
           <div id="map"></div>
           <div class="map-note">
               <i class="fa-solid fa-location-crosshairs"></i>
               <span id="coordsDisplay">Klik peta atau seret marker untuk menandai lokasi tepat Anda</span>
           </div>
       </div>
       <input type="hidden" id="latitude" name="latitude">
       <input type="hidden" id="longitude" name="longitude">
   </div>
   ```

2. **Penyelarasan Teks Kartu Kurir Pengiriman (Langkah 2):**
   - Menata ulang teks deskripsi kurir agar lebih ringkas, to-the-point, dan proporsional dalam grid 2x2.

   ```html
   <div class="shipping-grid">
       <label class="shipping-card-option active" onclick="selectShippingOption(this)">
           <input type="radio" name="shippingGroup" value="Maxim" checked>
           <div class="shipping-card-content">
               <div class="shipping-card-top">
                   <span class="shipping-icon"><i class="fa-solid fa-motorcycle"></i></span>
                   <span class="shipping-name">Maxim Instant</span>
                   <span class="shipping-badge">Populer</span>
               </div>
               <span class="shipping-desc">Pengantaran kilat 30–60 menit</span>
           </div>
       </label>
       <!-- Opsi kurir Kuantar, Iron, Pick Up dengan struktur serupa -->
   </div>
   ```

3. **Banner Edukasi Tulisan Kue (Langkah 3):**
   - Menggunakan ikon informatif halus `fa-circle-info` dan tata letak teks yang bersahabat:

   ```html
   <div class="custom-cake-notice">
       <div class="notice-icon">
           <i class="fa-solid fa-circle-info"></i>
       </div>
       <div class="notice-body">
           <strong>Ketentuan Penulisan Ucapan di Atas Kue:</strong>
           <p>Mohon maaf, toko kami <strong>TIDAK MENERIMA</strong> penulisan ucapan <em>Selamat Ulang Tahun (HBD / Ultah)</em>, <em>Barakallah Fii Umrik</em>, <em>Anniversary (Anniv)</em>, serta <strong>angka</strong> dalam bentuk apa pun di atas kue.</p>
           <p class="notice-examples">Contoh ucapan yang diperbolehkan: "Selamat Wisuda", "Semangat Kerja", "Terima Kasih", "Congratulations", dll.</p>
       </div>
   </div>
   ```

4. **Trust Badges Minimalis di Bawah Tombol CTA:**
   - Menghubungkan 3 poin kepercayaan (*Security & Quality*) dengan pembatas putus-putus tipis yang elegan:

   ```html
   <div class="checkout-trust-box">
       <div class="trust-item">
           <i class="fa-solid fa-shield-halved"></i>
           <span>Pembayaran Aman & Terenkripsi</span>
       </div>
       <div class="trust-item">
           <i class="fa-solid fa-cake-candles"></i>
           <span>Pastry Segar Dibuat Setiap Hari</span>
       </div>
       <div class="trust-item">
           <i class="fa-solid fa-clock-rotate-left"></i>
           <span>Konfirmasi Pesanan Cepat</span>
       </div>
   </div>
   ```

---

### B. Lembar Gaya CSS: `resources/css/pelanggan_custom.css`

1. **Card & Header Steps Minimalis:**
   ```css
   .checkout-card {
       background: #ffffff;
       border: 1px solid #e2e8f0;
       border-radius: 12px;
       padding: 24px;
       margin-bottom: 20px;
       box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
       transition: border-color 0.2s, box-shadow 0.2s;
   }

   .checkout-step-header {
       display: flex;
       align-items: center;
       gap: 14px;
       margin-bottom: 20px;
       border-bottom: 1px solid #f1f5f9;
       padding-bottom: 14px;
   }

   .step-badge {
       width: 28px;
       height: 28px;
       border-radius: 8px;
       background: #f0fdf4;
       color: #00b207;
       font-weight: 700;
       font-size: 13px;
       display: flex;
       align-items: center;
       justify-content: center;
       flex-shrink: 0;
       border: 1px solid #bbf7d0;
       box-shadow: none;
   }
   ```

2. **Form Controls & Focus Rings:**
   ```css
   .form-input-custom, .form-textarea-custom {
       padding: 10px 14px;
       border: 1px solid #cbd5e1;
       border-radius: 8px;
       font-size: 13.5px;
       outline: none;
       transition: all 0.2s ease;
       width: 100%;
       background: #ffffff;
       color: #1e293b;
       font-family: inherit;
   }
   .form-input-custom:focus, .form-textarea-custom:focus {
       border-color: #00b207;
       box-shadow: 0 0 0 3px rgba(0, 178, 7, 0.1);
       background: #ffffff;
   }
   ```

3. **Kartu Kurir & Pembayaran Flat Selectable:**
   ```css
   .shipping-card-option {
       position: relative;
       display: flex;
       align-items: flex-start;
       border: 1px solid #e2e8f0;
       border-radius: 10px;
       padding: 11px 13px;
       cursor: pointer;
       background: #ffffff;
       transition: border-color 0.2s, background 0.2s;
       user-select: none;
   }
   .shipping-card-option:hover {
       border-color: #cbd5e1;
   }
   .shipping-card-option.active {
       border-color: #00b207;
       background: #f0fdf4;
   }
   ```

4. **Kotak Peringatan Kue Kustom yang Tenang (*Soft Amber Notice*):**
   ```css
   .custom-cake-notice {
       display: flex;
       align-items: flex-start;
       gap: 12px;
       background: #fefce8;
       border: 1px solid #fef08a;
       border-left: 3px solid #eab308;
       border-radius: 8px;
       padding: 12px 14px;
       margin-bottom: 18px;
   }
   .notice-icon {
       color: #ca8a04;
       font-size: 16px;
       flex-shrink: 0;
       margin-top: 1px;
   }
   .notice-body {
       font-size: 12.5px;
       color: #713f12;
       line-height: 1.5;
   }
   ```

5. **Tombol CTA & Trust Badges Ramping:**
   ```css
   .btn-place-order {
       display: flex;
       align-items: center;
       justify-content: center;
       gap: 8px;
       width: 100%;
       background: #00b207;
       color: white;
       border: none;
       border-radius: 10px;
       padding: 14px 18px;
       font-size: 15px;
       font-weight: 700;
       cursor: pointer;
       transition: all 0.2s ease;
       box-shadow: 0 2px 8px rgba(0, 178, 7, 0.2);
   }
   .btn-place-order:hover {
       background: #009906;
       transform: translateY(-1px);
       box-shadow: 0 4px 12px rgba(0, 178, 7, 0.3);
   }
   .checkout-trust-box {
       display: flex;
       flex-direction: column;
       gap: 8px;
       margin-top: 18px;
       padding-top: 14px;
       border-top: 1px dashed #e2e8f0;
   }
   .trust-item {
       display: flex;
       align-items: center;
       gap: 8px;
       font-size: 11.5px;
       color: #64748b;
   }
   ```

---

## 3. Hasil Pengujian & Verifikasi

1. **Uji Fitur Khusus: `CheckoutDesignTest`**
   ```bash
   php artisan test --filter=CheckoutDesignTest
   # PASS (38 assertions) - 0.63s
   ```
2. **Kompilasi Aset Frontend (Vite):**
   ```bash
   npm run build
   # public/build/assets/pelanggan_pages-CIED0tmo.css (100.39 kB)
   # public/build/assets/pelanggan_pages-BYUocfiY.js (63.96 kB)
   # Built in 448ms
   ```
3. **Pembersihan Cache View Blade:**
   ```bash
   php artisan view:clear
   # INFO Compiled views cleared successfully.
   ```
4. **Uji Regresi Penuh Seluruh Modul:**
   ```bash
   php artisan test
   # Tests: 91 passed (408 assertions) - 18.67s
   ```
   Seluruh 91 tests pada sistem berjalan mulus dan lulus 100%.
