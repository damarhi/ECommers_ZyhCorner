# Dokumentasi Implementasi Penyimpanan Lokasi Peta Leaflet & Koordinat GPS pada Checkout

## 1. Latar Belakang & Masalah
Sebelum perbaikan ini, pengguna dapat menggeser pin marker peta Leaflet, mencari lokasi melalui bar pencarian Nominatim, atau menggunakan tombol GPS *"Gunakan Lokasi Saya"* pada halaman Checkout (`checkout.blade.php`).

Namun, nilai koordinat tersebut **belum tersimpan** saat pesanan dibuat karena:
1. Fungsi pengiriman JavaScript `submitOrder()` pada `checkout.blade.php` dan `pelanggan_pages.js` belum menyertakan properti `latitude` dan `longitude` ke dalam payload AJAX JSON (`fetch`).
2. Backend `CartCheckoutController` yang bertugas memproses pesanan dan menyimpan alamat baru menerima nilai `latitude` dan `longitude` sebagai `null`.
3. Kasir, admin toko, dan kurir tidak memiliki tautan langsung Google Maps pada rincian pesanan untuk mengantarkan kue ke titik yang presisi.

---

## 2. Arah Desain & Kontrak Frontend (`/skill:frontend-design-direction`)
- **Flat & Clean**: Menjaga estetika flat Zyh Corner tanpa penumpukan kartu (*no cards inside cards*).
- **Visual Assurance**: Badge *"GPS Terpasang"* di kartu ringkasan alamat langsung aktif secara real-time saat koordinat dipilih.
- **Pembersihan State**: Jika pengguna memilih alamat tersimpan lain yang belum memiliki GPS atau memilih opsi *"Alamat Baru"*, input koordinat dan tampilan display langsung di-reset untuk menghindari tumpang tindih data koordinat lama.
- **Tautan Aksesibilitas Google Maps**: Titik koordinat yang dikirimkan secara otomatis diformat menjadi tautan Google Maps ramah klik pada catatan pesanan (`catatan_pesanan`) untuk memudahkan verifikasi kasir dan pengantaran kurir.

---

## 3. Rincian Perubahan Kode

### A. Frontend Checkout (`resources/views/Pelanggan/checkout.blade.php`)
1. **Reset Koordinat saat Pergantian Alamat**:
   - Di `selectSavedAddress()`: Jika alamat terpilih tidak memiliki GPS (`hasGps === false`), input `#latitude` dan `#longitude` di-kosongkan dan teks `#coordsDisplay` dikembalikan ke default.
   - Di `selectNewAddress()`: Mengosongkan nilai `#latitude` dan `#longitude`.
2. **Ekstraksi & Injeksi Koordinat pada `submitOrder()`**:
   ```javascript
   const latEl = document.getElementById('latitude');
   const lngEl = document.getElementById('longitude');
   let latitudeVal = latEl && latEl.value ? parseFloat(latEl.value) : null;
   let longitudeVal = lngEl && lngEl.value ? parseFloat(lngEl.value) : null;

   fetch(routeUrl, {
       method: 'POST',
       headers: {
           'Content-Type': 'application/json',
           'X-CSRF-TOKEN': token
       },
       body: JSON.stringify({
           items: localCart,
           address: streetAddress,
           notes: notes,
           payment_method: paymentVal,
           shipping_method: shippingVal,
           recipient_name: recipientName,
           recipient_phone: recipientPhone,
           destination_area_id: destinationAreaId,
           destination_area_name: document.getElementById('areaSearchInput')?.value || '',
           postal_code: postalCode,
           shipping_cost: shippingCost,
           save_address: document.getElementById('saveAddressCheckbox')?.checked || false,
           address_label: document.getElementById('newAddressLabel')?.value.trim() || '',
           latitude: latitudeVal,
           longitude: longitudeVal
       })
   })
   ```

---

### B. Skrip Halaman Pelanggan (`resources/js/pelanggan_pages.js`)
1. **Pembaruan `window.updateCoordsInputs(lat, lng)`**:
   Memastikan badge `#summaryGpsBadge` otomatis berstatus `inline-flex` saat koordinat diperbarui via interaksi peta, geocoding, atau GPS.
2. **Penyelarasan pada Fallback `window.submitOrder()`**:
   Menyertakan `latitude: latitudeVal` dan `longitude: longitudeVal` pada payload JSON agar konsisten jika dipanggil dari modul lain.

---

### C. Backend Controller (`app/Http/Controllers/Pelanggan/CartCheckoutController.php`)
1. **Penyimpanan ke Tabel `user_addresses`**:
   Data `$request->input('latitude')` dan `$request->input('longitude')` kini terisi penuh saat pelanggan mencentang *"Simpan alamat ini ke buku alamat saya"* atau saat membuat alamat perdana.
2. **Penyematan Link Google Maps pada Pesanan**:
   ```php
   if ($request->filled('latitude') && $request->filled('longitude')) {
       $lat = $request->input('latitude');
       $lng = $request->input('longitude');
       $mapLink = "https://maps.google.com/?q={$lat},{$lng}";
       $gpsNote = "[Titik GPS: {$lat}, {$lng} | {$mapLink}]";
       $finalNotes = $finalNotes ? ($finalNotes . ' ' . $gpsNote) : $gpsNote;
   }
   ```
   Data ini tersimpan ke kolom `catatan_pesanan` pada tabel `pesanans`, sehingga otomatis terbaca oleh:
   - Invoice Cetak Admin & Kasir (`invoice.blade.php`)
   - Modal Rincian Pesanan Kasir (`order_detail_modal.blade.php`)
   - Rincian Pesanan Pelanggan (`order_detail.blade.php`)

---

### D. Pengujian Otomatis (`tests/Feature/CustomerAddressManagementTest.php`)
Menambahkan unit test regresi:
`test_checkout_saves_new_address_with_coordinates_and_records_gps_in_order_notes()`
- Memverifikasi HTTP POST request checkout membawa `latitude` dan `longitude`.
- Memverifikasi tabel `user_addresses` menyimpan nilai desimal koordinat.
- Memverifikasi tabel `pesanans` pada kolom `catatan_pesanan` menyimpan koordinat serta tautan `maps.google.com`.

---

## 4. Hasil Verifikasi & Uji

1. **Vite Build**:
   ```bash
   npm run build
   ```
   *Hasil*: Berhasil (built in 528ms, `pelanggan_pages-BPYseIu6.js`).

2. **Pengujian Fitur Alamat & Desain Checkout**:
   ```bash
   php artisan test tests/Feature/CustomerAddressManagementTest.php tests/Feature/CheckoutDesignTest.php
   ```
   *Hasil*: **10 passed (98 assertions)** 100% Green.

3. **Pengujian Seluruh Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil*: **195 passed (1041 assertions)** 100% Green tanpa regresi.
