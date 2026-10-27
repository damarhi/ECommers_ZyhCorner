# Dokumentasi Perubahan Desain: Promo Banner Gambar Full-Bleed & Gradasi Putih

Dokumentasi ini mencatat implementasi penyempurnaan desain pada komponen **Promo Banner (Figma Replica / 4th Column)** di halaman beranda (`resources/views/Pelanggan/home.blade.php`) serta modal pratinjau admin (`resources/views/Admin/kelola_carousel/banners.blade.php`).

Perubahan ini membuat gambar produk **memenuhi seluruh kartu banner (kiri, kanan, atas, bawah / *full-bleed edge-to-edge cover*)**, sekaligus mempertahankan dan mempertegas **lapisan gradasi putih (*white radiance multi-stop gradient veil*)** di bagian atas untuk menjamin keterbacaan teks dan tombol CTA dengan kontras tinggi.

---

## 1. Analisis & Konsep Perubahan

### A. Kondisi Sebelumnya
Sebelumnya, elemen gambar `.promo-figma-img` berada di kontainer bawah (`.promo-figma-image-wrap`) dengan batasan `max-height: 200px` dan `object-fit: contain`. Akibatnya, gambar hanya mengisi sepertiga area bawah kartu, menyisakan ruang latar putih polos yang dominan di dua pertiga area atasnya.

### B. Konsep Solusi (*Full-Bleed Showcase Poster*)
```
┌────────────────────────────────────────────────────────┐
│  ╭──────────────────────────────────────────────────╮  │
│  │ (Image Fills 100% Card: Left, Right, Top, Bottom)│  │
│  │                                                  │  │
│  │    ░▒▓ [WHITE GRADIENT SPOTLIGHT VEIL] ▓▒░       │  │
│  │                                                  │  │
│  │         [Pill: ✨ SUMMER SALE]                   │  │
│  │                                                  │  │
│  │               75% OFF                            │  │
│  │                                                  │  │
│  │      Aneka Pastry & Cake Pilihan Terbaik         │  │
│  │                                                  │  │
│  │           [ Shop Now  → ]                        │  │
│  │                                                  │  │
│  │     ░▒▓ Smooth Dissolve into Pastry Photo ▓▒░    │  │
│  │                                                  │  │
│  │        [⏰ Limited Time Offer]                   │  │
│  │                                                  │  │
│  │           🥐 🍰 🧁 Pastry Photo Cover            │  │
│  ╰──────────────────────────────────────────────────╯  │
└────────────────────────────────────────────────────────┘
```

1. **Cover Penuh (Kiri, Kanan, Atas, Bawah)**:
   - Kontainer gambar `.promo-figma-image-wrap` dialihkan ke posisi `position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1;`.
   - Elemen gambar `.promo-figma-img` disetel `position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center bottom;`.
   - Mengikuti sudut membulat kartu secara mulus (`border-radius: 20px; overflow: hidden;`).
2. **Gradasi Putih Tetap Ada (*The White Radiance Veil*)**:
   - Pseudo-element `::before` pada kartu bertindak sebagai kanvas gradasi multi-stop 180° (`z-index: 2`):
     ```css
     background: linear-gradient(
         180deg,
         #ffffff 0%,
         rgba(255, 255, 255, 0.98) 28%,
         rgba(255, 255, 255, 0.88) 45%,
         rgba(255, 255, 255, 0.45) 66%,
         rgba(255, 255, 255, 0.10) 84%,
         rgba(255, 255, 255, 0) 100%
     );
     ```
   - Memberikan latar putih susu yang bersih dan solid bagi badge pill, angka diskon "75% OFF", tagline, dan tombol "Shop Now", lalu memudar secara organik ke bawah mengekspos foto pastry keemasan.
3. **Z-Index Layering**:
   - Layer 1 (`z-index: 1`): Gambar latar pastry cover penuh.
   - Layer 2 (`z-index: 2`): Lapisan gradasi putih multi-stop pelindung teks.
   - Layer 3 (`z-index: 3`): Teks header (badge, title, tagline, CTA) dan floating tag "Limited Time Offer".

---

## 2. Rincian Perubahan Kode Berkas

### A. `resources/views/Pelanggan/home.blade.php`

