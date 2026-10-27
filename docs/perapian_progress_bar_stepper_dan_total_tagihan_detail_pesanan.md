# Dokumentasi Perapian Visual Progress Bar Stepper & Total Tagihan Detail Pesanan

Dokumen ini mencatat penyelesaian perbaikan visual dan stabilitas tata letak pada halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`), yang mencakup perapian garis konektor pelacakan (*tracking stepper progress bar*) dan eliminasi pemecahan teks janggal (*awkward word wrapping*) pada blok Total Tagihan di kartu Rincian Pembayaran.

---

## 1. Analisis Masalah & Akar Penyebab (*Root Cause*)

### A. Masalah Teks "Total Tagihan" & Pemecahan Kata Kaku (*Awkward Word Wrapping*)
* **Gejala**:
  Pada kartu **"Rincian Pembayaran"** (`.summary-card`), teks pada baris total terpecah menjadi beberapa baris yang tidak rapi (misalnya: kata `"Total"` pada baris pertama dan `"Tagihan:"` jatuh ke baris kedua, atau simbol `"Rp."` terpisah dari nominal angkanya `"155.000"`).
* **Akar Masalah (*Root Cause*)**:
  1. Kartu Rincian Pembayaran berada di dalam grid Bootstrap 3 kolom (`col-md-4`). Dengan padding kartu sebelumnya sebesar `22px` di setiap sisi, lebar efektif area konten dalam menyusut cukup drastis.
  2. Elemen label dan nominal pada `.summary-calc-row.main-total` tidak diproteksi oleh deklarasi `white-space: nowrap;`.
  3. Ketiadaan kontainer penegas visual (*visual boundary container*) membuat baris total rentan tertekan oleh flexbox saat nama angka nominal bertambah panjang, merusak hierarki estetika ringkasan pembayaran.

### B. Masalah Garis Konektor Stepper (*Progress Bar Alignment & Overflow*)
* **Gejala**:
  1. Garis konektor track progres pelacakan (abu-abu `#e2e8f0` dan hijau `#00b207`) menembus atau melebihi (*overflow*) sisi kiri lingkaran pertama (`01 Pesanan Dibuat`) dan sisi kanan lingkaran terakhir (`05 Pesanan Selesai`).
  2. Garis konektor terlihat sedikit turun (tidak tepat berada di titik pusat lingkaran tahapan / *vertical misalignment*).
  3. Label nama langkah memiliki tinggi yang tidak seragam (label 1 baris seperti "Sedang Dikirim" vs label 2 baris seperti "Verifikasi Pembayaran"), menyebabkan ketidakteraturan baseline vertikal.
* **Akar Masalah (*Root Cause*)**:
  1. Implementasi sebelumnya menggunakan posisi absolut dengan offset statis dalam piksel:
     ```css
     .stepper-line-bg {
         position: absolute;
         top: 22px;
         left: 60px;
         right: 60px;
         height: 4px;
     }
     ```
     Ketika lebar layar atau kontainer berubah secara responsif, nilai statis `left: 60px` dan `right: 60px` tidak lagi bertepatan dengan titik tengah lingkaran terluar yang terdistribusi secara persentase dinamis.
  2. Secara vertikal, lingkaran tahapan memiliki diameter `44px` (titik pusat = `22px`). Ketika garis setinggi `4px` diposisikan pada `top: 22px`, pusat vertikal garis berada pada koordinat `22px + (4px / 2) = 24px`. Hal ini menyebabkan deviasi vertikal ke bawah sebesar `2px`.
  3. Elemen `.step-label` tidak memiliki `min-height` dan penataan flexbox yang menjamin keselarasan baseline antar kolom langkah.

---

## 2. Solusi Desain & Arsitektur Geometri

### A. Geometri Garis Konektor Berbasis Persentase Pusat (*Center-Anchored Track*)
Dengan 5 langkah (*step*) yang didistribusikan merata menggunakan Flexbox:
* Setiap `.step-item` memiliki lebar `20%` (`flex: 1 1 20%; max-width: 20%;`).
* Titik tengah horizontal lingkaran **Langkah 1** berada tepat di persentase:
  $$\text{Center}_{\text{Step 1}} = \frac{20\%}{2} = 10\%$$
