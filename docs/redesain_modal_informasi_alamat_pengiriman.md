# Rencana & Dokumentasi Desain: Modal Informasi Alamat Pengiriman (Minimalis, Simple & Rapi)

Mengikuti panduan **`frontend-design-direction`**, dokumen ini menguraikan analisis desain, arsitektur tata letak, hirarki visual, dan implementasi teknis untuk merapikan modal **Informasi Alamat Pengiriman** pada halaman rincian pesanan pelanggan (`order_detail.blade.php`).

---

## 1. Analisis Masalah Tampilan Sebelumnya (Visual Debt)

Berdasarkan inspeksi tangkapan layar modal sebelumnya:
1. **Vertical Stacking yang Monoton & Menumpuk**:
   - Field `Nama Penerima`, `Nomor Telepon`, dan `Email Pembeli` ditumpuk secara vertikal satu per satu sebagai teks polos tanpa pengelompokan yang jelas (*raw key-value dump*).
   - Mengambil ruang vertikal yang tinggi namun miskin pembeda konteks.
2. **Ketiadaan Pengelompokan Kartu (Chunking)**:
   - Data kontak, alamat fisik, dan instruksi kurir tercampur dalam satu wadah datar tanpa pembatas fungsional.
3. **Penataan Tombol Footer yang Canggung**:
   - Tombol "Tutup" berada sendirian di sisi kiri bawah dengan garis abu-abu tipis tanpa penegasan letak (*misaligned action anchor*). Standar UX modal menempatkan tombol penutup utama di sudut kanan bawah untuk alur mata pengguna yang alami.
4. **Catatan Pengiriman Kurang Terintegrasi**:
   - Catatan pengiriman menggunakan kotak terpisah yang terkadang terasa terisolasi atau mencolok tanpa keselarasan warna produk Zyh Corner.

---

## 2. Arahan Desain (Frontend Design Direction)

### 2.1. Desain Kontrak & Karakteristik
- **Purpose**: Membaca rincian kontak penerima, alamat pengantaran paket, serta catatan kurir secara cepat, jelas, dan nyaman tanpa kebingungan.
- **Audience**: Pelanggan yang ingin memastikan data pengiriman mereka akurat.
- **Tone**: *Clean, modern, utilitarian, calm, minimal*.
- **Memorable Detail**: Pengelompokan berbasis kartu micro-sections (`.modal-section-card`) dengan ikon semantik lembut, 2-kolom grid cerdas untuk nama & telepon, pembatas halus (*divider*), serta palet warna aksen yang serasi (hijau emerald khas Zyh Corner, merah lembut untuk lokasi, dan warm amber untuk catatan).

### 2.2. Struktur Visual (Wireframe Perbandingan)

#### Desain Sebelumnya (Flat & Menumpuk)
```
┌─────────────────────────────────────────────────────────┐
│ Informasi Alamat Pengiriman                         [×] │
│ Pesanan #GDUJRP                                         │
├─────────────────────────────────────────────────────────┤
│ Nama Penerima                                           │
│ nama_nama                                               │
│                                                         │
│ Nomor Telepon                                           │
│ (+62) 8123123123                                        │
│                                                         │
│ Email Pembeli                                           │
│ nama@gmail.com                                          │
│                                                         │
│ Alamat Lengkap                                          │
│ [Jl. Riau No. 123, Sukajadi, Pekanbaru (28261)        ] │
│                                                         │
│ Catatan Pengiriman                                      │
│ [sadads [Penerima: nama_nama (8123123123)]            ] │
├─────────────────────────────────────────────────────────┤
│ [Tutup]                                                 │
└─────────────────────────────────────────────────────────┘
```

#### Desain Baru (Minimalist Card-Based Layout)
```
┌─────────────────────────────────────────────────────────┐
│ 📍 Informasi Alamat Pengiriman                      [×] │
│ Pesanan #GDUJRP                                         │
├─────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 👤 KONTAK PENERIMA                                  │ │
│ │ ┌─────────────────────────┐ ┌─────────────────────┐ │ │
│ │ │ NAMA PENERIMA           │ │ NOMOR TELEPON       │ │ │
│ │ │ nama_nama               │ │ 📞 (+62) 8123123123 │ │ │
│ │ └─────────────────────────┘ └─────────────────────┘ │ │
│ │ ┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄ │ │
│ │ EMAIL PEMBELI                                       │ │
│ │ ✉️ nama@gmail.com                                   │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 📍 ALAMAT LENGKAP                                   │ │
│ │ Jl. Riau No. 123, Sukajadi, Pekanbaru (28261)       │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 📝 CATATAN PENGIRIMAN (Kondisional)                 │ │
│ │ sadads [Penerima: nama_nama (8123123123)]           │ │
│ └─────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────┤
│                                                 [Tutup] │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Komponen & Aturan CSS

### 3.1. Kartu Kontak Penerima (`.modal-section-card`)
- Menampung informasi nama, telepon, dan email.
- Menggunakan `display: grid; grid-template-columns: 1fr 1fr; gap: 12px;` untuk desktop, dan responsif menjadi 1 kolom pada ponsel pintar ($\le 576\text{px}$).
- Label menggunakan format mikro kapital bersih: `font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;`.
- Nilai kontak menggunakan warna pekat berkontras tinggi (`#0f172a` dan `#1e293b`).

### 3.2. Kartu Alamat Lengkap
- Menggunakan latar putih bersih dengan border halus `#e2e8f0` di atas alas dialog `#f8fafc`.
- Tipografi alamat: `font-size: 13px; color: #1e293b; font-weight: 500; line-height: 1.55;`.

### 3.3. Kartu Catatan Pengiriman (Kondisional)
- Hanya dirender jika `$order->catatan_pesanan` tidak kosong.
- Diberikan styling kartu tematik hangat (*warm alert tint*):
  - Latar belakang: `#fffdf5`
  - Border: `1px solid #fef08a`
  - Judul: `color: #92400e`
  - Teks catatan: `color: #78350f; font-size: 12.5px;`

### 3.4. Footer Dialog
- `display: flex; justify-content: flex-end;` sehingga tombol *Tutup* berada rapi di sisi kanan bawah.

---

## 4. Hasil Verifikasi

1. **Vite Asset Build**:
   ```text
   public/build/assets/pelanggan_pages-BaujATOc.css  137.65 kB │ gzip: 23.79 kB
   public/build/assets/pelanggan_pages-CTr8yAde.js    66.25 kB │ gzip: 13.98 kB
   ✓ built in 507ms
   ```
2. **PHPUnit Automated Test Suite**:
   ```text
   Tests:    130 passed (653 assertions)
   Duration: 26.38s
   ```
   Seluruh assertion kunci seperti `orderAddressDetailModal`, `Informasi Alamat Pengiriman`, `closeAddressDetailModal()`, `displayBillingPhoneModal`, dan `displayBillingEmailModal` lulus 100%.
