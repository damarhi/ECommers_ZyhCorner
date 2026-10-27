# Pembersihan Hardcode Billing Address & Integrasi Alamat Dinamis

## Ringkasan Eksekutif
Sebelum pembaruan ini, seluruh tampilan *Billing Address* (alamat tagihan/pengiriman) pada antarmuka pelanggan menggunakan teks statis *hardcoded fallback* `"pekanbaru, Rumbai, Umban Sari"`. Teks statis ini muncul ketika akun pelanggan baru memiliki kolom `users.alamat` bernilai `NULL`. Selain itu, pada halaman Detail Pesanan (`order_detail.blade.php`), alamat ditulis statis tanpa membaca riwayat alamat pengiriman riil dari transaksi (`pesanans.alamat_pengiriman`).

Pembaruan ini membersihkan seluruh string statis tersebut di seluruh lapisan (Blade Views, JavaScript config, dan form checkout), menggantinya dengan logika dinamis berbasis database, serta menambahkan fitur *auto-fill* alamat akun ke form checkout.

---

## 1. Berkas yang Diubah

| No | Berkas | Perubahan Utama |
|:---:|---|---|
| 1 | `resources/views/Pelanggan/dashboard.blade.php` | Menghapus fallback statis; menampilkan pesan ramah `"Belum ada alamat tagihan (Klik Edit Address)"` jika `null`; menyertakan `userAlamat` & `userAddress` di `window.dashboardConfig`; mengubah placeholder modal menjadi contoh netral. |
| 2 | `resources/views/Pelanggan/order_detail.blade.php` | Menghubungkan alamat pengiriman ke data riil transaksi: `{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}`; menghapus penimpaan string statis oleh JavaScript. |
| 3 | `resources/views/Pelanggan/settings.blade.php` | Mengubah placeholder input alamat menjadi contoh alamat netral: `"Contoh: Jl. Sudirman No. 12, RT 01/RW 02"`. |
| 4 | `resources/views/Pelanggan/checkout.blade.php` | Menambahkan atribut `value="{{ Auth::user()->alamat ?? '' }}"` pada input `#streetAddress` agar alamat tersimpan otomatis terisi (*pre-filled*). |
| 5 | `resources/js/dashboard.js` | Menghapus fallback `"pekanbaru, Rumbai, Umban Sari"` dari sinkronisasi localStorage dan config. |
| 6 | `resources/js/pelanggan_pages.js` | Menghapus fallback `"pekanbaru, Rumbai, Umban Sari"`; memperbarui sinkronisasi dashboard & order detail agar menggunakan data dinamis dan styling teks abu-abu miring jika alamat belum diatur. |

---

## 2. Rincian Perubahan Kode

### A. `resources/views/Pelanggan/dashboard.blade.php`

#### 1. Kartu Billing Address (Baris 92–105)
**Sebelum:**
```blade
<div class="billing-title">Billing Address</div>
<div class="billing-name" id="displayBillingName">{{ $user->nama }}</div>
<div class="billing-address" id="displayBillingAddress">{{ $user->alamat ?? 'pekanbaru, Rumbai, Umban Sari' }}</div>
<div class="billing-contact">
    <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
</div>
<div class="billing-contact">
    <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
</div>
<a onclick="openEditAddress()" class="green-link" style="margin-top: 10px;">Edit Address</a>
```

**Sesudah:**
```blade
<div class="billing-title">Billing Address</div>
<div class="billing-name" id="displayBillingName">{{ $user->nama }}</div>
@if(!empty($user->alamat))
    <div class="billing-address" id="displayBillingAddress">{{ $user->alamat }}</div>
@else
    <div class="billing-address" id="displayBillingAddress" style="color: #888; font-style: italic;">Belum ada alamat tagihan (Klik Edit Address)</div>
@endif
<div class="billing-contact">
    <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
</div>
<div class="billing-contact">
    <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
</div>
<a onclick="openEditAddress()" class="green-link" style="margin-top: 10px;">Edit Address</a>
```

#### 2. Modal Edit Address & Objek Konfigurasi (Baris 248–272)
**Sebelum:**
```blade
<input type="text" id="inputBillingAddress" class="form-control-modal" placeholder="e.g. pekanbaru, Rumbai, Umban Sari" required>

...

window.dashboardConfig = {
    userNama: @json($user->nama),
    userEmail: @json($user->email),
    userPhone: @json($user->no_hp)
};
```