* Titik tengah horizontal lingkaran **Langkah 5** berada tepat di persentase:
  $$\text{Center}_{\text{Step 5}} = 100\% - \frac{20\%}{2} = 90\%$$

Dengan menaruh pembungkus track `.stepper-track-wrap` pada:
```css
.stepper-track-wrap {
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 4px;
    z-index: 1;
}
```
Maka garis konektor secara matematis **dijamin 100% berawal dari titik pusat lingkaran 1 dan berakhir persis di titik pusat lingkaran 5**, tanpa pernah meluap (*zero overflow*) pada resolusi layar apa pun.

### B. Keselarasan Vertikal Sempurna (*Perfect Vertical Centering*)
* Tinggi Lingkaran (`.step-circle`): `44px` $\rightarrow$ Titik pusat vertikal = `22px`.
* Tinggi Garis Track (`.stepper-line-bg` / `.stepper-line-active`): `4px`.
* Penempatan `top`:
  $$\text{Top} = 22\text{px} - \left(\frac{4\text{px}}{2}\right) = 20\text{px}$$
Hasil: Garis konektor membelah titik tengah lingkaran secara presisi.

```
       [ Lingkaran 44px ]
      ┌──────────────────┐
      │                  │
20px ─┼──────────────────┼─  <-- Top Garis Track (4px)
22px ─┼─────── Pusat ────┼─  <-- Pusat Garis & Lingkaran Presisi
24px ─┼──────────────────┼─  <-- Bottom Garis Track
      │                  │
      └──────────────────┘
```

### C. Alur Progres Garis Aktif
Panjang garis aktif (`.stepper-line-active`) menggunakan nilai `$connectorWidth` yang secara proporsional mengisi track dari titik 0% hingga 100%:
* **Langkah 1 (Pesanan Dibuat)**: `0%` (garis belum terisi)
* **Langkah 2 (Verifikasi Pembayaran)**: `25%` (garis terisi menghubungkan ke Langkah 2)
* **Langkah 3 (Dikonfirmasi & Dikemas)**: `50%` (garis terisi menghubungkan ke Langkah 3)
* **Langkah 4 (Sedang Dikirim)**: `75%` (garis terisi menghubungkan ke Langkah 4)
* **Langkah 5 (Pesanan Selesai)**: `100%` (garis terisi penuh menghubungkan ke Langkah 5)

---

## 3. Rincian Modifikasi Kode

### A. File: `resources/views/Pelanggan/order_detail.blade.php`

#### 1. Penataan Card Rincian Pembayaran & Total Tagihan
```css
/* Penyesuaian padding kartu untuk menambah ruang horizontal sebesar 8px */
.summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 18px; /* Sebelumnya: 20px 22px */
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

/* Kontainer bergaya pill dengan aksen hijau lembut */
.summary-card-footer.summary-total-footer {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-radius: 8px;
    padding: 9px 12px;
    margin-top: 10px;
}

.summary-calc-row.main-total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    width: 100%;
    gap: 6px;
    border: none;
    margin: 0;
    padding: 0;
}

/* Proteksi anti-wrapping dan proporsi tipografi terkalibrasi */
.summary-calc-row.main-total span:first-child {
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.summary-calc-row.main-total span:last-child {
    color: #00b207;
    font-size: 15.5px;
    font-weight: 800;
    white-space: nowrap;
    letter-spacing: -0.2px;
}
```

Markup HTML baris total:
```blade
<div class="summary-card-footer summary-total-footer">
    <div class="summary-calc-row main-total" style="border-top: none; margin-top: 0; padding-top: 0;">
        <span>Total Tagihan:</span>
        <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
    </div>
</div>
```

