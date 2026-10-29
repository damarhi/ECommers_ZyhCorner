# Dokumentasi Perbaikan Card Ringkasan & Tampilan Pesan Kontak Admin

## 1. Latar Belakang & Analisis Masalah

Pada halaman administrasi **Pesan Kontak Pelanggan** (`resources/views/Admin/pesan_kontak/index.blade.php`), tiga kartu metrik ringkasan (*KPI Summary Cards*) di bagian atas sebelumnya tampil sebagai kotak putih kosong polos tanpa memunculkan angka, teks, maupun ikon.

### Akar Masalah: Tabrakan CSS (*White-on-White Text Invisibility*)
1. **Aturan Global `.card` di `public/css/admin.css`**:
   Pada `public/css/admin.css` baris 421–427, terdapat aturan penataan kartu:
   ```css
   .card {
       border: 1px solid #E5E7EB !important;
       border-radius: 10px !important;
       box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
       transition: box-shadow 0.2s ease;
       background: #FFFFFF !important; /* Memaksa latar belakang semua .card menjadi putih */
   }
   ```
2. **Penggunaan Kelas Legacy PurpleAdmin**:
   Tiga kartu metrik di `pesan_kontak/index.blade.php` ditulis menggunakan kelas lawas:
   - `class="card bg-gradient-info card-img-holder text-white ..."`
   - `class="card bg-gradient-warning card-img-holder text-white ..."`
   - `class="card bg-gradient-success card-img-holder text-white ..."`
3. **Konflik Urutan Deklarasi (*Cascading Specificity*)**:
   Aturan `.bg-gradient-info` dideklarasikan pada baris 374, sedangkan aturan `.card` dengan `background: #FFFFFF !important;` dideklarasikan pada baris 426. Berdasarkan aturan cascading CSS, aturan yang dideklarasikan belakangan memenangkan penataan latar belakang, sehingga warna gradient tertimpa warna putih murni.
4. **Teks Putih di Atas Latar Putih**:
   Keberadaan kelas `text-white` memaksa seluruh teks, angka metrik, dan ikon di dalam kartu menjadi warna putih (`#ffffff`). Akibatnya, data yang sebenarnya ada dan telah di-*render* oleh Blade menjadi tidak terlihat sama sekali oleh mata pengguna (*invisible*).

---

## 2. Rincian Solusi & Implementasi Desain

Mengacu pada prinsip **`frontend-design-direction`**, kartu metrik diselaraskan dengan standar dasbor admin Zyh Corner yang sudah terbukti stabil pada halaman Dasbor Utama, Seluruh Penjualan, Diskon, dan Produk Kadaluarsa.

### A. Penerapan Komponen `.card.stat-card`
Komponen `.card.stat-card` diatur pada `public/css/admin.css` (baris 444–492) dengan aturan latar belakang yang memiliki spesifisitas tinggi dan border radius konsisten:
- **Kartu 1: Total Pesan Masuk**
  - Kelas: `card stat-card stat-card-indigo border-0 text-white shadow-sm`
  - Latar Belakang: `#4F46E5` (Indigo / Ungu Elegan)
  - Ikon: `mdi-email-multiple-outline`
  - Label: `Total Pesan Masuk`
  - Keterangan: `Akumulasi seluruh pesan kontak`
- **Kartu 2: Belum Dibaca (Urgensi Perhatian)**
  - Kelas: `card stat-card stat-card-coral border-0 text-white shadow-sm`
  - Latar Belakang: `#EF4444` (Coral / Merah Perhatian)
  - Ikon: `mdi-email-alert-outline`
  - Label: `Belum Dibaca`
  - Keterangan: `Memerlukan respon admin`
- **Kartu 3: Sudah Dibalas**
  - Kelas: `card stat-card stat-card-teal border-0 text-white shadow-sm`
  - Latar Belakang: `#14B8A6` (Teal / Hijau Sukses)
  - Ikon: `mdi-email-check-outline`
  - Label: `Sudah Dibalas`
  - Keterangan: `Pesan yang telah ditindaklanjuti`

### B. Standardisasi Tab Navigasi Filter
- Pada controller `ContactMessageController.php`, ditambahkan kalkulasi metrik pesan yang sudah dibaca:
  ```php
  $readMessages = ContactMessage::where('status', 'Dibaca')->count();
  ```
- Pada `index.blade.php`, tab "Sudah Dibaca" kini menampilkan indikator jumlah `Sudah Dibaca ({{ $readMessages ?? 0 }})` sehingga seragam dengan seluruh tab filter lainnya (`Semua Pesan`, `Belum Dibaca`, dan `Sudah Dibalas`).

