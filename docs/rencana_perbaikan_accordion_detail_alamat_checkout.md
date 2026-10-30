# Rencana & Dokumentasi Teknis: Perbaikan Interaktivitas Accordion Detail Alamat Checkout

Dokumen ini disusun berdasarkan prinsip `/skill:frontend-design-direction` dan standar antarmuka Zyh Corner untuk mengidentifikasi penyebab panel detail alamat tidak muncul saat tombol/header *"Detail Alamat"* diklik pada halaman Checkout (`/checkout` &mdash; `resources/views/Pelanggan/checkout.blade.php`), serta menetapkan rencana perbaikan komprehensif dari aspek fungsionalitas, desain visual, aksesibilitas, dan stabilitas peta Leaflet.

---

## 1. Diagnosis Akar Masalah (Root Cause Analysis)

### A. Gejala yang Ditemukan
Saat pelanggan berada di halaman checkout dan mengklik header kartu atau tombol toggle berlabel **"Detail Alamat"**:
- Kontainer formulir alamat dan peta Leaflet (`#addressAccordionBody`) tetap tertutup (`max-height: 0px`).
- Ikon panah chevron (`#addressAccordionIcon`) tidak berputar 180 derajat.
- Teks label tombol (`#accordionToggleLabel`) tidak beralih menjadi *"Tutup Detail"*.
- Tidak terjadi respons visual apa pun pada antarmuka.

### B. Investigasi Teknis & Bukti Konsol Browser
Pengujian langsung menggunakan headless browser Chromium pada sesi pelanggan aktif mengungkap temuan berikut:
1. **Eksekusi Konsol Browser**:
   ```
   SyntaxError: Unexpected token ';'
   Location: http://127.0.0.1:8000/checkout:1082:10
   typeof window.toggleAddressAccordion: "undefined"
   ```
2. **Penyebab Kesalahan Sintaksis**:
   Pada blok pemrosesan pesanan `fetch(routeUrl, { ... })` di `checkout.blade.php` (baris 901–918), penambahan properti koordinat `latitude` dan `longitude` ke dalam payload `JSON.stringify({...})` mengalami kesalahan penutupan tanda kurung kurawal:
   ```javascript
   // Kode Bermasalah (Sebelum Perbaikan):
   body: JSON.stringify({
       items: localCart,
       address: streetAddress,
       notes: notes,
       // ...
       latitude: latitudeVal,
       longitude: longitudeVal
   }) // <- Kekurangan tanda kurung kurawal penutup '}' pada objek payload!
   .then(response => response.json())
   ```
3. **Dampak Sistemik ke Seluruh JavaScript Halaman**:
   Karena `JSON.stringify({ ...` tidak ditutup dengan benar, parser JavaScript pada browser klien melempar `SyntaxError` saat mengompilasi skrip inline `checkout.blade.php`. Akibatnya, **seluruh eksekusi skrip dibatalkan oleh engine JavaScript browser**:
   - `window.toggleAddressAccordion` tidak pernah terdefinisi (`undefined`).
   - `initAreaAutocomplete`, `renderCustomTextCard`, dan `window.updateOrderTotalsWithShipping` tidak dapat dijalankan.
   - Atribut HTML `onclick="toggleAddressAccordion()"` memicu kegagalan hening (*silent failure*) karena target fungsi tidak ditemukan.

4. **Mengapa Suite PHPUnit Tetap Lulus (*False Green*)**:
   Suite pengujian PHPUnit (`php artisan test`) melakukan pengujian sisi server (HTTP status, Blade view rendering, dan regex string HTML). PHPUnit tidak menjalankan mesin JavaScript klien (V8/SpiderMonkey), sehingga kesalahan sintaksis JS di dalam tag `<script>` tidak terdeteksi selama pengujian unit backend.

---

## 2. Arah Desain Frontend (`/skill:frontend-design-direction`)

### A. Fondasi Desain (Design Direction Foundations)
1. **Tujuan Antarmuka (Purpose)**:
   Memberikan kejelasan instan dan fleksibilitas bagi pelanggan untuk memeriksa, mengoreksi, atau menambahkan rincian penerima, alamat jalan, patokan, serta titik koordinat GPS presisi tanpa membebani ruang vertikal halaman secara default.
2. **Audiens (Audience)**:
   Pelanggan e-commerce yang sedang menyelesaikan transaksi checkout. Mereka membutuhkan kejelasan scanning data dengan hierarki bersih tanpa hambatan visual (*frictionless*).
3. **Tone & Karakter Visual**:
   *Utilitarian, clean, flat, and confident*. Mengikuti identitas Zyh Corner tanpa elemen dekoratif berlebih, tanpa penumpukan kartu di dalam kartu (*no cards inside cards*), dengan token warna hijau khas `#00b207`.
4. **Detail Berkesan (Memorable Detail)**:
   Transisi ketinggian *cubic-bezier* 350ms yang halus, rotasi chevron 180°, ringkasan status GPS berbentuk badge hijau muda (`#dcfce7`), serta pemulihan ubin peta Leaflet instan melalui pemanggilan `checkoutMap.invalidateSize()` tepat saat transisi selesai.

### B. Matriks Status Konten (Content States Matrix)

