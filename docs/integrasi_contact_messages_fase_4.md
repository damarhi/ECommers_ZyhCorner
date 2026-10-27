# Dokumentasi Integrasi Tabel & Fitur Pengiriman Pesan Kontak (Fase 4)

## 1. Latar Belakang & Masalah Sebelumnya
Sebelum implementasi **Fase 4**, formulir kontak *"Just Say Hello"* pada halaman kontak pelanggan (`/contact`) hanya berupa *mockup* antarmuka (dummy). Ketika pengunjung mengisi formulir dan menekan tombol **Send Message**, formulir hanya memicu fungsi JavaScript `handleContactSubmit` yang menampilkan alert statis SweetAlert2 tanpa melakukan pengiriman data ke server (*backend*) dan tanpa menyimpan pesan ke database.

Akibatnya:
1. Pesan, masukan, dan permintaan kustom dari pelanggan tidak pernah sampai ke pemilik toko atau administrator.
2. Tidak ada riwayat pesan yang tersimpan di sistem.
3. Panel admin belum memiliki modul *Inbox* / Pesan Masuk untuk membaca dan menindaklanjuti pesan pelanggan.
4. Ikon notifikasi pesan di navbar atas admin menampilkan data statis contoh bawaan template ("Mark", "Cregh", "4 pesan baru").

---

## 2. Rincian Solusi & Implementasi Teknis

### A. Skema Database & Model `ContactMessage`
Dibuat tabel baru `contact_messages` melalui berkas migrasi:
- **Migration**: `database/migrations/2026_10_23_000002_create_contact_messages_table.php`
- **Model**: `app/Models/ContactMessage.php`

**Struktur Tabel `contact_messages`:**
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `BIGINT (PK)` | Auto-increment identifier pesan |
| `nama` | `VARCHAR(255)` | Nama lengkap pengirim pesan |
| `email` | `VARCHAR(255)` | Alamat surel pengirim pesan |
| `subjek` | `VARCHAR(255)` | Subjek atau judul pesan |
| `pesan` | `TEXT` | Isi pesan lengkap dari pelanggan |
| `status` | `ENUM` | Nilai status: `'Belum Dibaca'`, `'Dibaca'`, `'Dibalas'` (default: `'Belum Dibaca'`) |
| `created_at` | `TIMESTAMP` | Waktu pengiriman pesan |
| `updated_at` | `TIMESTAMP` | Waktu perubahan status pesan |

### B. Route & Controller Pelanggan (`PelangganController.php`)
Didaftarkan route publik baru:
- `POST /contact/send` $\rightarrow$ `PelangganController@sendContactMessage` (`name: contact.send`)

Metode `sendContactMessage` melakukan:
1. Validasi input:
   - `nama`: `required|string|max:255`
   - `email`: `required|email|max:255`
   - `subjek`: `required|string|max:255`
   - `pesan`: `required|string|max:3000`
2. Sanitasi data menggunakan `strip_tags` dan `filter_var(..., FILTER_SANITIZE_EMAIL)`.
3. Penyimpanan data ke tabel `contact_messages` dengan status awal `'Belum Dibaca'`.
4. Pengembalian respon JSON terstruktur `{ success: true, message: '...', data: ... }` untuk AJAX request atau *redirect back with flash message* untuk submit konvensional.

### C. Integrasi Frontend Form Kontak (`contact.blade.php`)
1. Formulir `#contactForm` diperbarui dengan atribut `action="{{ route('contact.send') }}"`, `@csrf`, serta atribut `name` yang sesuai (`nama`, `email`, `subjek`, `pesan`).
2. Fungsi `handleContactSubmit(event)` diubah untuk melakukan AJAX HTTP `POST` via `fetch`:
   - Tombol submit dinonaktifkan sementara dan menampilkan animasi spinner: `<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...`.
   - Mengirim payload JSON disertai header `X-CSRF-TOKEN` dan `Accept: application/json`.
   - Menampilkan SweetAlert2 sukses dan melakukan form reset otomatis jika pesan berhasil terkirim.
   - Menampilkan SweetAlert2 galat jika terjadi kegagalan validasi atau masalah jaringan.
   - Mengembalikan tombol ke keadaan aktif semula pada blok `finally`.

### D. Modul Admin Inbox & Manajemen Pesan (`Admin/pesan_kontak/index.blade.php`)
Dibuat pengontrol baru:
- **Controller**: `app/Http/Controllers/Admin/ContactMessageController.php`
- **Views**: `resources/views/Admin/pesan_kontak/index.blade.php`
- **Routes**:
  - `GET /admin/messages` $\rightarrow$ `ContactMessageController@index`
  - `GET /admin/messages/{id}` $\rightarrow$ `ContactMessageController@show`
  - `PATCH /admin/messages/{id}/status` $\rightarrow$ `ContactMessageController@updateStatus`
  - `DELETE /admin/messages/{id}` $\rightarrow$ `ContactMessageController@destroy`
  - `POST /admin/messages/bulk-delete` $\rightarrow$ `ContactMessageController@bulkDelete`

**Fitur Modul Admin:**
1. **Rangkuman Statistik KPI Card**:
   - Total Pesan Masuk
   - Pesan Belum Dibaca (*warning badge*)
   - Pesan Sudah Dibalas (*success badge*)
2. **Tab Navigasi Filter**:
   - Filter cepat berdasarkan status (`Semua`, `Belum Dibaca`, `Dibaca`, `Dibalas`) disertai jumlah pesan.
3. **Pencarian Real-Time**:
   - Pencarian nama pengirim, email, subjek, maupun isi pesan.