**Sesudah:**
```blade
<input type="text" id="inputBillingAddress" class="form-control-modal" placeholder="Contoh: Jl. Sudirman No. 12, RT 01/RW 02" value="{{ $user->alamat ?? '' }}" required>

...

window.dashboardConfig = {
    userNama: @json($user->nama),
    userEmail: @json($user->email),
    userPhone: @json($user->no_hp),
    userAlamat: @json($user->alamat ?? ''),
    userAddress: @json($user->alamat ?? ''),
    csrfToken: '{{ csrf_token() }}'
};
```

---

### B. `resources/views/Pelanggan/order_detail.blade.php`

#### 1. Kartu Ringkasan Alamat (Baris 785–794)
**Sebelum:**
```blade
<div class="summary-card-title">Address</div>
<div class="summary-card-name" id="displayBillingName">{{ $user->nama }}</div>
<div class="summary-card-address" id="displayBillingAddress">pekanbaru, Rumbai, Umban Sari</div>
<div class="summary-card-contact">
    <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
</div>
<div class="summary-card-contact">
    <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
</div>
```

**Sesudah:**
```blade
<div class="summary-card-title">Alamat Pengiriman</div>
<div class="summary-card-name" id="displayBillingName">{{ $order->customer_name ?? $user->nama }}</div>
<div class="summary-card-address" id="displayBillingAddress">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
<div class="summary-card-contact">
    <span>Email:</span> <span id="displayBillingEmail">{{ $order->email_pembeli ?? $user->email }}</span>
</div>
<div class="summary-card-contact">
    <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
</div>
```

#### 2. Script Sinkronisasi JS (Baris 987–995)
Menghapus penimpaan `#displayBillingAddress` dengan string statis localStorage agar alamat riil transaksi tidak tertimpa.

---

### C. `resources/views/Pelanggan/checkout.blade.php` (Baris 37–39)

**Sebelum:**
```blade
<label for="streetAddress">Street Address</label>
<input type="text" id="streetAddress" class="form-input-custom" placeholder="Detail alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan" required>
```

**Sesudah:**
```blade
<label for="streetAddress">Street Address</label>
<input type="text" id="streetAddress" class="form-input-custom" value="{{ Auth::user()->alamat ?? '' }}" placeholder="Detail alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan" required>
```

---

### D. `resources/js/dashboard.js` & `resources/js/pelanggan_pages.js`

- Menghapus fallback `"pekanbaru, Rumbai, Umban Sari"`.
- Jika alamat pengguna di database kosong (`rawAddress === ""`), teks antarmuka menampilkan `"Belum ada alamat tagihan (Klik Edit Address)"` dengan gaya teks abu-abu miring (`#888`, `italic`).
- Ketika pengguna menyimpan alamat baru melalui modal atau form pengaturan, antarmuka otomatis kembali ke warna normal (`#666`, `normal`) dan menyimpan nilai baru ke database `users.alamat`.

---

## 3. Hasil Pengujian Verifikasi (*Headless Browser Proof*)

Uji coba dilakukan menggunakan browser headless terotomasi terhadap endpoint aplikasi langsung (`http://clone_pa_zyhcorner.test`):

1. **Kondisi Awal (Akun Pengguna dengan Alamat NULL)**:
   - Teks `#displayBillingAddress` di Dashboard: `"Belum ada alamat tagihan (Klik Edit Address)"`
   - Warna teks: `rgb(136, 136, 136)` (#888)
   - Font style: `italic`
   - String statis `"pekanbaru, Rumbai, Umban Sari"`: **Tidak ditemukan (0 occurrence)**.

2. **Pembaruan Alamat via API / Modal (`POST /settings/address`)**:
   - Request: `{"alamat": "Jl. Riau No. 123, Sukajadi, Pekanbaru"}`
   - Response: `{"success": true, "message": "Alamat tagihan berhasil diperbarui!"}`
   - Verifikasi SQLite DB: Kolom `users.alamat` terupdate menjadi `"Jl. Riau No. 123, Sukajadi, Pekanbaru"`.

3. **Verifikasi Dashboard Pasca Update**:
   - Teks `#displayBillingAddress`: `"Jl. Riau No. 123, Sukajadi, Pekanbaru"`
   - Font style: `normal`

4. **Verifikasi Pre-fill Halaman Checkout (`/checkout`)**:
   - Input `#streetAddress` otomatis terisi dengan: `"Jl. Riau No. 123, Sukajadi, Pekanbaru"`.

5. **Verifikasi Halaman Detail Pesanan (`/order-history/{id}`)**:
   - Judul kartu: `"ALAMAT PENGIRIMAN"`
   - Nilai alamat: Menampilkan alamat pengiriman riil transaksi (`$order->alamat_pengiriman`).
