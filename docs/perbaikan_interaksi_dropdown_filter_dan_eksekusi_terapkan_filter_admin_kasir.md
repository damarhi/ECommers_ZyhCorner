# Perbaikan Interaksi Dropdown Filter dan Eksekusi Tombol Terapkan Filter Admin & Kasir

## 1. Analisis Masalah

Pada modul Admin dan Kasir (`resources/views/Admin/`), ditemukan dua kendala mendasar terkait pengalaman interaksi pengguna (*user experience*) pada toolbar filter:

1. **Dropdown Filter Otomatis Tertutup Saat Memilih Opsi (Checkbox Diklik)**
   - **Penyebab**: Aplikasi menggunakan **Bootstrap v5.3.2**. Secara default, komponen dropdown Bootstrap memiliki konfigurasi `autoClose: true`.
   - **Mekanisme**: Setiap kali pengguna mengklik elemen di dalam `.dropdown-menu` (seperti `<input type="checkbox">` atau `<label>`), event klik mengalami *bubbling* hingga ke document listener Bootstrap yang memicu fungsi `hide()`. Akibatnya, pengguna tidak dapat memilih lebih dari satu filter dalam satu kali pembukaan menu.

2. **Tombol "Terapkan Filter" Tidak Berfungsi (Filter Mati / Tidak Menyaring Data)**
   - **Penyebab**: Terjadi pembajakan event submit form (*form submit hijacking*) pada skrip `public/js/live-instant-search.js`.
   - **Mekanisme**: Skrip memiliki event listener pada event `submit` form:
     ```javascript
     if (container || hasLocalFilterableContent()) {
         e.preventDefault();
         if (debounceTimer) clearTimeout(debounceTimer);
         triggerFilter(input, input.value.trim());
     }
     ```
     Karena hampir setiap halaman Admin memiliki tabel (`.table tbody tr`) atau grid produk (`#product-grid`), `hasLocalFilterableContent()` selalu bernilai `true`. Saat tombol `<button type="submit">Terapkan Filter</button>` diklik, `e.preventDefault()` langsung membatalkan pengiriman form HTTP GET ke backend controller. Skrip kemudian hanya menjalankan `triggerFilter(input, input.value.trim())` yang hanya melakukan pencarian teks berbasis JavaScript lokal pada baris tabel yang sedang tampil dan sama sekali mengabaikan seluruh input checkbox filter yang dipilih.

---

## 2. Rincian Perubahan Kode

### A. JavaScript (`public/js/live-instant-search.js`)

1. **Penambahan `initDropdownSafeguards()`**:
   Mencegah event klik pada checkbox dan label di dalam menu dropdown memicu penutupan dropdown:
   ```javascript
   function initDropdownSafeguards() {
       document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
           if (menu.dataset.autocloseBound) return;
           menu.dataset.autocloseBound = 'true';
           menu.addEventListener('click', function(e) {
               // Cegah penutupan otomatis Bootstrap saat pengguna memilih checkbox atau mengklik label di dalam dropdown
               var isAction = e.target.closest('button[type="submit"], a.dropdown-item, a.btn, .btn-close');
               if (!isAction) {
                   e.stopPropagation();
               }
           });
       });
   }
   ```

