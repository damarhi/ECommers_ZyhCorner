# Dokumentasi Pencegahan Scrolling Tabel Saat Tombol Aksi Status Aktif

## 1. Latar Belakang & Analisis Masalah

Pada halaman administrasi **Pesan Kontak Pelanggan** (`resources/views/Admin/pesan_kontak/index.blade.php`), saat admin mengklik tombol aksi **Ubah Status** (ikon list biru), menu dropdown opsi status (`Belum Dibaca`, `Dibaca`, `Dibalas`) terbuka, namun antarmuka memicu kemunculan bilah penggulir (*scrollbar*) dan tabel menjadi dapat digulir (*scrolling*).

### Akar Masalah Teknis (Root Cause Analysis)
1. **Batasan Dua Sumbu W3C CSS (*Two-Axis Overflow Constraint*)**:
   Tabel dibungkus di dalam kontainer `.table-responsive` yang memiliki aturan bawaan:
   ```css
   .table-responsive {
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
   }
   ```
   Berdasarkan spesifikasi resmi W3C CSS Overflow Module, sebuah elemen **tidak dapat** menggabungkan `overflow-x: auto` dengan `overflow-y: visible`. Saat salah satu sumbu bernilai `auto`, sumbu lainnya otomatis dihitung (*computed*) oleh engine browser menjadi `auto`.
2. **Ketinggian Tabel yang Pendek (*Short Table Content*)**:
   Ketika tabel hanya memiliki sedikit baris (misalnya 2 pesan kontak), tinggi alami elemen `<table>` dan `<tbody>` hanya sekitar 130px–140px.
3. **Menu Dropdown Berposisi Absolut Melampaui Batas Kontainer**:
   Secara default pada Bootstrap 5, menu dropdown ditempatkan dengan `position: absolute` di dalam elemen sel `<td>` pada baris yang bersangkutan. Menu dropdown yang memiliki tinggi ~135px pada baris terbawah (baris ke-2) mencuat keluar melampaui batas bawah elemen `<tbody>` dan `.table-responsive`.
4. **Pemicuan Scrollbar**:
   Karena anak elemen keluar dari batas wadah yang berstatus `overflow: auto`, browser membaca terjadinya luapan konten (*content overflow*) dan mengaktifkan area scrolling (horizontal maupun vertikal). Akibatnya, tabel dapat digeser ke kiri/kanan/bawah dan tata letak bergeser (*layout shift*).

---

## 2. Rincian Solusi & Implementasi Desain (`skill:frontend-design-direction`)

Untuk mengatasi masalah tersebut secara permanen tanpa mengorbankan fungsionalitas responsif, diterapkan pendekatan **dua lapis pengaman (*defense-in-depth*)**:

### A. Lapis 1: Popper Strategy `fixed` & Viewport Boundary
Pada tombol trigger dropdown status di `resources/views/Admin/pesan_kontak/index.blade.php`, ditambahkan konfigurasi Popper:
- `data-bs-popper-config='{"strategy":"fixed"}'`
- `data-bs-boundary="viewport"`

**Dampak Teknis**:
Popper mengatur elemen `.dropdown-menu` menggunakan `position: fixed` relatif terhadap *viewport*. Elemen dengan `position: fixed` dikeluarkan dari perhitungan aliran tata letak (*layout flow*) dan batas overflow kontainer `.table-responsive`. Akibatnya, kontainer tabel tidak mendeteksi adanya elemen anak yang meluap, sehingga **scrollbar sama sekali tidak dipicu**.

### B. Lapis 2: Dynamic CSS Overflow Guard (`:has(.dropdown-menu.show)`)
Pada stylesheet global `public/css/admin.css` (bagian tabel), ditambahkan aturan:
```css
/* Table Dropdown Scroll Prevention Guard */
.table-responsive:has(.dropdown-menu.show) {
    overflow: visible !important;
}

.table-responsive .dropdown-menu {
    z-index: 1055 !important;
}
```
**Dampak Teknis**:
Menggunakan CSS pseudo-class `:has()`, kontainer `.table-responsive` secara dinamis diatur menjadi `overflow: visible` **hanya saat menu dropdown berstatus terbuka (`.show`)**. Menu dropdown dapat melayang bebas tanpa terpotong (*clipped*) atau memicu scrollbar. Begitu dropdown ditutup, `overflow-x: auto` kembali aktif.

### C. Lapis 3: Ruang Dasar Tabel (*Breathing Room*)
Pada `resources/views/Admin/pesan_kontak/index.blade.php`, elemen `.table-responsive` diberikan gaya dasar:
```html
<div class="table-responsive" style="min-height: 220px;">
```
Hal ini memastikan tabel memiliki ruang vertikal yang lapang saat memuat sedikit data (1–2 baris), sehingga dropdown tidak menimpa garis batas bawah card.

---

## 3. Berkas yang Diubah

| No | Berkas | Perubahan |
|----|--------|-----------|
| 1 | `public/css/admin.css` | Menambahkan aturan fail-safe `.table-responsive:has(.dropdown-menu.show) { overflow: visible !important; }` dan deklarasi `z-index: 1055 !important;`. |
| 2 | `resources/views/Admin/pesan_kontak/index.blade.php` | Menambahkan `style="min-height: 220px;"` pada `.table-responsive` dan atribut `data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="viewport"` pada tombol dropdown Ubah Status. |

---

## 4. Komparasi Kode (Sebelum vs Sesudah)

### A. View (`resources/views/Admin/pesan_kontak/index.blade.php`)
```html
<!-- SEBELUM -->
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
    ...
    <div class="dropdown d-inline">
        <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                title="Ubah Status">
            <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12.5px; border-radius: 8px;">
            ...
        </ul>
    </div>
</div>

<!-- SESUDAH -->
<div class="table-responsive" style="min-height: 220px;">
    <table class="table table-hover align-middle mb-0">
    ...
    <div class="dropdown d-inline">
        <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-popper-config='{"strategy":"fixed"}'
                data-bs-boundary="viewport"
                aria-expanded="false"
                style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                title="Ubah Status">
            <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12.5px; border-radius: 8px;">
            ...
        </ul>
    </div>
</div>
```

### B. Stylesheet (`public/css/admin.css`)
```css
/* DITAMBAHKAN */
/* Table Dropdown Scroll Prevention Guard */
.table-responsive:has(.dropdown-menu.show) {
    overflow: visible !important;
}

.table-responsive .dropdown-menu {
    z-index: 1055 !important;
}
```

---

## 5. Hasil Uji Otomatis & Verifikasi

1. **Kompilasi Blade View Cache**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil*: `Compiled views cleared successfully. Blade templates cached successfully.` (0 error).

2. **Feature Test Admin Contact Message**:
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
   *Hasil*: **165 passed (839 assertions)** lulus 100% tanpa ada regresi sistem.
