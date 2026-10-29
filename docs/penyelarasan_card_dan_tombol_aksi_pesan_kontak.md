# Dokumentasi Penyelarasan Ukuran Card & Penataan Tombol Aksi Pesan Kontak

## 1. Latar Belakang & Analisis Masalah

Berdasarkan tinjauan visual antarmuka **Pesan Kontak Pelanggan** (`resources/views/Admin/pesan_kontak/index.blade.php`), terdapat dua aspek tata letak yang memerlukan penyelarasan agar sesuai dengan standar *frontend design direction* Zyh Corner:

1. **Ketidakseragaman Dimensi & Grid Kartu Ringkasan (KPI Cards)**:
   - Kolom 1 dan 2 menggunakan kelas `col-md-4 stretch-card grid-margin mb-3 mb-md-0`, sedangkan Kolom 3 hanya menggunakan `col-md-4 stretch-card grid-margin` (tanpa `mb-3 mb-md-0`).
   - Pada resolusi desktop (`md` ke atas), Kolom 3 mewarisi margin bawah bawaan tema PurpleAdmin (`margin-bottom: 2.5rem`), sementara Kolom 1 dan 2 memiliki margin bawah `0`. Hal ini memicu ketidaksejajaran batas bawah antarkartu.
   - Tanpa deklarasi `h-100` dan batas tinggi minimum (`min-height`) eksplisit pada elemen kartu serta pemotongan teks rapi (`text-truncate`), panjang teks sub-keterangan berpotensi memicu perbedaan tinggi kartu antarkolom pada rentang layar tablet hingga desktop.

2. **Jarak Antartombol Aksi yang Tidak Rapi & Asimetris**:
   - Tombol 1 (*Lihat Detail*) memiliki margin kanan manual `me-1` (4px).
   - Tombol 2 (*Dropdown Ubah Status*) dan Tombol 3 (*Hapus*) tidak memiliki margin antar-elemen. Akibatnya, jarak antara tombol 1 dan 2 berbeda dengan jarak antara tombol 2 dan 3 yang menempel rapat.
   - Tombol 3 (*Hapus*) sebelumnya menggunakan varian `btn-outline-danger` dengan padding arbitrer `px-2 py-1`, menjadikannya tidak simetris (bukan bujur sangkar `32px x 32px`) dibandingkan Tombol 1 (`btn-inverse-success`) dan Tombol 2 (`btn-inverse-info`).

---

## 2. Rincian Perubahan Kode

Perubahan diterapkan pada berkas view utama:
- **`resources/views/Admin/pesan_kontak/index.blade.php`**

### A. Penyelarasan Tiga Kartu Ringkasan KPI
1. **Standarisasi Grid Wrapper**: Ketiga kolom kartu kini menggunakan kelas pembungkus yang identik:
   ```html
   <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
   ```
2. **Ketinggian & Dimensi Terkunci**:
   - Menambahkan kelas utilitas `w-100 h-100` dan gaya inline `min-height: 108px; border-radius: 12px;` pada ketiga kartu (`.card.stat-card`).
   - Menambahkan padding responsif `p-3 p-sm-4 h-100` pada `.card-body` kartu.
3. **Penyelarasan Komponen Fleksibel**:
   - Menambahkan `flex-shrink-0` pada `.stat-icon` agar ikon tidak pernah terkompresi.
   - Memberikan `class="flex-grow-1" style="min-width: 0;"` pada kontainer teks.
   - Menambahkan kelas `text-truncate` dan atribut `title` pendukung pada judul label dan deskripsi subteks untuk mencegah luapan (*overflow*).

### B. Penataan & Standarisasi Tombol Aksi Tabel
1. **Container Flexbox Terpadu dengan `gap`**:
   - Mengganti pembungkus baris tombol aksi:
     ```html
     <div class="d-inline-flex align-items-center justify-content-center" style="gap: 6px;">
     ```
   - Menghapus margin kanan manual `me-1` pada tombol pertama sehingga jarak antar-seluruh tombol (1–2 dan 2–3) seragam 6 piksel.