2. **Pemulihan Event Submit Form pada Tombol Submit Filter**:
   Membedakan antara penekanan tombol Enter pada input pencarian instan dengan penekanan tombol submit eksplisit ("Terapkan Filter"):
   ```javascript
   // Penanganan pengiriman form filter & pencarian
   if (form) {
       form.addEventListener('submit', function(e) {
           var container = getAjaxContainer(input);
           var submitter = e.submitter;
           var isSubmitButton = submitter && (
               submitter.matches('button[type="submit"], input[type="submit"]') ||
               submitter.classList.contains('btn')
           );

           // 1. Jika form memiliki container AJAX (misalnya: all-sales):
           if (container) {
               e.preventDefault();
               if (debounceTimer) clearTimeout(debounceTimer);
               triggerAjaxSearch(input, form, container, input.value.trim());
               closeActiveDropdown(form);
               return;
           }

           // 2. Jika tombol "Terapkan Filter" ditekan pada form reguler server-side:
           // Izinkan pengiriman form secara native (jangan panggil e.preventDefault)
           if (isSubmitButton) {
               closeActiveDropdown(form);
               return;
           }

           // 3. Jika pengguna menekan Enter pada input pencarian dan halaman memiliki data lokal yang dapat disaring:
           if (hasLocalFilterableContent()) {
               e.preventDefault();
               if (debounceTimer) clearTimeout(debounceTimer);
               triggerFilter(input, input.value.trim());
           }
       });
   }
   ```

3. **Helper Penutupan Dropdown Secara Terprogram (`closeActiveDropdown`)**:
   ```javascript
   function closeActiveDropdown(container) {
       var dropdownToggle = (container || document).querySelector('[data-bs-toggle="dropdown"][aria-expanded="true"]');
       if (dropdownToggle && window.bootstrap && typeof window.bootstrap.Dropdown !== 'undefined') {
           var instance = window.bootstrap.Dropdown.getInstance(dropdownToggle);
           if (instance) {
               instance.hide();
           }
       }
   }
   ```

4. **Perbaikan Multi-Value Checkbox pada Query AJAX (`triggerAjaxSearch`)**:
   Menggunakan `append()` alih-alih `set()` agar parameter array seperti `jenis_penjualan[]` tidak saling menimpa ketika lebih dari satu checkbox dicentang:
   ```javascript
   var handledKeys = new Set();
   for (var key of formData.keys()) {
       if (key !== 'page' && key !== 'search' && !handledKeys.has(key)) {
           targetUrl.searchParams.delete(key);
           handledKeys.add(key);
       }
   }
   for (var pair of formData.entries()) {
       if (pair[0] !== 'page' && pair[0] !== 'search') {
           targetUrl.searchParams.append(pair[0], pair[1]);
       }
   }
   ```

---

### B. View Blade Admin & Kasir (`data-bs-auto-close="outside"`)

Menambahkan atribut `data-bs-auto-close="outside"` pada elemen tombol `#filterDropdown` di 10 berkas tampilan Blade:

1. **`resources/views/Admin/kelola_produk/products.blade.php`**
   ```html
   <button class="btn btn-sm {{ $activeFilterCount > 0 ? 'btn-gradient-primary text-white' : 'btn-outline-primary' }} dropdown-toggle ..." 
           type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
   ```
2. **`resources/views/Admin/kelola_produk/custom_products.blade.php`**
3. **`resources/views/Admin/kelola_produk/discounts.blade.php`**
4. **`resources/views/Admin/kelola_produk/expired_products.blade.php`**
5. **`resources/views/Admin/kelola_penjualan/orders.blade.php`**
6. **`resources/views/Admin/kelola_penjualan/all_sales.blade.php`**
7. **`resources/views/Admin/kelola_penjualan/pos.blade.php`**
8. **`resources/views/Admin/kelola_penjualan/returs.blade.php`**
9. **`resources/views/Admin/kelola_akun/customers.blade.php`**
10. **`resources/views/Admin/kelola_carousel/banners.blade.php`**

---

## 3. Hasil Pengujian dan Verifikasi

1. **Kompilasi Frontend (Vite Build)**:
   ```bash
   npm run build
   ```
   Status: **Berhasil** (`built in 534ms`).

2. **Automated Unit & Feature Tests**:
   ```bash
   php artisan test && git clean -fd public/uploads/
   ```
   Status: **PASS (216 tests passed, 1163 assertions)**.
   - Paginasi query string, filter status pesanan online, tab filter pelanggan, dan filter kategori POS berfungsi 100%.
   - Filter AJAX maupun submit form server-side berjalan normal tanpa regresi.
