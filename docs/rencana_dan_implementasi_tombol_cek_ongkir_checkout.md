# Rencana & Dokumentasi Teknis: Implementasi Tombol Cek Ongkir & Sinkronisasi Tarif Kurir Checkout

Dokumen ini disusun berdasarkan panduan `/skill:frontend-design-direction` dan standar Zyh Corner untuk merancang dan mendokumentasikan implementasi tombol interaktif **"Cek Ongkir"** pada kartu Metode Pengiriman di halaman Checkout (`/checkout` &mdash; `resources/views/Pelanggan/checkout.blade.php`). Fitur ini memungkinkan pelanggan merefresh tarif ongkir kurir secara *real-time* atau otomatis tersinkronisasi saat wilayah tujuan dipilih.

---

## 1. Fondasi Desain Frontend (`/skill:frontend-design-direction`)

### A. Kontrak Desain (Design Contract)
1. **Tujuan Antarmuka (Purpose)**:
   Memberikan transparansi penuh dan kendali kepada pelanggan untuk merefresh biaya pengiriman (*shipping cost*) sesuai alamat tujuan yang dipilih tanpa harus memuat ulang (*reload*) halaman.
2. **Audiens (Audience)**:
   Pelanggan toko kue Zyh Corner yang ingin memastikan kepastian tarif kurir motor lokal (Gojek, Grab, Maxim) atau opsi Pick Up sebelum menekan tombol *"Buat Pesanan"*.
3. **Tone Visual**:
   *Utilitarian, clean, flat, and confident*. Menggunakan token warna khas Zyh Corner (`#00b207`), kontras tajam, serta tanpa penumpukan kartu di dalam kartu (*no cards inside cards*).
4. **Detail Berkesan (Memorable Detail)**:
   - Tombol mini bergaris tegas (*outline button*) `#00b207` di sisi kanan header Step 2 dengan ikon berputar halus saat proses sinkronisasi berlangsung.
   - Tag harga dinamis (`.shipping-cost-tag`) di setiap kartu kurir yang otomatis berubah warna hijau aktif saat kartu tersebut dipilih.
   - Pemicu otomatis: saat pembeli memilih alamat tersimpan atau memilih kelurahan dari dropdown autocomplete, tarif otomatis diperbarui secara senyap (*silent background refresh*).

### B. Matriks Status Konten (Content States Matrix)

| Status | Pemicu (Trigger) | Umpan Balik Visual (Visible Feedback) | Tindakan Lanjutan |
| :--- | :--- | :--- | :--- |
| **Idle / Normal** | Nilai awal saat halaman checkout dimuat | Tombol bertuliskan `<i class="fa-solid fa-arrows-rotate"></i> Cek Ongkir`. Kartu kurir menampilkan tag harga default. | Klik tombol untuk mengecek tarif terbaru. |
| **Loading / Syncing** | Tombol diklik atau alamat baru dipilih | Tombol dinonaktifkan (`disabled: true`), ikon berubah menjadi spinner berputar `<i class="fa-solid fa-spinner fa-spin"></i>`, teks menjadi *"Memuat..."*. | Menunggu respons endpoint `/biteship/rates` (asinkron). |
| **Success / Updated** | Data tarif diterima dari server | Tombol kembali normal, tag harga tiap kurir diperbarui, opsi aktif mengkalkulasi ulang total tagihan ringkasan pesanan, dan muncul toast notifikasi hijau: *"Tarif ongkir berhasil diperbarui"*. | Pelanggan dapat memilih opsi kurir yang diinginkan. |
| **Empty / Unselected Address** | Pelanggan menekan tombol sebelum mengisi wilayah/alamat pada Langkah 1 | Muncul dialog informasi ramah (*SweetAlert*): *"Silakan isi kelurahan atau kecamatan tujuan pada Langkah 1 terlebih dahulu untuk mengecek tarif ongkir."* | Pelanggan diarahkan melengkapi Langkah 1. |
| **Error / Network Fail** | Terjadi gangguan koneksi internet ke API | Tombol kembali aktif normal dan sistem menggunakan tarif flat aman lokal (*graceful fallback*) tanpa membuat halaman macet. | Pelanggan tetap dapat melanjutkan checkout dengan tarif standar. |

---

