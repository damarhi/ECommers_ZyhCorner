# Penjelasan dan Perbaikan Progress Bar Stepper Pengiriman

## 1. Analisis Masalah Berdasarkan Screenshot dan Inspeksi Layout Engine

Berdasarkan analisis visual dan pengukuran layout geometris subpixel (menggunakan Chrome Headless layout bounding rect inspection) pada kartu status progres pengiriman (`resources/views/Pelanggan/order_detail.blade.php`), ditemukan 2 akar masalah utama mengapa tampilan sebelumnya tidak presisi:

---

### A. Mengapa Bar Tidak Berada di Tengah Ikon / Status (Vertikal)

1. **Akar Masalah Sumbu Vertikal (Y-Axis Offset)**:
   - Elemen lingkaran ikon (`.step-circle`) memiliki ukuran `44px × 44px` dengan `border: 2px solid #e2e8f0`.
   - Mengikuti CSS box model standar (`content-box` default pada elemen circle tertentu atau padding border), total tinggi fisik lingkaran adalah:
     $$\text{Tinggi Total} = 44\text{px} + 2\text{px (border atas)} + 2\text{px (border bawah)} = 48\text{px}$$
   - Titik pusat vertikal lingkaran (`center Y`) berada tepat di:
     $$\text{Pusat } Y = \frac{48\text{px}}{2} = 24\text{px}$$
   - Pada implementasi sebelumnya:
     - Garis track (`.stepper-track-wrap`) diberi `top: 24px` atau `top: 21px` **tanpa** transformasi titik tumpu vertikal (`transform: translateY(-50%)`).
     - Garis memiliki ketebalan `height: 4px`. Tanpa `translateY(-50%)`, garis dirender mulai dari $Y = 24\text{px}$ sampai $Y = 28\text{px}$, sehingga titik pusat garis berada di $Y = 26\text{px}$ (turun $2\text{px}$ ke bawah dari pusat lingkaran).
     - Pada versi lawas sebelum refaktor, garis bahkan menggunakan `top: 24px` dengan kontainer yang memiliki margin berbeda, sehingga garis terlihat turun ke bawah menyentuh tepi bawah lingkaran alih-alih membelah tengahnya.

2. **Solusi Vertikal yang Diterapkan**:
   ```css
   .stepper-track-wrap {
       position: absolute;
       top: 24px;
       transform: translateY(-50%);
       height: 4px;
       left: 10%;
       right: 10%;
       z-index: 1;
       pointer-events: none;
   }
   ```
   - Dengan `top: 24px;` dan `transform: translateY(-50%);`, titik pusat garis track $4\text{px}$ digeser ke atas sebesar $2\text{px}$ (50% dari tingginya), sehingga garis membentang simetris dari $Y = 22\text{px}$ hingga $Y = 26\text{px}$ dengan titik tengah **persis di $Y = 24\text{px}$** (`differenceY: 0`).

---

### B. Mengapa Panjangnya Tidak Full dari Langkah 1 Sampai 5 (Horizontal)

1. **Akar Masalah Sumbu Horizontal (X-Axis Span)**:
   - **Formula Lawas (`left: 100px; right: 100px`)**:
     Pada kode template awal, garis track diberi jarak statis `left: 100px; right: 100px;`.
     Karena lebar layar pengguna dan lebar kontainer kartu bervariasi (responsif), nilai statis $100\text{px}$ menyebabkan garis track memulai titik awalnya jauh ke kanan dari lingkaran langkah 1, dan berhenti jauh ke kiri sebelum mencapai lingkaran langkah 5 (timbul celah kosong sekitar $85\text{px}$ di sisi kiri dan $85\text{px}$ di sisi kanan).
   - **Perhitungan Geometris Kolom Flex 5-Langkah**:
     - Kontainer stepper menampung 5 langkah (`.step-item`) dengan lebar proporsional masing-masing $20\%$ (`flex: 1 1 20%`).
     - Kolom Langkah 1 menempati rentang $0\% - 20\%$. Titik pusat lingkaran Langkah 1 berada di:
       $$\text{Pusat Langkah 1} = 0\% + \frac{20\%}{2} = 10\%$$
     - Kolom Langkah 5 menempati rentang $80\% - 100\%$. Titik pusat lingkaran Langkah 5 berada di:
       $$\text{Pusat Langkah 5} = 80\% + \frac{20\%}{2} = 90\% \quad (\text{atau } 10\% \text{ dari tepi kanan})$$
     - Oleh karena itu, agar garis track membentang **penuh dari pusat lingkaran Langkah 1 ke pusat lingkaran Langkah 5**, garis track harus menggunakan:
       ```css
       left: 10%;
       right: 10%;
       ```
   - **Formula Lebar Aktif Lawas**:
     Pada kode lawas, progres garis aktif menggunakan rumus:
     `width: calc(({{ $connectorWidth }} / 100) * (100% - 200px));`
     Pengurang statis `- 200px` tersebut memotong lebar garis hijau aktif sehingga garis tidak pernah dapat menyentuh langkah aktif dengan pas di layar desktop.

2. **Solusi Horizontal yang Diterapkan**:
   - Garis track membentang proporsional dari `left: 10%` ke `right: 10%`.
   - Progres aktif (`.stepper-line-active`) menggunakan persentase murni tanpa offset pixel statis:
     ```html
     <div class="stepper-line-active" style="width: {{ $connectorWidth }}%;"></div>
     ```
     Dengan proporsi:
     - Langkah 1 (Pesanan Dibuat): $0\%$
     - Langkah 2 (Verifikasi Pembayaran): $25\%$ (tepat di pusat Langkah 2)
     - Langkah 3 (Dikonfirmasi & Dikemas): $50\%$ (tepat di pusat Langkah 3)
     - Langkah 4 (Sedang Dikirim): $75\%$ (tepat di pusat Langkah 4)
     - Langkah 5 (Pesanan Selesai): $100\%$ (tepat di pusat Langkah 5)

---

## 2. Hasil Verifikasi Subpixel Bounding Box (Chrome Engine)

Pengujian dilakukan menggunakan Chrome Headless dengan script inspeksi `getBoundingClientRect()`:

```json
{
  "trackCenterY": 1649.078125,
  "circleCenterY": 1649.078125,
  "differenceY": 0,
  "trackLeft": 168.5,
  "circle1CenterX": 168.5,
  "differenceX": 0,
  "trackRight": 1128.5,
  "circle5CenterX": 1128.5,
  "differenceXRight": 0
}
```

- **Penyelarasan Vertikal**: Selisih titik tengah Y antara garis track dan lingkaran adalah **0px** (presisi sempurna).
- **Penyelarasan Horizontal Kiri**: Selisih titik awal garis track dengan pusat lingkaran Langkah 1 adalah **0px** (`differenceX: 0`).
- **Penyelarasan Horizontal Kanan**: Selisih titik akhir garis track dengan pusat lingkaran Langkah 5 adalah **0px** (`differenceXRight: 0`).
- **Semua Test Suite**: 130/130 pengujian Laravel lulus (`php artisan test`).
