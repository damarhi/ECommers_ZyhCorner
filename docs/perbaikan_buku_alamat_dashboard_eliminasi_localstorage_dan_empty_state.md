# Dokumentasi Perbaikan Buku Alamat Dashboard: Eliminasi LocalStorage & Implementasi Empty State

## 1. Ringkasan Permasalahan & Latar Belakang

Pada modul Dasbor Pelanggan (`resources/views/Pelanggan/dashboard.blade.php`), ditemukan inkonsistensi data alamat yang dialami oleh akun pelanggan baru (seperti akun `baldiagus23ti@mahasiswa.pcr.ac.id` / User ID 18):
1. **Inkonsistensi Tampilan Kartu vs Modal Edit**:
   - Pada kartu "Buku Alamat Pengiriman" di dashboard, data yang tampil adalah alamat milik akun demo lama (`Jl. Riau No. 123, Sukajadi, Pekanbaru`, nomor kontak `(+62) 8123123123`, dan email `nama@gmail.com`).
   - Namun ketika tombol "Edit Alamat Utama" diklik, form modal justru menampilkan data akun yang benar (`baldiagus`, nomor telepon `81234567877`, sedangkan input alamat jalan dan area Biteship kosong).
2. **Kontaminasi Data Lintas Akun via Browser `localStorage`**:
   - Pada `resources/js/dashboard.js`, terdapat skrip `DOMContentLoaded` lama yang membaca key global `zyh_cust_name`, `zyh_cust_address`, `zyh_cust_email`, dan `zyh_cust_phone` dari `localStorage` browser.
   - Data sisa pengujian dari akun sebelumnya secara sepihak menimpa teks elemen DOM server (`#displayBillingAddress`, `#displayBillingEmail`, `#displayBillingPhone`).
   - Selain itu, saat tombol logout ditekan, `localStorage` tidak pernah dibersihkan.
3. **Absennya Empty State pada Akun Baru**:
   - Akun baru yang belum memiliki alamat tetap menampilkan badge "Alamat Utama" dan "Default" dengan placeholder miring *"Belum ada alamat tagihan (Klik Edit Address)"*, bukan antarmuka *Empty State* yang ramah, informatif, dan mengarahkan pengguna untuk menambah alamat.

---

## 2. Investigasi & Akar Masalah (Root Cause Analysis)

### Akar Masalah 1: Penimpaan Sepihak DOM oleh `localStorage` di JavaScript
- **File**: `resources/js/dashboard.js` (baris 1–37) dan `resources/js/pelanggan_pages.js` (baris 1430–1442).
- **Mekanisme**:
  Saat halaman dasbor dimuat, browser menjalankan:
  ```javascript
  const storedAddress = localStorage.getItem('zyh_cust_address') || window.dashboardConfig.userAlamat || "";
  const storedEmail = localStorage.getItem('zyh_cust_email') || window.dashboardConfig.userEmail;
  const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) " + window.dashboardConfig.userPhone;

  if (storedAddress) document.getElementById('displayBillingAddress').textContent = storedAddress;
  ```
  Karena key `zyh_cust_address` tidak memiliki namespace akun (`user_id`), data alamat akun yang pernah login di komputer/browser yang sama akan terbaca dan menimpa tampilan DOM akun baru yang alamat aslinya masih `null` di database.

### Akar Masalah 2: Sifat Redundan Penyimpanan `localStorage` saat Mutasi
- **File**: `resources/js/dashboard.js` (fungsi `saveAddress` dan `saveProfile`).
- **Mekanisme**:
  Saat pengguna mengedit atau menyimpan alamat/profil, skrip memanggil `localStorage.setItem('zyh_cust_address', ...)` sebelum mengirim request HTTP ke API server. Karena aplikasi Laravel telah memiliki persistensi basis data penuh (tabel `users` dan `user_addresses`), penggunaan `localStorage` untuk data identitas pengguna adalah anti-pattern yang menyebabkan desinkronisasi (*cache invalidation issue*).

### Akar Masalah 3: Struktur Blade Dashboard Belum Memiliki Logika State Visual
- **File**: `resources/views/Pelanggan/dashboard.blade.php`.
- **Mekanisme**:
  Struktur HTML kartu alamat langsung merender kartu alamat default tanpa mengecek apakah pelanggan sudah memiliki alamat tersimpan (`$defaultAddrObj` atau `$user->alamat`). Akibatnya, pelanggan baru disajikan kartu setengah jadi dengan label "Default" palsu.

