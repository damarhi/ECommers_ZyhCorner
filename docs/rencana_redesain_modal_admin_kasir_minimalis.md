# Rencana Induk: Standarisasi Redesain Seluruh Modal Admin & Kasir (Simpel & Minimalis)

## 1. Filosofi & Panduan Desain (*Frontend Design Direction*)

Mengacu pada prinsip **`frontend-design-direction`**, desain seluruh modal pada dasbor Admin dan Kasir ZYHCORNER harus mengedepankan kesederhanaan, kecepatan pemindaian data (*rapid scanning*), dan estetika modern yang tenang (*calm & utilitarian*):

1. **Simpel & Bersih (*No Visual Clutter*)**:
   - **Bebas Gradien Berlebih**: Menghapus seluruh header ungu gelap tebal (`bg-gradient-primary text-white`) pada modal dan menggantinya dengan latar putih bersih (`bg-white`) yang elegan.
   - **Bebas Anti-Pattern "Card di dalam Card"**: Tidak menggunakan kartu abu-abu bertumpuk di dalam body modal. Sebagai gantinya, digunakan kontainer bergaris batas tipis (*hairline border*) `1px solid #e2e8f0` dengan latar belakang `#f8fafc`.
   - **Bebas Titik Dua Mentah (`:`)**: Format label dan nilai ditata menggunakan hierarki tipografi vertikal (*label kecil di atas, nilai tebal di bawah*) atau grid *key-value* flexbox yang rapi.

2. **Kompak & Terpusat (*Ergonomic Dimensions*)**:
   - Seluruh modal wajib menggunakan `modal-dialog-centered`.
   - Sudut membulat modern: `border-radius: 12px; overflow: hidden;`.
   - **Proteksi Tinggi Layar**: Seluruh modal diberi batas `max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;` dengan `modal-body` bertipe `overflow-y: auto;` agar tidak pernah terpotong di layar laptop (768p/900p).
   - **Standar 3 Ukuran Lebar Terkendali**:
     * **Compact Form (440px - 460px)**: Kategori, Tindakan Kadaluarsa, Edit Status.
     * **Standard Form (500px - 520px)**: Tambah/Edit Kasir, Tambah/Edit Pelanggan, Tambah Stok POS, Edit Retur, Edit Diskon.
     * **Wide / Detail View (680px - 720px)**: Detail Retur, Tambah Diskon Multi-Produk, Detail Pesan Kontak, Tambah/Edit/Preview Banner.

3. **Tipografi & Ikonografi Minimalis**:
   - Header dilengkapi ikon sirkular lembut ($34 \times 34\text{ px}$) bersaturasi rendah.
   - Field input dilengkapi *input-group prefix* ikon esensial (nama: user, email: amplop, telp: whatsapp, harga: `Rp`, diskon: `%`, kategori: tag).
   - Pengelompokan input menggunakan **Grid 2 Kolom** (`row g-2`) untuk memangkas keharusan *scroll* panjang.

---

## 2. Rincian Redesain per Modul (15 Modal)

### Fase 1: Modul Penjualan & Kasir POS
1. **Modal Tambah Stok POS (`resources/views/Admin/kelola_penjualan/pos.blade.php`)**:
   - **Ukuran**: `max-width: 480px; modal-dialog-centered`.
   - **Header**: Latar putih, badge hijau lembut `mdi-package-variant-plus`, judul "Tambah Stok Produk".
   - **Body**: Baris pratinjau produk simpel (thumbnail kecil 44px, nama produk, badge stok saat ini), input angka stok dengan input-group bersih.
   - **Footer**: Tombol Batal dan tombol Simpan Stok.
2. **Modal Detail Retur (`resources/views/Admin/kelola_penjualan/returs.blade.php`)**:
   - **Ukuran**: `max-width: 700px; modal-dialog-centered`.
   - **Body**: Grid 2 kolom seimbang (Kiri: Info Pelanggan, No Pesanan, Alasan Retur; Kanan: Bukti Foto Retur dalam frame proporsional).
3. **Modal Tambah & Edit Retur (`resources/views/Admin/kelola_penjualan/returs.blade.php`)**:
   - **Ukuran**: `max-width: 520px; modal-dialog-centered`.
   - **Body**: Input group bersih untuk nama pelanggan, nomor pesanan, dropdown alasan retur, dan dropzone upload bukti foto ringkas.

---

### Fase 2: Modul Kelola Akun (Kasir & Pelanggan)
4. **Modal Tambah & Edit Kasir (`resources/views/Admin/kelola_akun/cassiers.blade.php`)**:
   - **Ukuran**: `max-width: 500px; modal-dialog-centered`.
   - **Body**: Grid 2 kolom untuk data kontak (Email & No. WhatsApp), input group ikon gembok untuk Password, textarea alamat ringkas.