#### 2. Penataan Stepper Progress Bar (Desktop & Responsif Mobile)
```css
/* Container Stepper */
.stepper-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 15px 0 10px;
    position: relative;
    padding: 0;
    width: 100%;
}

/* Pembungkus Garis Konektor Geometri 10% - 90% */
.stepper-track-wrap {
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 4px;
    z-index: 1;
}

.stepper-line-bg {
    position: absolute;
    inset: 0;
    height: 4px;
    background-color: #e2e8f0;
    border-radius: 2px;
}

.stepper-line-active {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    height: 4px;
    background-color: #00b207;
    border-radius: 2px;
    transition: width 0.4s ease;
}

/* Item Tahapan 20% seragam */
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 3;
    flex: 1 1 20%;
    max-width: 20%;
    padding: 0 4px;
}

/* Label dengan min-height dan flex centering */
.step-label {
    margin-top: 10px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    transition: color 0.3s;
    text-align: center;
    line-height: 1.35;
    min-height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Badge status mikro berbentuk pill */
.step-sub-badge {
    font-size: 10.5px;
    margin-top: 4px;
    font-weight: 600;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 50px;
    line-height: 1.3;
}
.step-sub-badge.sub-success {
    background: #e8f8ed;
    color: #00b207;
    border: 1px solid #b7ebc5;
}
.step-sub-badge.sub-warning {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.step-sub-badge.sub-muted {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
```

Responsif Mobile (`@media (max-width: 768px)`):
```css
@media (max-width: 768px) {
    .stepper-container {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 10px;
        gap: 24px;
    }
    .stepper-track-wrap {
        position: absolute;
        top: 20px;
        bottom: 20px;
        left: 31px;
        width: 4px;
        height: auto;
        right: auto;
    }
    .stepper-line-bg {
        position: absolute;
        inset: 0;
        width: 4px;
        height: 100%;
    }
    .stepper-line-active {
        position: absolute;
        top: 0;
        left: 0;
        width: 4px !important;
        height: var(--mobile-stepper-height, 0%) !important;
        right: auto;
    }
    .step-item {
        flex-direction: row;
        width: 100%;
        max-width: 100%;
        align-items: center;
        justify-content: flex-start;
        gap: 16px;
        text-align: left;
        padding: 0;
    }
    .step-label {
        margin-top: 0;
        min-height: auto;
        text-align: left;
        justify-content: flex-start;
        font-size: 13.5px;
    }
}
```

Markup HTML Stepper:
```blade
<div class="stepper-container" style="--mobile-stepper-height: {{ $connectorWidth }}%;">
    <div class="stepper-track-wrap">
        <div class="stepper-line-bg"></div>
        <div class="stepper-line-active" style="width: {{ $connectorWidth }}%;"></div>
    </div>
    
    <!-- Step 1: Pesanan Dibuat -->
    <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">
            @if($statusStep > 1)
                <i class="fa-solid fa-check"></i>
            @else
                01
            @endif
        </div>
        <div class="step-label">Pesanan Dibuat</div>
    </div>
    
    <!-- Step 2: Verifikasi Pembayaran -->
    <div class="step-item {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">
            @if($statusStep >= 2)
                <i class="fa-solid fa-check"></i>
            @else
                02
            @endif
        </div>
        <div class="step-label">Verifikasi Pembayaran</div>
        @if($isCod)
            <span class="step-sub-badge sub-muted">Bayar saat diterima</span>
        @elseif($isPaid)
            <span class="step-sub-badge sub-success"><i class="fa-solid fa-circle-check me-1"></i>Lunas</span>
        @else
            <span class="step-sub-badge sub-warning"><i class="fa-regular fa-clock me-1"></i>Menunggu</span>
        @endif
    </div>
    
    <!-- Step 3: Dikonfirmasi & Dikemas -->
    <div class="step-item {{ $statusStep >= 3 ? ($statusStep > 3 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">
            @if($statusStep > 3)
                <i class="fa-solid fa-check"></i>
            @else
                03
            @endif
        </div>
        <div class="step-label">Dikonfirmasi &amp; Dikemas</div>
        @if($statusStep >= 3)
            <span class="step-sub-badge sub-success">Dapur Zyh Corner</span>
        @elseif($statusStep == 2)
            <span class="step-sub-badge sub-warning"><i class="fa-solid fa-clock-rotate-left me-1"></i>Menunggu Konfirmasi</span>
        @endif
    </div>
    
    <!-- Step 4: Sedang Dikirim -->
    <div class="step-item {{ $statusStep >= 4 ? ($statusStep > 4 ? 'completed' : 'active') : '' }}">
        <div class="step-circle">
            @if($statusStep > 4)
                <i class="fa-solid fa-check"></i>
            @else
                04
            @endif
        </div>
        <div class="step-label">Sedang Dikirim</div>
    </div>
    
    <!-- Step 5: Pesanan Selesai -->
    <div class="step-item {{ $statusStep >= 5 ? 'completed' : '' }}">
        <div class="step-circle">
            @if($statusStep >= 5)
                <i class="fa-solid fa-check"></i>
            @else
                05
            @endif
        </div>
        <div class="step-label">Pesanan Selesai</div>
    </div>
</div>
```

