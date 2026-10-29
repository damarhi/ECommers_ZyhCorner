# Panduan Lengkap Integrasi & Konfigurasi API Zyh Corner

Dokumen ini menjelaskan arsitektur, konfigurasi lingkungan (`.env`), alur kerja, dan penanganan kegagalan (*fault-tolerance & graceful fallback*) untuk seluruh layanan API yang terintegrasi pada sistem **Zyh Corner**.

---

## 📋 Daftar Isi
1. [Ringkasan Variabel Lingkungan (.env)](#1-ringkasan-variabel-lingkungan-env)
2. [Midtrans Payment Gateway (Snap & Core API)](#2-midtrans-payment-gateway-snap--core-api)
3. [Google OAuth 2.0 (Social Login & Registrasi Cepat)](#3-google-oauth-20-social-login--registrasi-cepat)
4. [Brevo (Sendinblue) Transactional Email API (OTP & Notifikasi)](#4-brevo-sendinblue-transactional-email-api-otp--notifikasi)
5. [Biteship Logistics API (Tarif Ongkir & Pelacakan Resi)](#5-biteship-logistics-api-tarif-ongkir--pelacakan-resi)
6. [OpenStreetMap & Leaflet Geocoding (Pemetaan Alamat)](#6-openstreetmap--leaflet-geocoding-pemetaan-alamat)
7. [Google Maps Embed API (Lokasi Fisik Toko)](#7-google-maps-embed-api-lokasi-fisik-toko)
8. [Verifikasi Pengujian & Keamanan](#8-verifikasi-pengujian--keamanan)

---

## 1. Ringkasan Variabel Lingkungan (.env)

Tambahkan blok konfigurasi berikut pada berkas `.env` aplikasi Anda:

```env
# =========================================================================
# External Third-Party APIs & Gateway Configuration
# =========================================================================

# 1. Midtrans Payment Gateway (Snap & Core API)
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=Gxxxxxxxx
MIDTRANS_IS_PRODUCTION=false

# 2. Google OAuth 2.0 (Social Login & Registrasi Cepat)
GOOGLE_CLIENT_ID=xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URL="${APP_URL}/login"

# 3. Brevo (Sendinblue) Transactional Email API (OTP & Notifikasi)
BREVO_API_KEY=xkeysib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
BREVO_SENDER_EMAIL=damarn.h46@gmail.com
BREVO_SENDER_NAME="${APP_NAME}"
KASIR_NOTIFICATION_EMAIL=qwertyuiopxaw@gmail.com

# 4. Biteship Logistics API (Kurir & Ongkir Otomatis)
BITESHIP_API_KEY=biteship_test.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
BITESHIP_ORIGIN_AREA_ID=ID_PLW_01
BITESHIP_ORIGIN_POSTAL_CODE=28654
BITESHIP_ORIGIN_LATITUDE=0.40384
BITESHIP_ORIGIN_LONGITUDE=101.85588
BITESHIP_IS_PRODUCTION=false
```

---

## 2. Midtrans Payment Gateway (Snap & Core API)

### A. Kegunaan
* Pembuatan token transaksi popup pembayaran Snap (`Snap Token`).
* Pembayaran digital otomatis: Transfer Bank (BCA, BNI, BRI, Mandiri, Permata), E-Wallet (GoPay, ShopeePay), dan QRIS.
* Webhook sinkronisasi status transaksi otomatis ke pesanan lunas (`Paid`).

### B. Titik Kode Integrasi
* **Konfigurasi:** `config/services.php` (`services.midtrans`)
* **Controller:** `app/Http/Controllers/Pelanggan/PembayaranController.php`
* **Frontend Snap SDK:**
  - `resources/views/Pelanggan/checkout.blade.php`
  - `resources/views/Pelanggan/order_detail.blade.php`
  - `resources/views/Pelanggan/order_history.blade.php`
  - `resources/views/Pelanggan/order_success.blade.php`
* **Routes:**
  - `GET /order-history/{id}/pay` (`order.pay`)
  - `GET /order-history/{id}/check-payment` (`order.checkPayment`)
  - `POST /midtrans-callback` (`midtrans.callback`)

### C. Cara Mendapatkan Kredensial
1. Masuk ke [Midtrans Dashboard](https://dashboard.midtrans.com).
2. Pilih mode **Sandbox** (lingkungan uji coba) atau **Production**.
3. Navigasi ke **Settings** > **Access Keys**:
   - Salin **Merchant ID**, **Client Key**, dan **Server Key**.
4. Navigasi ke **Settings** > **Configuration**:
   - Isi **Payment Notification URL** dengan: `https://domain-anda.com/midtrans-callback`

### D. Fitur Keamanan & Graceful Handling
* **Validasi Signature SHA-512:** Callback dicek integritasnya menggunakan formula `hash("sha512", order_id + status_code + gross_amount + serverKey)`.
* **Proteksi Error Frontend:** Jika kunci belum diatur, sistem menampilkan notifikasi ramah tanpa menyebabkan JavaScript crash (`typeof snap === 'undefined'`).
* **Mode Mock Pengujian Lokal:** Jika `MIDTRANS_SERVER_KEY` diset ke `mock` atau `sandbox-demo`, pengembang dapat melakukan simulasi checkout lunas tanpa koneksi internet.

---

## 3. Google OAuth 2.0 (Social Login & Registrasi Cepat)

### A. Kegunaan
* Masuk ke akun (*Sign-in*) dengan satu klik menggunakan akun Google.
* Pelanggan baru otomatis didaftarkan dan diarahkan ke verifikasi OTP nomor/email.

### B. Titik Kode Integrasi
* **Konfigurasi:** `config/services.php` (`services.google`)
* **Controller:** `app/Http/Controllers/AuthController.php` (`redirectToGoogle`, `handleGoogleCallback`)
* **Routes:**
  - `GET /auth/google` (`login.google`)
  - `GET /auth/google/callback` (`login.google.callback`)
  - `GET /login?code=...` (fallback route standar)

### C. Cara Mendapatkan Kredensial
1. Buka [Google Cloud Console](https://console.cloud.google.com).
2. Buat proyek baru atau pilih proyek yang sudah ada.
3. Masuk ke **APIs & Services** > **Credentials** > **Create Credentials** > **OAuth client ID**.
4. Pilih **Web application**.
5. Tambahkan **Authorized redirect URIs**:
   - `http://localhost:8000/login`
   - `http://localhost:8000/auth/google/callback`
   - `https://zyhcorner.com/login`
   - `https://zyhcorner.com/auth/google/callback`
6. Salin **Client ID** dan **Client Secret** ke file `.env`.

---

## 4. Brevo (Sendinblue) Transactional Email API (OTP & Notifikasi)

### A. Kegunaan
* Mengirim email transaksional via protokol HTTP POST port 443 (melewati limitasi blokir port SMTP 25/465/587 pada hosting shared/VPS).
* Pengiriman 6-digit OTP verifikasi registrasi dan Google login.
* Notifikasi email real-time untuk kasir (pesanan baru masuk, pengajuan retur, produk kadaluarsa).
* Notifikasi pesan kontak baru dari formulir hubungi kami ke administrator.

### B. Titik Kode Integrasi
* **Konfigurasi:** `config/services.php` (`services.brevo`)
* **Layanan:** `app/Services/BrevoMailService.php`, `app/Services/NotificationService.php`
* **Endpoint Brevo:** `https://api.brevo.com/v3/smtp/email`

### C. Cara Mendapatkan Kredensial
1. Masuk ke [Brevo Dashboard](https://app.brevo.com).
2. Buka menu akun di pojok kanan atas > **SMTP & API** > tab **API Keys**.
3. Klik **Generate a new API key** (v3), salin nilainya ke `BREVO_API_KEY`.
4. Pastikan email pengirim (`BREVO_SENDER_EMAIL`) telah diverifikasi di menu **Senders & IP**.

---

## 5. Biteship Logistics API (Tarif Ongkir & Pelacakan Resi)

### A. Kegunaan
* Pencarian dinamis nama kelurahan/kecamatan/kota di seluruh Indonesia (*Area Autocomplete*).
* Perhitungan tarif ongkir kurir terpadu (Instant: GoSend/Grab/Maxim, Same Day: Paxel, Reguler: JNE/SiCepat/J&T).
* Pelacakan live status resi kurir melalui modal tracking.

### B. Titik Kode Integrasi
* **Konfigurasi:** `config/services.php` (`services.biteship`)
* **Layanan Backend:** `app/Services/BiteshipService.php`
* **Controller:** `app/Http/Controllers/Pelanggan/CartCheckoutController.php` (`biteshipAreas`)
* **Route:** `GET /biteship/areas?search={keyword}`
* **Frontend Autocomplete:**
  - `resources/views/Pelanggan/checkout.blade.php`
  - `resources/js/dashboard.js` (`bindAreaAutocomplete`)

### C. Cara Mendapatkan Kredensial
1. Daftar di [Biteship Dashboard](https://biteship.com).
2. Masuk ke menu **Integrasi API** / **Developer**.
3. Salin API Key ke `BITESHIP_API_KEY`.

### D. Mekanisme Graceful Fallback
Jika API Key belum diisi atau koneksi internet Biteship sedang lambat:
* Pencarian area otomatis menggunakan cache lokal 17 area operasional Pelalawan & Riau (`Pangkalan Kerinci Kota, Barat, Timur, Sorek, Sukajadi, Tampan, dll.`).
* Tarif ongkir menggunakan skema flat tier resmi toko (Instant Rp 20.000, Same Day Rp 15.000, Reguler Rp 12.000, Ambil di Toko Rp 0).

---

## 6. OpenStreetMap & Leaflet Geocoding (Pemetaan Alamat)

### A. Kegunaan
* Memilih titik lokasi pengantaran pesanan secara visual dengan pin interaktif pada peta.
* Menyimpan koordinat garis lintang (*latitude*) dan garis bujur (*longitude*) untuk akurasi kurir.

### B. Titik Kode Integrasi
* **Penyedia Peta:** OpenStreetMap Tiles (`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`)
* **Koordinat Default Toko:** `0.40384, 101.85588` (Jl. Pemda, Pangkalan Kerinci Kota, Pelalawan, Riau)
* **Frontend Script:** `resources/views/Pelanggan/checkout.blade.php`, `resources/js/dashboard.js`

---

## 7. Google Maps Embed API (Lokasi Fisik Toko)

### A. Kegunaan
* Menampilkan peta interaktif toko Zyh Corner pada halaman **Hubungi Kami** (*Contact Us*).

### B. Titik Kode Integrasi
* **View:** `resources/views/Pelanggan/contact.blade.php`
* **Google Place ID:** `0x31d5c5c32fbe821f:0xcc0417290c322aec`
* **Alamat:** Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654.

---

## 8. Verifikasi Pengujian & Keamanan

Seluruh integrasi API di atas telah diuji secara komprehensif menggunakan PHPUnit:

```bash
php artisan test
```

**Hasil Pengujian:**
* **Total Pengujian:** 179 Test Cases
* **Total Assertions:** 946 Assertions
* **Status:** 100% Pass (Semua Hijau)
* **Fitur Terlindungi:** Tidak ada kredensial sensitif yang dibocorkan ke commit Git publik (seluruh kredensial menggunakan template `.env.example`).