## 2. Perubahan Kode & Arsitektur

### A. Backend Layer
1. **Penambahan Endpoint Rate API (`routes/web.php` & `bootstrap/app.php`)**:
   - Mendaftarkan route: `POST /biteship/rates` (`CartCheckoutController@biteshipRates`).
   - Menambahkan pengecualian CSRF pada `bootstrap/app.php` untuk `/biteship/rates` agar pemanggilan AJAX utility checkout selalu stabil.
2. **Controller Logic (`app/Http/Controllers/Pelanggan/CartCheckoutController.php`)**:
   - Menerima parameter `destination_area_id`, `destination_area_name`, dan item keranjang belanja.
   - Memanggil `BiteshipService::getRates($destinationAreaId, $items)`.
   - Mengonversi hasil perhitungan ke format pemetaan kurir checkout melalui `BiteshipService::formatCheckoutRates()`.
3. **Service Layer (`app/Services/BiteshipService.php`)**:
   - Menambahkan metode `formatCheckoutRates(array $ratesResult): array` untuk memetakan tarif aktual Biteship (Gojek, Grab, Maxim, Pick Up Rp 0) atau fallback flat rate jika mode offline.

### B. Frontend Layer (`resources/views/Pelanggan/checkout.blade.php`)
1. **Header Step 2 (Metode Pengiriman)**:
   Menyematkan tombol aksi cepat:
   ```html
   <button type="button" class="btn-refresh-shipping" id="btnRefreshShipping" onclick="refreshShippingRates()" title="Cek & perbarui tarif ongkir berdasarkan wilayah tujuan">
       <i class="fa-solid fa-arrows-rotate" id="refreshShippingIcon"></i>
       <span id="refreshShippingText">Cek Ongkir</span>
   </button>
   ```
2. **Pill Harga Dinamis pada Kartu Kurir**:
   - Gojek: `<span class="shipping-cost-tag" id="costGojek">Rp. 20.000</span>`
   - Grab: `<span class="shipping-cost-tag" id="costGrab">Rp. 20.000</span>`
   - Maxim: `<span class="shipping-cost-tag" id="costMaxim">Rp. 20.000</span>`
   - Pick Up: `<span class="shipping-badge free">Gratis</span>`
3. **Fungsi JavaScript `window.refreshShippingRates(silent)`**:
   - Mengambil data ID wilayah dan item keranjang belanja.
   - Melakukan panggilan `fetch()` ke endpoint `/biteship/rates`.
   - Memperbarui atribut `data-cost` pada kartu kurir dan teks tag harga.
   - Menghitung ulang nilai `currentShippingCost` dan memicu `window.updateOrderTotalsWithShipping()`.
4. **Auto-Trigger On Selection**:
   - Dipicu otomatis di `window.selectArea()` saat pelanggan memilih kelurahan dari hasil pencarian autocomplete.
   - Dipicu otomatis di `window.selectSavedAddress()` saat pelanggan memilih alamat dari buku alamat.

### C. Styling CSS (`resources/css/pelanggan_custom.css`)
- `.btn-refresh-shipping`: Tombol ramping berlatar putih dengan border `#00b207`, hover solid hijau, dan ring fokus aksesibilitas `:focus-visible`.
- `.shipping-cost-tag`: Badge harga abu-abu lembut `#f1f5f9` yang beralih menjadi hijau muda `#dcfce7` dengan teks `#00b207` saat kartu kurir aktif dipilih.

---

## 3. Bukti Verifikasi Pengujian

1. **Browser Headless Interactivity (Chromium)**:
   - Status awal: Tombol `Cek Ongkir` muncul dengan teks dan ikon yang presisi.
   - Saat diklik: Tombol memasuki status `disabled: true` dan menampilkan spinner `Memuat...`.
   - Selesai pemanggilan: Tombol kembali normal (`disabled: false`), nilai tag harga dan ringkasan total pesanan terbarui otomatis.
2. **Suite Pengujian Otomatis (PHPUnit)**:
   - `test_biteship_rates_route_returns_json_and_couriers_mapping`: Lulus.
   - `test_format_checkout_rates_maps_api_pricing_accurately`: Lulus.
   - Total pengujian proyek: **197 passed (1056 assertions) &mdash; 100% Green**.
