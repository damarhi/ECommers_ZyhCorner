# Rencana Perbaikan Nomor Telepon Navbar & Pengalihan Tautan Footer ke WhatsApp

Dokumen ini merinci rencana perbaikan untuk mengatasi nomor telepon statis pada navbar desktop dan mengalihkan seluruh tautan telepon pada footer dan mobile drawer agar langsung membuka WhatsApp resmi admin Zyh Corner.

---

## 1. Analisis Masalah

1. **Nomor Statis pada Navbar Desktop (`layout_pelanggan.blade.php`)**:
   - Bagian `.nav-phone` pada navbar desktop di-hardcode ke `<span>(+62) 8123456789</span>` dan bukan merupakan elemen interaktif (tidak bisa diklik).
   - Seharusnya menampilkan nomor dinamis toko/admin (`$storeSettings['store_phone']`) dan dapat diklik langsung membuka obrolan WhatsApp toko (`https://wa.me/...`).
2. **Nomor Telepon Footer Masih Menggunakan Protokol `tel:`**:
   - Di footer (`layout_pelanggan.blade.php`), nomor kontak menggunakan `href="tel:+..."`.
   - Permintaan spesifik mengharuskan tautan nomor HP pada footer diarahkan ke WhatsApp (`https://wa.me/...`).
3. **Penyelarasan Mobile Drawer**:
   - Tautan telepon pada mobile drawer juga diselaraskan agar langsung mengarah ke WhatsApp toko.

---

## 2. Rincian Langkah Implementasi (Step-by-Step)

### Step 1: Perbaikan Navbar Desktop dan Mobile Drawer ke WhatsApp Dinamis
- **Tujuan**: Mengubah `.nav-phone` pada navbar desktop dan mobile drawer menjadi tautan WhatsApp dinamis mengonsumsi `$storeSettings['wa_number']` dan `$storeSettings['store_phone']`.
- **Perubahan Kode**:
  - `resources/views/Layout/layout_pelanggan.blade.php`:
    - Ubah `.nav-phone` menjadi tag `<a>` dengan `href="https://wa.me/{{ $storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber() }}" target="_blank" rel="noopener noreferrer"`.
    - Ganti teks hardcoded `(+62) 8123456789` dengan `{{ $storeSettings['store_phone'] ?? '(+62) 812-3456-789' }}`.
    - Pada mobile drawer, ubah `href="tel:+..."` menjadi `href="https://wa.me/{{ $storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber() }}" target="_blank"`.

### Step 2: Pengalihan Tautan Nomor HP Footer ke WhatsApp
- **Tujuan**: Mengarahkan tautan nomor telepon di kolom pertama footer langsung ke WhatsApp toko.
- **Perubahan Kode**:
  - `resources/views/Layout/layout_pelanggan.blade.php`:
    - Pada footer baris nomor telepon, ubah `href="tel:+..."` menjadi `href="https://wa.me/{{ $storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber() }}" target="_blank" rel="noopener noreferrer"`.
    - Tambahkan atribut `title="Chat WhatsApp Toko"`.

### Step 3: Pengujian Otomatis dan Validasi
- **Tujuan**: Menambahkan assertion pada test suite untuk memverifikasi navbar, drawer, dan footer mengarah ke WhatsApp dinamis tanpa sisa hardcode.
- **Perubahan Kode**:
  - Perbarui `tests/Feature/AdminContactSyncTest.php`:
    - Pastikan respons view beranda/katalog memuat `https://wa.me/` pada navbar dan footer.
    - Pastikan teks hardcode `(+62) 8123456789` tidak lagi muncul di halaman publik mana pun.