4. **Tabel Interaktif**:
   - Baris pesan yang berstatus `'Belum Dibaca'` ditandai dengan sorotan warna *warning* dan font tebal (*bold*).
   - Checkbox *bulk selection* untuk menghapus beberapa pesan sekaligus.
5. **Modal Baca Detail Pesan**:
   - Membuka modal yang menampilkan nama pengirim, email yang dapat diklik, waktu pesan terkirim, badge status, subjek, dan isi pesan utuh.
   - Saat modal dibuka untuk pesan yang berstatus `'Belum Dibaca'`, sistem otomatis mengirimkan permintaan AJAX latar belakang ke `/admin/messages/{id}` untuk mengubah status menjadi `'Dibaca'`.
   - Tombol cepat **Balas via Email** (`mailto:...`) dengan template subjek dan cuplikan pesan pengirim otomatis terisi.
   - Tombol cepat **Tandai Sudah Dibalas** langsung dari dalam modal.
6. **Ubah Status & Hapus Pesan**:
   - Dropdown menu untuk mengubah status ke salah satu dari: `Belum Dibaca`, `Dibaca`, atau `Dibalas`.
   - Konfirmasi SweetAlert2 sebelum penghapusan data tunggal maupun massal.

### E. Integrasi Navigasi Panel Admin (`layout_pAdmin.blade.php`)
1. **Sidebar Navigation**:
   - Menambahkan kategori navigasi `Interaksi Pelanggan` dengan menu **Pesan Kontak** (`mdi-email-open-multiple-outline`).
   - Menyematkan badge counter berwarna merah dinamis yang menampilkan jumlah pesan berstatus `'Belum Dibaca'`.
2. **Top Navbar Message Dropdown**:
   - Menghapus template statis bawaan ("Mark", "Cregh").
   - Menghubungkan dropdown pesan ke 3 pesan kontak terbaru dari database SQLite.
   - Menampilkan titik indikator kuning (`count-symbol bg-warning`) hanya jika ada pesan yang belum dibaca.
   - Tautan langsung ke halaman inbox `/admin/messages`.

---

## 3. Berkas yang Dibuat & Dimodifikasi

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   └── ContactMessageController.php (BARU)
│   │   └── Pelanggan/
│   │       └── PelangganController.php (MODIFIKASI: sendContactMessage)
│   └── Models/
│       └── ContactMessage.php (BARU)
├── database/
│   └── migrations/
│       └── 2026_10_23_000002_create_contact_messages_table.php (BARU)
├── resources/
│   └── views/
│       ├── Admin/
│       │   └── pesan_kontak/
│       │       └── index.blade.php (BARU)
│       ├── Layout/
│       │   └── layout_pAdmin.blade.php (MODIFIKASI: sidebar & navbar)
│       └── Pelanggan/
│           └── contact.blade.php (MODIFIKASI: AJAX form submit & alert)
├── routes/
│   └── web.php (MODIFIKASI: rute publik & rute admin messages)
└── docs/
    └── integrasi_contact_messages_fase_4.md (DOKUMENTASI INI)
```

---

## 4. Hasil Verifikasi End-to-End

### A. Pengujian Frontend Formulir Kontak (`/contact`)
- Pengujian dilakukan via browser headless Puppeteer/Chromium pada URL `http://clone_pa_zyhcorner.test/contact`.
- Pengisian data:
  - **Nama**: `Budi Pratama`
  - **Email**: `budi.pratama@example.com`
  - **Subjek**: `Pemesanan Kue Ulang Tahun Custom 3 Tingkat`
  - **Pesan**: `Halo Zyh Corner, saya ingin menanyakan estimasi biaya dan pemesanan kue ulang tahun bertema floral untuk tanggal 30 November. Mohon info lebih lanjut.`
- **Hasil**:
  - Tombol mengirimkan request `POST` ber-CSRF token ke `/contact/send`.
  - Respon HTTP 200 JSON `{ success: true, message: '...' }` diterima.
  - SweetAlert2 judul *"Pesan Terkirim!"* muncul.
  - Form otomatis di-reset.

### B. Pengujian Basis Data SQLite
- Query langsung ke `database/database.sqlite`:
  ```sql
  SELECT id, nama, email, subjek, pesan, status, created_at FROM contact_messages;
  ```
- **Hasil**: Data tersimpan sempurna dengan status awal `'Belum Dibaca'`.

### C. Pengujian Panel Admin (`/admin/messages`)
- Login sebagai administrator (`admin@gmail.com`).
- Navigasi ke `/admin/messages`:
  - Sidebar menampilkan badge merah `1` untuk pesan belum dibaca.
  - Kartu KPI menampilkan: Total Pesan `1`, Belum Dibaca `1`, Dibalas `0`.
  - Pesan Budi Pratama tampil di baris teratas dengan badge *Belum Dibaca*.
- Membuka modal detail pesan:
  - Seluruh isi pesan ditampilkan secara presisi.
  - Background AJAX otomatis memperbarui status pesan di SQLite menjadi `'Dibaca'`.
  - Tombol *"Tandai Sudah Dibalas"* ditekan $\rightarrow$ status berhasil diperbarui menjadi `'Dibalas'`, dan KPI ter-update menjadi Total `1`, Belum Dibaca `0`, Dibalas `1`.
  - Filter tab *Belum Dibaca* menampilkan *empty state*, dan filter tab *Dibalas* menampilkan pesan tersebut.

---

## 5. Kesimpulan
Fase 4 telah selesai 100% tanpa kendala maupun regresi. Fitur pengiriman pesan kontak kini beroperasi penuh secara nyata dari sisi pelanggan hingga dasbor pengelolaan administrator.

Aplikasi kini siap untuk melangkah ke **Fase 5: Tabel & Manajemen FAQ Dinamis (`faqs`)**.