| Status | Pemicu (Trigger) | Umpan Balik Visual (Visible Feedback) | Tindakan Lanjutan |
| :--- | :--- | :--- | :--- |
| **Collapsed (Tutup)** | Nilai default saat pelanggan memiliki alamat tersimpan (`$hasSavedAddresses = true`) | Kontainer accordion tertutup (`max-height: 0`). Header menampilkan *summary pill* (Nama penerima &bull; Alamat jalan &bull; Badge GPS) serta tombol *"Detail Alamat"* dengan ikon chevron ke bawah. | Klik header atau tekan `Enter`/`Space` untuk membuka rincian. |
| **Expanding (Proses Buka)** | Klik tombol/header atau pemilihan opsi `+ Tulis Alamat Baru` pada dropdown | Transisi ekspansi ketinggian `0px → 2500px` dengan kurva `cubic-bezier(0.4, 0, 0.2, 1)`. Ikon chevron berputar mulus 180°. Border accordion berubah aksen hijau `#00b207`. | Peta Leaflet secara otomatis melakukan rekalkulasi ukuran (`invalidateSize()`) pada milidetik ke-350. |
| **Expanded (Terbuka Penuh)** | Transisi selesai / Default bagi pelanggan baru tanpa alamat tersimpan | Seluruh baris input (Penerima, HP, Wilayah Autocomplete Biteship, Kode Pos, Detail Alamat, Input Pencarian Lokasi Peta, dan Kanvas Peta Leaflet) terbuka penuh dan siap diedit. Label tombol menjadi *"Tutup Detail"*. | Pelanggan dapat mengubah data atau menggeser pin lokasi pada peta. |
| **A11y / Keyboard Focus** | Pelanggan menavigasi menggunakan tombol `Tab` keyboard | Header `#addressAccordionHeader` memiliki `role="button"`, `tabindex="0"`, `aria-expanded="true/false"`, dan menampilkan ring fokus kontras lembut `outline: 2px solid #00b207; outline-offset: -2px;`. | Menekan `Enter` atau `Space` membuka/menutup accordion tanpa sentuhan mouse. |

---

## 3. Rencana Perbaikan Bertahap (Implementation Plan)

### Fase 1: Perbaikan Sintaksis & Keandalan JavaScript
1. Memperbaiki penutupan objek pada `body: JSON.stringify({...})` di fungsi pengiriman pesanan `checkout.blade.php`.
2. Memastikan tidak ada sisa sintaksis yang merusak parsing JavaScript halaman.

### Fase 2: Peningkatan Aksesibilitas WAI-ARIA & Keyboard Handler
1. Menambahkan atribut `role="button"`, `tabindex="0"`, `aria-controls="addressAccordionBody"`, dan `aria-expanded` pada `#addressAccordionHeader`.
2. Menyematkan event listener `keydown` untuk tombol `Enter` dan `Space`:
   ```javascript
   onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleAddressAccordion(); }"
   ```
3. Memperbarui nilai `aria-expanded` secara dinamis di dalam fungsi `window.toggleAddressAccordion(forceOpen)`.
4. Menambahkan `:focus-visible` styling pada `.checkout-faq-q`.

### Fase 3: Penyelarasan Transisi CSS & Penanganan Kanvas Leaflet
1. Memastikan style `.checkout-faq-a` memiliki kurva transisi yang stabil:
   ```css
   .checkout-faq-a {
       max-height: 0;
       overflow: hidden;
       transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
   }
   .checkout-address-accordion.open .checkout-faq-a {
       max-height: 2500px;
   }
   ```
2. Mempertahankan pemanggilan `checkoutMap.invalidateSize()` dengan penundaan 350ms agar kanvas peta yang tersembunyi tidak mengalami distorsi ubin (*gray tiles issue*) saat accordion dibuka.

### Fase 4: Pengujian & Verifikasi Menyeluruh
1. **Verifikasi Browser Nyata (Headless Chromium)**:
   - Memastikan tidak ada lagi `SyntaxError` di konsol browser (`0 errors`).
   - Menguji navigasi keyboard (`Tab` + `Enter`) pada `#addressAccordionHeader` dan memastikan accordion membuka dan menutup (`aria-expanded="true/false"`).
   - Menguji klik mouse pada `#addressAccordionHeader` dan memastikan class beralih menjadi `open`, `body.clientHeight: 668px`, serta label beralih menjadi *"Tutup Detail"*.
2. **Verifikasi PHPUnit Test Suite**:
   - Menjalankan 195 feature & unit tests untuk memastikan *zero backend regression*.

---

## 4. Status Eksekusi & Hasil Verifikasi

1. **Sintaksis JavaScript**:
   - Telah diperbaiki kurung penutup objek `JSON.stringify({ ... })` pada baris 917 `checkout.blade.php`.
2. **Hasil Evaluasi Browser Nyata**:
   - Konsol error browser: `0 errors`.
   - Inisialisasi: `role="button"`, `tabindex="0"`, `ariaExpanded="false"`, `bodyHeight: 0px`, `label: "Detail Alamat"`.
   - Keyboard Navigation [Enter]: `ariaExpanded="true"`, `accClass="checkout-address-accordion open"`, `bodyHeight: 668px`, `label: "Tutup Detail"`.
   - Mouse Click [Close]: `ariaExpanded="false"`, `accClass="checkout-address-accordion"`, `bodyHeight: 0px`, `label: "Detail Alamat"`.
   - Mouse Click [Open]: `ariaExpanded="true"`, `accClass="checkout-address-accordion open"`, `bodyHeight: 668px`, `label: "Tutup Detail"`.
3. **Hasil Test Suite PHPUnit**:
   - `Tests: 195 passed (1041 assertions)` &mdash; 100% Green.