---

## 3. Rincian Perubahan Kode (Code Changes)

### A. Modifikasi `resources/js/dashboard.js`
1. **Menghapus Override DOM dari `localStorage` pada Inisialisasi**:
   Menghapus baris kode yang membaca `localStorage` dan menimpa elemen DOM. Menggantinya dengan pembersihan proaktif terhadap sisa key usang `zyh_cust_*`.
   ```javascript
   // Dashboard initialization & state management
   document.addEventListener('DOMContentLoaded', function() {
       // Ensure any legacy stale cross-account localStorage is cleared
       try {
           localStorage.removeItem('zyh_cust_name');
           localStorage.removeItem('zyh_cust_address');
           localStorage.removeItem('zyh_cust_email');
           localStorage.removeItem('zyh_cust_phone');
       } catch (e) {
           // Ignore localStorage access errors
       }
   });
   ```

2. **Refaktor `saveProfile` Menjadi Sinkron ke Backend**:
   Mengirim pembaruan nama pengguna langsung ke endpoint `/settings/account` via HTTP POST, bukan hanya menyimpan ke `localStorage`:
   ```javascript
   window.saveProfile = function(e) {
       e.preventDefault();
       const newName = document.getElementById('inputProfileName').value.trim();
       const config = window.dashboardConfig || {};
       const csrfToken = config.csrfToken || '';

       if (!newName) return;

       Swal.fire({
           title: 'Menyimpan Profil...',
           allowOutsideClick: false,
           didOpen: () => Swal.showLoading()
       });

       fetch('/settings/account', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': csrfToken,
               'Accept': 'application/json'
           },
           body: JSON.stringify({
               nama: newName,
               email: config.userEmail,
               no_hp: config.userPhone
           })
       })
       .then(res => res.json())
       .then(data => {
           Swal.close();
           if (data.success) {
               document.getElementById('displayProfileName').textContent = newName;
               const dbn = document.getElementById('displayBillingName');
               if (dbn) dbn.textContent = newName;
               window.closeEditProfile();
               Swal.fire({
                   title: 'Berhasil!',
                   text: 'Profil berhasil diperbarui.',
                   icon: 'success',
                   confirmButtonColor: '#00b207',
                   customClass: { popup: 'rounded-4' }
               }).then(() => {
                   window.location.reload();
               });
           } else {
               Swal.fire({
                   title: 'Gagal!',
                   text: data.message || 'Gagal memperbarui profil.',
                   icon: 'error',
                   confirmButtonColor: '#00b207'
               });
           }
       })
       .catch(err => {
           Swal.close();
           console.error(err);
           Swal.fire({
               title: 'Kesalahan Sistem',
               text: 'Gagal menyimpan perubahan profil.',
               icon: 'error',
               confirmButtonColor: '#00b207'
           });
       });
   };
   ```

3. **Menghapus `localStorage.setItem` pada `saveAddress`**:
   Mengeliminasi baris `localStorage.setItem('zyh_cust_address', streetAddress)` dan `localStorage.setItem('zyh_cust_phone', recipientPhone)`.

---

