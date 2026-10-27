# Dokumentasi Redesain Minimalis Halaman Error 403 (*Forbidden Access*)

## 1. Pendekatan Desain (*Frontend Design Direction*)

Sesuai arahan panduan **`frontend-design-direction`**, halaman kesalahan 403 dirancang ulang dengan pendekatan **minimalis, tenang (*calm & refined*), dan berfokus tunggal (*single-focus*)**:
- **Anti-Pattern yang Dihilangkan**: Menghilangkan seluruh elemen bertumpuk (*no cards inside cards*), ikon merah besar yang agresif, info profil avatar pengguna, badge peran tambahan, dan tautan sekunder berlebih.
- **Karakteristik Visual**: Kartu terpusat dengan sudut membulat lembut (`border-radius: 20px`), bayangan halus (*soft ambient shadow*), tipografi bersih (*Plus Jakarta Sans*), dan palet warna sage/forest green khas Zyh Corner (`#024220`).

---

## 2. Struktur 4 Elemen Utama

Halaman kini hanya memuat **4 elemen esensial** yang diminta:

```
┌────────────────────────────────────────────────────────┐
│                                                        │
│                    [ LOGO BRAND ]                      │  <- 1. Logo lingkaran Zyh Corner (72px)
│                                                        │
│                 403 • FORBIDDEN                        │  <- 2. Tag & Teks Error
│                  Akses Ditolak                         │
│                                                        │
│       Halaman ini memiliki batasan akses khusus        │  <- 3. Penjelasan Error Ringkas
│       dan tidak dapat dibuka dengan akun Anda.         │
│                                                        │
│         [  Kembali ke Dashboard / Home  ]              │  <- 4. Tombol Kembali Cerdas
│                                                        │
└────────────────────────────────────────────────────────┘
```

### Rincian Komponen (`resources/views/errors/403.blade.php`)

1. **Logo Toko**:
   - Menampilkan gambar `asset('image/logo.jpg')` berukuran $72 \times 72\text{px}$ berbentuk lingkaran sempurna dengan bayangan lembut.
2. **Teks Error**:
   - *Status Tag*: `<span class="error-tag">403 &bull; Forbidden</span>` dengan latar abu-abu netral yang rapi.
   - *Judul Headline*: `<h1 class="error-title">Akses Ditolak</h1>` ($22\text{px}$, font-weight 700).
3. **Penjelasan Error**:
   - Menampilkan teks penjelasan yang santun dan informatif:
     `{{ $exception->getMessage() ?: 'Halaman ini memiliki batasan akses khusus dan tidak dapat dibuka dengan akun Anda saat ini.' }}`
4. **Tombol Kembali ke Home / Dashboard**:
   - Tombol utama tunggal bergaya *forest green* (`#024220`) dengan efek transisi hover lembut:
     - **Admin**: *"Kembali ke Dashboard Admin"* $\to$ mengarah ke `/admin/dashboard`.
     - **Kasir**: *"Kembali ke Kelola Pesanan"* $\to$ mengarah ke `/admin/orders`.
     - **Pelanggan**: *"Kembali ke Dashboard Saya"* $\to$ mengarah ke `/dashboard`.
     - **Tamu (*Guest*)**: *"Kembali ke Beranda"* $\to$ mengarah ke `/`.

---

## 3. Bukti Pengujian (*Automated Headless Browser*)

Pengujian dilakukan menggunakan browser headless pada resolusi **Desktop (1280x900)** dan **Mobile (390x844)**:

### A. Metrik & Hasil Inspeksi Desktop
- **Elemen Kartu Terdeteksi**: `cardFound: true` (lebar terpusat max 440px).
- **Integritas Logo**: `logoLoaded: true`, sumber `http://clone_pa_zyhcorner.test/image/logo.jpg`.
- **Status Tag**: `403 • FORBIDDEN`.
- **Judul**: `Akses Ditolak`.
- **Penjelasan**: `Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.`.
- **Nested Card Audit**: `hasNestedCards: false` (bebas dari kartu bersarang).
- **Screenshot Desktop**: Tersimpan di `docs/error_403_minimalist_desktop.png`.

### B. Metrik & Hasil Inspeksi Mobile (390x844)
- **Lebar Kartu**: $358\text{px}$ (responsif memenuhi viewport mobile dengan margin aman $16\text{px}$).
- **Tinggi Kartu**: $380.8\text{px}$ (proporsional dan tidak membutuhkan *scrolling*).
- **Dimensi Logo**: $64 \times 64\text{px}$.
- **Screenshot Mobile**: Tersimpan di `docs/error_403_minimalist_mobile.png`.

### C. Pengujian Fungsional Tombol Kembali
1. **Admin di Halaman 403**:
   - Label tombol: *"Kembali ke Dashboard Admin"*.
   - Saat diklik $\to$ berhasil kembali ke `http://clone_pa_zyhcorner.test/admin/dashboard` (Title: *Dashboard - Admin*).
2. **Pelanggan di Halaman 403**:
   - Label tombol: *"Kembali ke Dashboard Saya"*.
   - Saat diklik $\to$ berhasil kembali ke `http://clone_pa_zyhcorner.test/dashboard` (Title: *Customer Dashboard - Zyh Corner*).

---

## 4. Kesimpulan
Halaman Error 403 kini telah tampil minimalis, estetis, proporsional di seluruh ukuran layar, dan memenuhi seluruh kriteria tanpa elemen yang tidak diperlukan.
