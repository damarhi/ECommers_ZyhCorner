# Dokumentasi Perubahan Kode: Redesain Banner Discount Widget (Opsi A — Light Artisanal Ecobazar)

## 1. Latar Belakang & Audit Awal

Pada halaman beranda (`/`), kolom ke-4 dari widget produk (*Hot Deals*, *Best Seller*, *Top Rated*, dan *Promotional Banner*) sebelumnya memiliki beberapa kelemahan visual:
1. **Latar Belakang Terlalu Gelap & Muram**: Menggunakan gradien cokelat kopi pekat (`rgba(38, 20, 12, 0.78)` ke `rgba(18, 10, 6, 0.94)`) yang membungkam foto kue di belakangnya dan terlihat tidak seimbang di sebelah 3 kolom produk yang berlatar putih bersih (`#ffffff`).
2. **Lencana Terlalu Panjang & Kaku**: Teks oranye tebal `"PROMO DISKON TERBATAS AKHIR BULAN"` memakan ruang vertikal secara berlebihan.
3. **Kontras Warna Bertabrakan**: Judul berteks hijau terang di atas latar gelap pekat menciptakan kesan kusam (*dull*) dan sulit discan secara instan.
4. **Tombol CTA Terputus**: Tombol putih polos dengan teks hitam terpisah dari palet identitas Ecobazar.

### Arah Solusi (Opsi A — Light Artisanal Ecobazar)
Transformasi menjadi kartu promosi bertema terang yang elegan, minimalis, dan harmonis:
- **Latar Belakang**: Soft mint cream (`linear-gradient(180deg, #f5f8f5 0%, #edf2ee 100%)`) dengan border halus `#e2eae3` dan radius `18px`.
- **Lencana Ringkas**: Kapsul amber lembut (`rgba(255, 138, 0, 0.12)`) dengan teks `#d97706`.
- **Hierarki Tipografi**: Judul deep charcoal `#1a1a1a` yang tajam, didampingi baris diskon tegas (*statement percentage*) berwarna hijau `#00b207`.
- **Foto Produk Floating**: Gambar produk pastry tampil mengambang natural di tengah dengan efek *drop-shadow* lembut, tanpa ditutupi lapisan gelap.
- **Tombol Aksi Ecobazar**: Tombol kapsul hijau `#00b207` dengan teks putih dan micro-interaction panah geser.

---

## 2. File yang Diubah

### A. Blade View: `resources/views/Pelanggan/home.blade.php`

#### 1. Pembaruan Markup HTML Kolom Promo
```blade
<!-- 4. Promotional Banner (Option A: Light Artisanal Ecobazar) -->
<div>
    <div class="promo-banner">
        <div class="promo-banner-content">
            <div>
                <span class="promo-tag-pill">
                    <i class="fa-solid fa-tag"></i> {{ isset($popupBanner) && $popupBanner && $popupBanner->subjudul ? $popupBanner->subjudul : 'SPECIAL PROMO' }}
                </span>
                <h2>{{ isset($popupBanner) && $popupBanner && $popupBanner->judul_banner ? $popupBanner->judul_banner : 'Summer Sale' }}</h2>
                <div class="promo-discount-callout">
                    <span>Hemat hingga</span>
                    <span class="promo-discount-val">75% OFF</span>
                </div>
                <p class="promo-desc">Diskon spesial aneka kreasi kue & pastry lezat pilihan terbaik Zyh Corner.</p>
            </div>

            @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner))
                <div class="promo-img-wrap">
                    <img src="{{ asset('storage/' . $popupBanner->gambar_banner) }}" alt="{{ $popupBanner->judul_banner ?? 'Summer Sale' }}" class="promo-img">
                </div>
            @endif

            <div>
                <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-btn">
                    Shop Now <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
```

#### 2. Pembaruan CSS Inline `<style>`
```css
/* Promotional / Discount Banner (Option A: Light Artisanal Ecobazar) */
.promo-banner {
    border-radius: 18px;
    padding: 30px 22px;
    text-align: center;
    background: linear-gradient(180deg, #f5f8f5 0%, #edf2ee 100%);
    border: 1px solid #e2eae3;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    height: 100%;
    min-height: 400px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.promo-banner:hover {
    transform: translateY(-4px);
    border-color: #cde0d1;
    box-shadow: 0 12px 28px rgba(0, 178, 7, 0.09);
}
.promo-banner-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    height: 100%;
    justify-content: space-between;
}
.promo-tag-pill {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 10px;
    color: #d97706;
    background: rgba(255, 138, 0, 0.12);
    border: 1px solid rgba(217, 119, 6, 0.22);
    padding: 5px 14px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.promo-banner h2 {
    font-size: 22px;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 6px;
    line-height: 1.22;
    letter-spacing: -0.3px;
    text-shadow: none;
}
.promo-discount-callout {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #555555;
}
.promo-discount-val {
    font-size: 20px;
    font-weight: 800;
    color: #00b207;
    letter-spacing: -0.3px;
}
.promo-desc {
    font-size: 12.5px;
    color: #666666;
    line-height: 1.45;
    margin-bottom: 14px;
    max-width: 220px;
}
.promo-img-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 6px 0 16px 0;
    flex: 1;
}
.promo-img {
    max-height: 130px;
    max-width: 100%;
    object-fit: contain;
    filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.10));
    transition: transform 0.35s ease;
}
.promo-banner:hover .promo-img {
    transform: scale(1.06) translateY(-2px);
}
.promo-btn {
    background-color: #00b207;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 13.5px;
    padding: 11px 26px;
    border-radius: 50px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(0, 178, 7, 0.25);
}
.promo-btn:hover {
    background-color: #009906;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 178, 7, 0.35);
    color: #ffffff !important;
}
.promo-btn i {
    transition: transform 0.25s ease;
}
.promo-btn:hover i {
    transform: translateX(4px);
}
```

---

### B. Stylesheet: `public/css/home.css`
- Menyelaraskan seluruh kelas `.promo-banner`, `.promo-tag-pill`, `.promo-banner h2`, `.promo-discount-callout`, `.promo-desc`, `.promo-img-wrap`, `.promo-img`, dan `.promo-btn` dengan spesifikasi Opsi A di atas.

---

## 3. Hasil Pengujian & Verifikasi

- **Status HTTP**: `200 OK` (Ukuran DOM: ~125.4 KB).
- **Verifikasi Kontras & Visual**:
  - Gradien gelap pekat lama (`rgba(38, 20, 12)`) telah **dihilangkan 100%**.
  - Latar belakang baru bergaya lembut `#edf2ee` & `#f5f8f5` aktif.
  - Gambar produk pastry (`SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png`) tampil jernih di tengah banner dengan efek floating drop-shadow.
  - Tautan tombol mengarah ke rute `/shop` dengan visual hijau `#00b207`.