2. **Harmonisasi Varian & Ukuran Tombol Hapus**:
   - Mengubah Tombol Hapus dari `btn-outline-danger px-2 py-1` menjadi `btn-inverse-danger d-inline-flex align-items-center justify-content-center`.
   - Mengunci dimensi tombol menjadi `width: 32px; height: 32px; padding: 0; border-radius: 6px;`.
   - Menggunakan ikon seragam `mdi-delete` dengan ukuran font 16px.

---

## 3. Komparasi Kode (Sebelum vs Sesudah)

### A. Kartu Ringkasan KPI (`pesan_kontak/index.blade.php`)
```html
<!-- SEBELUM -->
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
    <!-- Belum Dibaca -->
    <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        ...
    </div>
    <!-- Sudah Dibalas (Tanpa mb-3 mb-md-0) -->
    <div class="col-md-4 stretch-card grid-margin">
        ...
    </div>
</div>

<!-- SESUDAH -->
<div class="row mb-4">
    <!-- Total Pesan Masuk -->
    <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card stat-card stat-card-indigo border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
            <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                <div class="stat-icon flex-shrink-0">
                    <i class="mdi mdi-email-multiple-outline"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <p class="stat-label mb-0 text-truncate" title="Total Pesan Masuk">Total Pesan Masuk</p>
                    <h3 class="stat-value my-1">{{ number_format($totalMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0 text-truncate" title="Akumulasi seluruh pesan kontak">Akumulasi seluruh pesan kontak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Belum Dibaca -->
    <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card stat-card stat-card-coral border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
            <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                <div class="stat-icon flex-shrink-0">
                    <i class="mdi mdi-email-alert-outline"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <p class="stat-label mb-0 text-truncate" title="Belum Dibaca">Belum Dibaca</p>
                    <h3 class="stat-value my-1">{{ number_format($unreadMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0 text-truncate" title="Memerlukan respon admin">Memerlukan respon admin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sudah Dibalas -->
    <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
        <div class="card stat-card stat-card-teal border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
            <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                <div class="stat-icon flex-shrink-0">
                    <i class="mdi mdi-email-check-outline"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <p class="stat-label mb-0 text-truncate" title="Sudah Dibalas">Sudah Dibalas</p>
                    <h3 class="stat-value my-1">{{ number_format($repliedMessages, 0, ',', '.') }}</h3>
                    <p class="stat-change mb-0 text-truncate" title="Pesan yang telah ditindaklanjuti">Pesan yang telah ditindaklanjuti</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

### B. Tombol Aksi Tabel (`pesan_kontak/index.blade.php`)
```html
<!-- SEBELUM -->
<td class="text-center">
    <div class="d-inline-flex align-items-center">
        <!-- Tombol Baca Modal -->
        <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1"
                style="width: 32px; height: 32px; padding: 0;"
                onclick="openMessageDetail(...)">
            <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
        </button>

        <!-- Dropdown Ubah Status -->
        <div class="dropdown d-inline">
            <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; padding: 0;" title="Ubah Status">
                <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
            </button>
            ...
        </div>

        <!-- Tombol Hapus -->
        <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline" id="deleteForm-{{ $msg->id }}">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" onclick="confirmDelete({{ $msg->id }})" title="Hapus Pesan">
                <i class="mdi mdi-delete-outline"></i>
            </button>
        </form>
    </div>
</td>

<!-- SESUDAH -->
<td class="text-center">
    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 6px;">
        <!-- Tombol Baca Modal -->
        <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                onclick="openMessageDetail(...)"
                title="Lihat Detail Pesan">
            <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
        </button>

        <!-- Dropdown Ubah Status -->
        <div class="dropdown d-inline">
            <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; padding: 0; border-radius: 6px;" title="Ubah Status">
                <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
            </button>
            ...
        </div>

        <!-- Tombol Hapus -->
        <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline delete-form" id="deleteForm-{{ $msg->id }}">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                    style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                    onclick="confirmDelete({{ $msg->id }})" title="Hapus Pesan">
                <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
            </button>
        </form>
    </div>
</td>
```

---

## 4. Hasil Verifikasi & Pengujian Otomatis

1. **Kompilasi Blade View Cache**:
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

3. **Seluruh Test Suite Aplikasi**:
   ```bash
   php artisan test
   ```
   *Hasil*: **165 passed (839 assertions)** lulus 100% tanpa adanya regresi sistem.