5. **Modal Tambah & Edit Pelanggan (`resources/views/Admin/kelola_akun/customers.blade.php`)**:
   - **Ukuran**: `max-width: 500px; modal-dialog-centered`.
   - **Body**: Seragam dengan form kasir, input group nama, email, nomor HP, dan alamat.

---

### Fase 3: Modul Kelola Data Produk
6. **Modal Tambah & Edit Kategori (`resources/views/Admin/kelola_produk/categories.blade.php`)**:
   - **Ukuran**: `max-width: 440px; modal-dialog-centered` (super simpel & ringkas).
   - **Body**: Satu input grup dengan ikon tag `mdi-tag-outline`, label jelas, dan teks bantuan format slug.
7. **Modal Tambah Diskon (`resources/views/Admin/kelola_produk/discounts.blade.php`)**:
   - **Ukuran**: `max-width: 640px; modal-dialog-centered`.
   - **Body**: Daftar centang produk dengan container scrollable (`max-height: 140px`), input persentase ber-prefix `%`, dan grid 2 kolom untuk Tanggal Mulai & Tanggal Berakhir.
8. **Modal Edit Diskon (`resources/views/Admin/kelola_produk/discounts.blade.php`)**:
   - **Ukuran**: `max-width: 480px; modal-dialog-centered`.
   - **Body**: Info nama produk terkunci, persentase diskon, dan rentang tanggal.
9. **Modal Tindakan Produk Kadaluarsa (`resources/views/Admin/kelola_produk/expired_products.blade.php`)**:
   - **Ukuran**: `max-width: 480px; modal-dialog-centered`.
   - **Body**: Kotak ringkasan produk kadaluarsa (nama, tanggal exp, stok), dropdown status tindakan (Dibuang / Dimusnahkan / Didonasikan / Dikembalikan), dan catatan.

---

### Fase 4: Modul Carousel Banner & Pesan Kontak
10. **Modal Tambah & Edit Banner (`resources/views/Admin/kelola_carousel/banners.blade.php`)**:
    - **Ukuran**: `max-width: 700px; modal-dialog-centered`.
    - **Body**: Grid 2 kolom (Kiri: Unggah gambar banner ber-preview responsif; Kanan: Isian judul, deskripsi, posisi tampilan, dan link target).
11. **Modal Pratinjau Banner (`previewBannerModal` di `banners.blade.php`)**:
    - **Ukuran**: `max-width: 720px; modal-dialog-centered`.
    - **Body**: Mockup canvas minimalis tanpa elemen dekoratif berlebih yang meniru tampilan banner pada sisi pelanggan.
12. **Modal Detail Pesan Kontak (`resources/views/Admin/pesan_kontak/index.blade.php`)**:
    - **Ukuran**: `max-width: 640px; modal-dialog-centered`.
    - **Body**: Menghilangkan header ungu gelap. Menampilkan kartu identitas pengirim (Nama, Email, Tanggal), boks kutipan pesan dengan tipografi tenang, serta tombol aksi cepat untuk menandai sudah dibalas.

---

## 3. Matriks Eksekusi & Prioritas

| Fase | Target File | Komponen Modal | Skala Lebar | Estimasi Perubahan |
| :---: | :--- | :--- | :---: | :--- |
| **Fase 1** | `pos.blade.php`<br>`returs.blade.php` | Tambah Stok POS,<br>Detail Retur, Tambah Retur, Edit Retur | 480px - 700px | Merampingkan header, membuat form stok kasir instan, dan menata bukti foto retur 2 kolom. |
| **Fase 2** | `cassiers.blade.php`<br>`customers.blade.php` | Tambah Kasir, Edit Kasir,<br>Tambah Pelanggan, Edit Pelanggan | 500px | Menata input group ber-ikon, merapikan grid email & WhatsApp. |
| **Fase 3** | `categories.blade.php`<br>`discounts.blade.php`<br>`expired_products.blade.php` | Tambah/Edit Kategori,<br>Tambah/Edit Diskon,<br>Tindakan Kadaluarsa | 440px - 640px | Form kategori minimalis, selector multi-produk diskon scrollable, dan dialog tindakan kadaluarsa. |
| **Fase 4** | `banners.blade.php`<br>`pesan_kontak/index.blade.php` | Tambah/Edit/Preview Banner,<br>Detail Pesan Kontak | 640px - 720px | Header putih modern, layout 2 kolom upload banner, dan tampilan baca pesan kontak bersih. |

---

## 4. Jaminan Mutu & Verifikasi

1. **Blade Template View Compilation**:
   `php artisan view:clear && php artisan view:cache` dijalankan pada setiap fase untuk memastikan bebas galat kompilasi.
2. **Automated Test Suite**:
   `php artisan test` dijalankan secara berkala untuk memastikan 100% dari 147 test cases tetap lulus (*Green*).
3. **Dokumentasi Lengkap**:
   Setiap perubahan kode dicatat secara rinci ke dalam folder `docs/`.