---

## 4. Hasil Verifikasi & Uji Kualitas

### A. Pengujian Unit & Fitur Otomatis (*Automated Feature Tests*)
1. Pengujian spesifik layout detail pesanan:
   ```bash
   php artisan test --filter=OrderDetailLayoutTest
   ```
   * **Hasil**: `6 passed (45 assertions)` dalam 1.59s.
   * **Validasi**:
     - `customer can view order detail with refined layout` $\rightarrow$ **PASS**
     - `canceled order renders canceled status and banner` $\rightarrow$ **PASS**
     - `customer cannot view other customers order` $\rightarrow$ **PASS**
     - `order detail renders biteship modal and copyable resi` $\rightarrow$ **PASS**
     - `order detail renders dynamic store whatsapp and shipping cost` $\rightarrow$ **PASS**
     - `customer cancelling order persists cancel reason` $\rightarrow$ **PASS**

2. Pengujian regresi penuh seluruh modul aplikasi:
   ```bash
   php artisan test
   ```
   * **Hasil**: `129 passed (637 assertions)` dalam 24.87s. Tingkat kelulusan **100% Green**.

### B. Kompilasi Aset Frontend (*Vite Build*)
Kompilasi bundel frontend dijalankan untuk menjamin integritas aset produksi:
```bash
npm run build
```
* **Hasil**: Sukses dikompilasi dalam 558ms tanpa error sintaks atau konflik CSS:
  - `public/build/assets/pelanggan_pages-BvTzmTLP.css`: `132.83 kB`
  - `public/build/assets/pelanggan_pages-CTr8yAde.js`: `66.25 kB`
  - `public/build/assets/app-DRPeDlVi.js`: `37.25 kB`
  - `public/build/manifest.json`: `1.15 kB`

---

## 5. Ringkasan Perubahan & Keuntungan Arsitektural

| Aspek | Kondisi Sebelum | Kondisi Sesudah | Keuntungan Teknis |
| :--- | :--- | :--- | :--- |
| **Garis Stepper (Overflow)** | Menggunakan `left: 60px; right: 60px;` statis yang meluap melewati lingkaran | Terbungkus `.stepper-track-wrap` dengan `left: 10%; right: 10%;` | Garis berlabuh persis pada titik pusat lingkaran langkah 1 dan 5 tanpa ada overflow |
| **Garis Stepper (Vertikal)** | `top: 22px` dengan tinggi 4px (pusat 24px, meleset 2px) | `top: 20px` dengan tinggi 4px (pusat 22px) | Garis terpusat secara geometris membelah lingkaran 44px |
| **Baseline Label Stepper** | Tinggi tidak terikat, label 1 baris dan 2 baris tidak sejajar | `min-height: 34px` dengan `display: flex; align-items: center;` | Baseline teks seluruh langkah sejajar secara horizontal |
| **Sub-badge Status Stepper** | Warna teks langsung tanpa styling pill terpadu | Kelas `.sub-success`, `.sub-warning`, `.sub-muted` dengan background lembut | Estetika pill mikro modern dengan kontras warna yang nyaman |
| **Teks Total Tagihan** | Rentan terpotong dan patah baris (misal: "Total" di baris 1, "Tagihan:" di baris 2) | Terproteksi `white-space: nowrap;` dalam `.summary-total-footer` | Teks dan nominal tidak pernah terpecah di berbagai resolusi layar |
| **Padding Card Pembayaran** | `padding: 20px 22px;` | `padding: 20px 18px;` | Tambahan ruang 8px memberikan fleksibilitas ekstra untuk nominal digit panjang |