### B. Modifikasi `resources/views/Pelanggan/dashboard.blade.php`
1. **Implementasi Dua State Visual (Empty State vs Populated State)**:
   Menerapkan panduan `/skill:frontend-design-direction` dengan desain flat, palet token standar Zyh Corner (`#00b207`, `#0f172a`, `#64748b`, `#f8fafc`, `#cbd5e1`), dan menghindari nesting berlebihan:
   ```blade
   <!-- Billing Address & Address Book Card -->
   <div class="col-md-6 mb-4">
       <div class="dashboard-card billing-card">
           <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
               <div class="billing-title" style="margin-bottom: 0;">Buku Alamat Pengiriman</div>
               <button type="button" onclick="openAddAddressModal()" class="green-link" style="background: none; border: none; padding: 0; cursor: pointer; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                   <i class="fa-solid fa-plus"></i> Tambah Alamat
               </button>
           </div>

           @php
               $defaultAddrObj = (isset($addresses) && $addresses->count() > 0) ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
               $hasAddress = $defaultAddrObj || !empty($user->alamat);
               $extraCount = isset($addresses) ? $addresses->where('is_default', false)->count() : 0;
           @endphp

           @if ($hasAddress)
               <!-- Primary Default Address Display (Populated State) -->
               <div class="address-item-card is-default mb-2">
                   <div class="saved-address-header">
                       <span class="saved-address-label">{{ $defaultAddrObj->label ?? 'Alamat Utama' }}</span>
                       <span class="saved-address-badge-default">Default</span>
                   </div>
                   <div class="billing-name" id="displayBillingName">{{ $defaultAddrObj->recipient_name ?? $user->nama }}</div>
                   <div class="billing-address" id="displayBillingAddress">{{ $defaultAddrObj->street_address ?? $user->alamat }}</div>
                   @if($defaultAddrObj && !empty($defaultAddrObj->destination_area_name))
                       <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">
                           {{ $defaultAddrObj->destination_area_name }}{{ !empty($defaultAddrObj->postal_code) ? ', ' . $defaultAddrObj->postal_code : '' }}
                       </div>
                   @endif
                   <div class="billing-contact" style="margin-top: 6px;">
                       <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
                   </div>
                   <div class="billing-contact">
                       <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $defaultAddrObj->recipient_phone ?? $user->no_hp }}</span>
                   </div>
                   <div class="address-item-actions">
                       <a onclick="openEditAddress({{ $defaultAddrObj ? json_encode($defaultAddrObj) : 'null' }})" class="green-link" style="cursor: pointer;"><i class="fa-solid fa-pen-to-square"></i> Edit Alamat Utama</a>
                       @if($extraCount > 0)
                           <button type="button" class="btn-other-addresses" onclick="openSavedAddressesModal()">
                               <i class="fa-solid fa-layer-group"></i> Lihat Alamat Lainnya ({{ $extraCount }})
                           </button>
                       @endif
                   </div>
               </div>
           @else
               <!-- Empty State: Belum Ada Alamat Pengiriman -->
               <div class="address-empty-card" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 22px 16px; text-align: center;">
                   <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                       <i class="fa-solid fa-map-location-dot" style="font-size: 20px; color: #94a3b8;"></i>
                   </div>
                   <div style="font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">Belum Ada Alamat Pengiriman</div>
                   <div style="font-size: 12.5px; color: #64748b; line-height: 1.5; max-width: 320px; margin: 0 auto 14px auto;">
                       Tambahkan alamat untuk mempermudah perhitungan ongkir otomatis dan pengiriman pesanan Anda.
                   </div>
                   <button type="button" onclick="openAddAddressModal()" class="btn-add-primary" style="background: #00b207; color: #ffffff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;">
                       <i class="fa-solid fa-plus"></i> Tambah Alamat Sekarang
                   </button>
                   <!-- Compatibility fallback hooks -->
                   <div style="display: none;">
                       <span id="displayBillingName">{{ $user->nama }}</span>
                       <span id="displayBillingAddress"></span>
                       <span id="displayBillingEmail">{{ $user->email }}</span>
                       <span id="displayBillingPhone">{{ $user->no_hp }}</span>
                   </div>
               </div>
           @endif
       </div>
   </div>
   ```

2. **Sinkronisasi Konfigurasi JS Dashboard dengan Server**:
   ```blade
   <script>
       window.dashboardConfig = {
           userNama: @json($user->nama),
           userEmail: @json($user->email),
           userPhone: @json($user->no_hp),
           userAlamat: @json($defaultAddrObj->street_address ?? ($user->alamat ?? '')),
           userAddress: @json($defaultAddrObj->street_address ?? ($user->alamat ?? '')),
           csrfToken: '{{ csrf_token() }}'
       };
   ```

---