---

## 3. Berkas yang Diubah & Dibuat

| No | Berkas | Perubahan |
|----|--------|-----------|
| 1 | `app/Http/Controllers/Admin/ContactMessageController.php` | Menghitung `$readMessages` dan mem-passing variabel tersebut ke tampilan `Admin.pesan_kontak.index`. |
| 2 | `resources/views/Admin/pesan_kontak/index.blade.php` | Mengganti kelas kartu lama dengan `.card.stat-card`, memperbaiki layout flex ikon & teks, serta menyelaraskan badge jumlah pada tab *Sudah Dibaca*. |
| 3 | `tests/Feature/AdminContactMessageTest.php` | Berkas pengujian fitur otomatis baru untuk memvalidasi kartu ringkasan, filter status tab, pembaruan status pesan, dan penghapusan pesan massal. |

---

## 4. Komparasi Kode (Sebelum vs Sesudah)

### A. Controller (`ContactMessageController.php`)
```php
// SEBELUM
$totalMessages = ContactMessage::count();
$unreadMessages = ContactMessage::where('status', 'Belum Dibaca')->count();
$repliedMessages = ContactMessage::where('status', 'Dibalas')->count();

return view('Admin.pesan_kontak.index', compact(
    'messages',
    'totalMessages',
    'unreadMessages',
    'repliedMessages'
));

// SESUDAH
$totalMessages = ContactMessage::count();
$unreadMessages = ContactMessage::where('status', 'Belum Dibaca')->count();
$readMessages = ContactMessage::where('status', 'Dibaca')->count();
$repliedMessages = ContactMessage::where('status', 'Dibalas')->count();

return view('Admin.pesan_kontak.index', compact(
    'messages',
    'totalMessages',
    'unreadMessages',
    'readMessages',
    'repliedMessages'
));
```

### B. View Kartu Ringkasan (`index.blade.php`)
```html
<!-- SEBELUM: Menggunakan bg-gradient-* dan card-img-holder yang tertimpa CSS .card -->
<div class="row mb-4">
    <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card bg-gradient-info card-img-holder text-white border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h4 class="font-weight-normal mb-2" style="font-size: 15px;">Total Pesan Masuk <i class="mdi mdi-email-outline mdi-24px float-end"></i></h4>
                <h2 class="mb-1 font-weight-bold">{{ number_format($totalMessages, 0, ',', '.') }}</h2>
                <small style="opacity: 0.85;">Akumulasi seluruh pesan kontak</small>
            </div>
        </div>
    </div>
    ...
</div>

<!-- SESUDAH: Menggunakan standar .card.stat-card dengan kontras warna tinggi -->
<div class="row mb-4">
    <!-- Total Pesan Masuk -->
    <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card stat-card stat-card-indigo border-0 text-white shadow-sm" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon">
                    <i class="mdi mdi-email-multiple-outline"></i>
                </div>
                <div>
                    <p class="stat-label mb-0">Total Pesan Masuk</p>
                    <h3 class="stat-value">{{ number_format($totalMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0">Akumulasi seluruh pesan kontak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Belum Dibaca (Urgensi Perhatian) -->
    <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card stat-card stat-card-coral border-0 text-white shadow-sm" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon">
                    <i class="mdi mdi-email-alert-outline"></i>
                </div>
                <div>
                    <p class="stat-label mb-0">Belum Dibaca</p>
                    <h3 class="stat-value">{{ number_format($unreadMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0">Memerlukan respon admin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sudah Dibalas -->
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card stat-card stat-card-teal border-0 text-white shadow-sm" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon">
                    <i class="mdi mdi-email-check-outline"></i>
                </div>
                <div>
                    <p class="stat-label mb-0">Sudah Dibalas</p>
                    <h3 class="stat-value">{{ number_format($repliedMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0">Pesan yang telah ditindaklanjuti</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 5. Verifikasi & Hasil Pengujian

1. **Blade Syntax & Template Cache**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil*: `Compiled views cleared successfully. Blade templates cached successfully.` (0 error).

2. **Feature Test Khusus Pesan Kontak (`AdminContactMessageTest`)**:
   ```bash
   php artisan test --filter=AdminContactMessageTest
   ```
   *Hasil*: **5 passed (25 assertions)** lulus 100%:
   - `admin can access contact messages page with stat cards`
   - `admin can filter contact messages by status`
   - `admin can update message status`
   - `admin can delete message`
   - `admin can bulk delete messages`

3. **Seluruh Test Suite Sistem**:
   ```bash
   php artisan test
   ```
   *Hasil*: **165 passed (839 assertions)** lulus 100% tanpa ada regresi.