```css
/* Promo Banner (4th Column) */
/* ==========================================================================
   Ecobazar Figma Replica: Promotional / Discount Banner (Enlarged & Enhanced)
   ========================================================================== */
.promo-banner-figma,
.promo-banner {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 100%;
    min-height: 430px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    padding: 32px 22px 20px 22px;
    background: #ffffff;
    border: 1.5px solid #dbe8dd;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 4px rgba(0, 178, 7, 0.06);
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}

/* Lapisan Gradasi Putih Multi-Stop (Melindungi Keterbacaan Teks Atas) */
.promo-banner-figma::before,
.promo-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        180deg,
        #ffffff 0%,
        rgba(255, 255, 255, 0.98) 28%,
        rgba(255, 255, 255, 0.88) 45%,
        rgba(255, 255, 255, 0.45) 66%,
        rgba(255, 255, 255, 0.10) 84%,
        rgba(255, 255, 255, 0) 100%
    );
    z-index: 2;
    pointer-events: none;
}

.promo-banner-figma:hover,
.promo-banner:hover {
    transform: translateY(-4px);
    border-color: #b9dac0;
    box-shadow: 0 16px 36px rgba(0, 178, 7, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
}

.promo-figma-header {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    z-index: 3;
}

.promo-figma-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 138, 0, 0.12);
    color: #ff8a00;
    border: 1px solid rgba(255, 138, 0, 0.28);
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    box-shadow: 0 2px 6px rgba(255, 138, 0, 0.08);
}

.promo-figma-title {
    font-size: 44px;
    font-weight: 900;
    color: #00b207;
    line-height: 1.05;
    margin-bottom: 6px;
    letter-spacing: -1px;
    text-shadow: 0 2px 8px rgba(0, 178, 7, 0.10);
}

.promo-figma-tagline {
    font-size: 12.5px;
    color: #666666;
    font-weight: 500;
    margin-bottom: 16px;
    line-height: 1.35;
    max-width: 260px;
}

.promo-figma-btn,
.promo-btn {
    background: linear-gradient(135deg, #00b207 0%, #009906 100%);
    color: #ffffff !important;
    font-size: 14px;
    font-weight: 700;
    padding: 11px 28px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 6px 18px rgba(0, 178, 7, 0.32);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    border: none;
}

.promo-figma-btn:hover,
.promo-btn:hover {
    background: linear-gradient(135deg, #009e06 0%, #008505 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 178, 7, 0.42);
    color: #ffffff !important;
}

/* Kontainer Gambar Memenuhi Seluruh Card (Kiri, Kanan, Atas, Bawah) */
.promo-figma-image-wrap {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
    z-index: 1;
    display: flex;
    justify-content: center;
    align-items: flex-end;
}

.promo-floating-tag {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    top: auto;
    z-index: 3;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 178, 7, 0.22);
    color: #00b207;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.10);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    pointer-events: none;
}

/* Gambar Cover Penuh 100% */
.promo-figma-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    max-height: none;
    object-fit: cover;
    object-position: center bottom;
    display: block;
    filter: saturate(1.05) contrast(1.02);
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.promo-banner-figma:hover .promo-figma-img,
.promo-banner:hover .promo-figma-img {
    transform: scale(1.06);
}
```

---

### B. `resources/views/Admin/kelola_carousel/banners.blade.php`

Pada modal pratinjau admin posisi `popup_promo`, tata letak diselaraskan menjadi cover penuh dengan gradasi pelindung:
```blade
@elseif($b->posisi_banner === 'popup_promo')
    <div class="p-3 text-center d-flex flex-column align-items-center justify-content-between position-relative overflow-hidden"
        style="border-radius: 18px; min-height: 250px; background: #ffffff; border: 1.5px solid #dbe8dd;">
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, #ffffff 0%, rgba(255, 255, 255, 0.98) 35%, rgba(255, 255, 255, 0.5) 65%, rgba(255, 255, 255, 0) 100%); z-index: 2; pointer-events: none;"></div>
        <div class="d-flex flex-column align-items-center w-100" style="position: relative; z-index: 3;">
            <span class="badge mb-1 text-uppercase fw-bold" style="background: rgba(255, 138, 0, 0.12); color: #ff8a00; border: 1px solid rgba(255, 138, 0, 0.28); border-radius: 20px; font-size: 10px; letter-spacing: 1.4px; padding: 4px 10px;">
                <i class="mdi mdi-creation me-1"></i> {{ $b->subjudul ?? 'SUMMER SALE' }}
            </span>
            <h3 class="fw-bold mb-1" style="color: #00b207; font-size: 26px; letter-spacing: -0.5px;">{{ $b->judul_banner }}</h3>
            <span class="btn btn-sm text-white fw-bold rounded-pill px-3 shadow-sm mb-1" style="background: linear-gradient(135deg, #00b207, #009906); font-size: 11.5px;">
                Shop Now <i class="mdi mdi-arrow-right ms-1"></i>
            </span>
        </div>
        <div class="w-100 position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;">
            <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="{{ $b->judul_banner }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center bottom;">
        </div>
    </div>
```

---

## 3. Hasil Pengujian & Verifikasi

1. **Pembersihan Cache Tampilan**:
   Perintah `php artisan view:clear` dijalankan dengan status `Compiled views cleared successfully`.
2. **Kompilasi & Render View**:
   - View beranda (`PelangganController::home()`) berhasil terkompilasi dan merender 127.002 karakter HTML.
   - Aturan cover `promo-figma-img` dan lapisan gradasi multi-stop `rgba(255, 255, 255, 0.98) 28%` terverifikasi aktif pada HTML yang dihasilkan.
   - Modal admin `banners.blade.php` berhasil terkompilasi dan merender kartu promo cover penuh dengan overlay gradasi putih.