### C. Modifikasi `resources/js/pelanggan_pages.js`
1. **Pembersihan `localStorage` pada Aksi Logout**:
   Pada fungsi konfirmasi keluar `confirmLogout()`, ditambahkan perintah penghapusan key `zyh_cust_*`:
   ```javascript
   window.confirmLogout = function() {
       Swal.fire({
           title: 'Konfirmasi Keluar',
           text: 'Apakah Anda yakin ingin keluar dari akun Anda?',
           icon: 'question',
           showCancelButton: true,
           confirmButtonColor: '#00b207',
           cancelButtonColor: '#d33',
           confirmButtonText: 'Ya, Keluar',
           cancelButtonText: 'Batal',
           customClass: {
               popup: 'rounded-4'
           }
       }).then((result) => {
           if (result.isConfirmed) {
               try {
                   localStorage.removeItem('zyh_cust_name');
                   localStorage.removeItem('zyh_cust_address');
                   localStorage.removeItem('zyh_cust_email');
                   localStorage.removeItem('zyh_cust_phone');
               } catch (e) {}
               const logoutForm = document.getElementById('logoutForm');
               if (logoutForm) logoutForm.submit();
           }
       });
   };
   ```

2. **Menghapus Fallback `localStorage` pada Detail Pesanan**:
   Pada baris 1430–1450, pembacaan alamat detail pesanan disesuaikan agar 100% menggunakan `window.orderDetailConfig` dari database server tanpa membaca `localStorage`.

---

### D. Modifikasi `resources/views/Pelanggan/order_detail.blade.php`
- Menghapus operator fallback `|| localStorage.getItem('zyh_cust_...')` pada baris 3011–3013 sehingga data pemesan pada detail pesanan terisolasi penuh sesuai entitas database pesanan terkait.

---

### E. Penambahan Unit & Feature Testing di `tests/Feature/CustomerDashboardTest.php`
Dua unit pengujian regresi ditambahkan untuk memastikan State A dan State B selalu terlindungi dalam CI/CD:
1. `test_dashboard_renders_empty_state_when_customer_has_no_address`:
   Memverifikasi bahwa pengguna baru tanpa alamat akan melihat teks *"Belum Ada Alamat Pengiriman"* dan tombol *"Tambah Alamat Sekarang"*, serta tidak melihat tombol *"Edit Alamat Utama"*.
2. `test_dashboard_renders_populated_state_with_full_details_when_customer_has_address`:
   Memverifikasi bahwa pengguna dengan alamat akan melihat label alamat, badge default, nama penerima, alamat jalan, kecamatan/kota, kode pos, dan tombol *"Edit Alamat Utama"*.

---

## 4. Hasil Verifikasi & Validasi Pengujian

1. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build
   ```
   *Output*: Berhasil mengompilasi seluruh modul (58 modul) tanpa kesalahan sintaks.

2. **Pengujian Unit & Feature (PHPUnit)**:
   ```bash
   php artisan test tests/Feature/CustomerDashboardTest.php
   ```
   *Output*:
   ```text
   PASS  Tests\Feature\CustomerDashboardTest
   ✓ dashboard resolves address fallback from customers table
   ✓ customer can update address
   ✓ customer can upload avatar
   ✓ dashboard renders saved addresses modal and hides extra addresses from main card
   ✓ dashboard renders empty state when customer has no address
   ✓ dashboard renders populated state with full details when customer has address

   Tests:    6 passed (33 assertions)
   ```

3. **Pengujian Keseluruhan Proyek**:
   ```bash
   php artisan test
   ```
   *Output*:
   ```text
   Tests:    194 passed (1034 assertions)
   Duration: 16.29s (100% Green)
   ```

4. **Verifikasi Simulasi Akun Pelanggan Baru vs Lama**:
   - Akun User ID 18 (`baldiagus23ti@mahasiswa.pcr.ac.id`, alamat `null`):
     - `Belum Ada Alamat Pengiriman`: **YES**
     - `Tambah Alamat Sekarang`: **YES**
     - Kontaminasi `Jl. Riau No. 123`: **NO (Tereliminasi)**
   - Akun User ID 1 (`nama@gmail.com`, alamat terisi):
     - `Populated State (Edit Alamat Utama)`: **YES**
     - `Empty State`: **NO**

---

## 5. Kesimpulan
Sistem buku alamat pada dasbor pelanggan kini menggunakan database server sebagai *single source of truth*. Permasalahan kebocoran data alamat antar pengguna melalui browser `localStorage` telah tuntas diselesaikan, antarmuka akun baru tampil profesional dengan *Empty State* yang terstandarisasi, dan seluruh rangkaian pengujian sistem (194 tests) tetap lulus 100%.
