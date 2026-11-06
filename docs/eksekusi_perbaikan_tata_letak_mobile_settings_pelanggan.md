# Laporan Eksekusi Perbaikan Tata Letak Mobile Halaman Pengaturan Akun (`/settings`)

**Tanggal Eksekusi:** 6 November 2026  
**Standar Kualitas:** `/skill:frontend-design-direction` (Evidence Contract, Content States, Rendered DOM Geometry Gate)  
**Cakupan Viewport:** Mobile (`<= 768px`: `340×740`, `375×812`, `412×915`) — Tampilan Website/Desktop (`> 768px` / `1440×900`) tetap **100% utuh tanpa perubahan**.

---

## 1. Latar Belakang & Permasalahan Geometri Sebelum Perbaikan

Berdasarkan audit visual pada ketiga *screenshot* pengguna (`local://image-91c7affb38bbbd33.webp`, `local://image-d3d9264f245aaddf.png`, `local://image-bbc12576ad9048c0.png`) dan pengukuran langsung **Headless Chrome CDP (`375×812`)**, ditemukan 5 masalah utama pada tata letak mobile halaman **Pengaturan Akun (`resources/views/Pelanggan/settings.blade.php`)**:

1. **Overflow Horizontal pada Segmented Tabs Bar (`.settings-tabs-wrapper`)**:
   - `.settings-tab-btn` memiliki `min-width: 140px; white-space: nowrap;`.
   - Pada layar `375px` (`clientWidth: 349px`), total lebar 3 tombol mencapai `scrollWidth: 449px` (**overflow `100px`**).
   - Tab ke-3 (**"Keamanan & Sandi"**) terpotong keluar layar (`right: 455px`) tanpa indikator *scroll*, memicu *Tab Blindness*.
2. **Inversi Hierarki Visual pada Tab 1 (`#tabContentProfile`)**:
   - Struktur Bootstrap `.col-md-7` (Form) dan `.col-md-5` (Avatar) menumpuk area foto profil (`top: 799px`) **di bawah** tombol submit *"Simpan Perubahan"* (`top: 715px`).
   - Tombol *"Simpan Perubahan"* hanya selebar `210px` (*inline*), menyisakan ruang kosong di kanan.
3. **Topbar Kartu Alamat Terpecah Menjadi 4 Baris pada Tab 2 (`#tabContentAddress`)**:
   - `.address-card-topbar` memuat 3 badge (`Alamat Rumah`, `Alamat Utama (Default)`, `Titik GPS Aktif`) dan tombol `Edit Alamat` yang membungkus (*wrap*) menjadi **4 baris vertikal** dengan tinggi mencapai **148px**.
4. **Pergeseran Horizontal `8px` Saat Klik Tab Ke-3 (`#tabContentPassword`)**:
   - `.dashboard-wrapper > .row` memiliki margin negatif Bootstrap (`margin-left: -15px; margin-right: -15px;`) di dalam kontainer dengan `padding: 0 12px`, sehingga saat tab ke-3 diklik, halaman bergeser `8px` ke kiri (`left: 12px` menjadi `left: 4px`).
5. **Tombol Aksi Belum *Full-Width* pada Tab 1 & Tab 3**:
   - Tombol `.btn-save-settings` (`Simpan Perubahan` dan `Perbarui Kata Sandi`) masih berformat *inline* (`width: 210px`–`214px`) sehingga kurang ergonomis untuk jangkauan ibu jari (*thumb zone*) di perangkat mobile.

---

## 2. Daftar File yang Diubah

1. `resources/views/Pelanggan/settings.blade.php`
2. `resources/css/pelanggan_pages.css`
3. `resources/css/pelanggan_custom.css`
4. `docs/evidence/settings-evidence.json` *(Beserta 4 artefak screenshot bukti render)*

---

## 3. Rincian Kode yang Diubah

### A. File: `resources/views/Pelanggan/settings.blade.php`

Menambahkan *class* semantik penargetan mobile (`.settings-profile-row`, `.settings-profile-form-col`, `.settings-profile-avatar-col`, `.settings-submit-wrapper`, `.settings-address-panel-header`, `.settings-panel-header-main`) tanpa mengubah *ID* input, atribut form, maupun fungsi JavaScript legasi:

```html
<!-- Tab 1: Profil Akun (Baris 64-95) -->
<form id="formAccountSettings" onsubmit="saveAccountSettings(event)">
    <div class="row align-items-center settings-profile-row">
        <div class="col-md-7 settings-profile-form-col">
            ...
            <div class="settings-submit-wrapper" style="margin-top: 24px;">
                <button type="submit" class="btn-save-settings">
                    <i class="fa-solid fa-check"></i> Simpan Perubahan
                </button>
            </div>
        </div>

        <div class="col-md-5 settings-profile-avatar-col">
            <div class="avatar-wrapper">
                ...
            </div>
        </div>
    </div>
</form>

<!-- Tab 2: Alamat Pengiriman Header (Baris 121-134) -->
<div class="settings-panel-header settings-address-panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div class="settings-panel-header-main" style="display: flex; align-items: center; gap: 14px;">
        ...
    </div>
    <button type="button" class="btn-add-address-header" onclick="openAddAddressModal()">
        <i class="fa-solid fa-plus-circle mr-1"></i> Tambah Alamat Baru
    </button>
</div>

<!-- Tab 3: Keamanan & Sandi Submit Wrapper (Baris 330-334) -->
<div class="settings-submit-wrapper" style="margin-top: 24px;">
    <button type="submit" class="btn-save-settings">
        <i class="fa-solid fa-lock"></i> Perbarui Kata Sandi
    </button>
</div>
```

---

### B. File: `resources/css/pelanggan_pages.css` & `resources/css/pelanggan_custom.css`

Menambahkan blok `@media (max-width: 768px)` khusus halaman `/settings` untuk mengatur grid navigasi 3 kolom bebas *overflow*, urutan *Identity-First* pada Tab 1, tata letak *2-Tier Topbar* pada Tab 2, dan tombol *full-width* pada Tab 1 & Tab 3:

```css
/* Mobile Responsive Overrides for Settings Page (<= 768px) */
@media (max-width: 768px) {
    .dashboard-wrapper > .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .dashboard-wrapper > .row > .col-lg-9.col-md-8 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .settings-tabs-wrapper {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 4px !important;
        padding: 5px !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }

    .settings-tab-btn {
        min-width: 0 !important;
        width: 100% !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        padding: 8px 4px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        letter-spacing: -0.2px !important;
        text-align: center !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .settings-tab-btn i {
        font-size: 13.5px !important;
    }

    .settings-panel-header {
        padding: 14px 16px !important;
        gap: 12px !important;
    }

    .settings-panel-header-icon {
        width: 38px !important;
        height: 38px !important;
        font-size: 16px !important;
        border-radius: 9px !important;
    }

    .settings-panel-title {
        font-size: 15px !important;
    }

    .settings-panel-subtitle {
        font-size: 11.5px !important;
        line-height: 1.35 !important;
    }

    .settings-panel-body {
        padding: 16px !important;
    }

    /* Tab 1: Profile Identity First Re-Order */
    .settings-profile-row {
        display: flex !important;
        flex-direction: column !important;
    }

    .settings-profile-avatar-col {
        order: -1 !important;
        width: 100% !important;
        margin-bottom: 18px !important;
    }

    .settings-profile-form-col {
        order: 1 !important;
        width: 100% !important;
    }

    .settings-profile-avatar-col .avatar-wrapper {
        margin-top: 0 !important;
        border-top: none !important;
        border-bottom: 1px dashed #e2e8f0 !important;
        padding: 4px 0 18px 0 !important;
    }

    .settings-profile-avatar-col .avatar-circle {
        width: 100px !important;
        height: 100px !important;
        margin-bottom: 10px !important;
    }

    .settings-profile-avatar-col .btn-chose-img {
        padding: 7px 16px !important;
        font-size: 12.5px !important;
        min-height: 38px !important;
    }

    .settings-profile-avatar-col .avatar-hint {
        font-size: 11px !important;
        margin-top: 6px !important;
    }

    .settings-submit-wrapper {
        margin-top: 18px !important;
        width: 100% !important;
    }

    .btn-save-settings {
        width: 100% !important;
        min-height: 44px !important;
        justify-content: center !important;
        padding: 11px 20px !important;
    }

    /* Tab 2: Address Card 2-Tier Topbar */
    .settings-address-panel-header .btn-add-address-header {
        width: 100% !important;
        min-height: 40px !important;
        justify-content: center !important;
        margin-top: 2px !important;
    }

    .address-display-main-card {
        padding: 16px !important;
    }

    .address-card-topbar {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        row-gap: 0 !important;
        column-gap: 6px !important;
        padding-bottom: 12px !important;
        margin-bottom: 12px !important;
    }

    .address-card-badges {
        display: contents !important;
    }

    .badge-address-label {
        order: 1 !important;
        font-size: 11px !important;
        padding: 4px 10px !important;
    }

    .btn-card-edit-address {
        order: 1 !important;
        margin-left: auto !important;
        padding: 5px 12px !important;
        font-size: 12px !important;
        min-height: 32px !important;
    }

    .address-card-topbar::before {
        content: "" !important;
        order: 2 !important;
        flex: 0 0 100% !important;
        height: 8px !important;
    }

    .badge-address-default,
    .badge-address-gps,
    .badge-address-nogps {
        order: 3 !important;
        font-size: 10px !important;
        padding: 3px 6px !important;
        gap: 4px !important;
    }

    .address-card-recipient {
        flex-wrap: wrap !important;
        gap: 4px 8px !important;
        font-size: 13.5px !important;
    }

    .address-card-recipient .recipient-phone {
        font-size: 12.5px !important;
        white-space: nowrap !important;
    }

    .address-card-street {
        font-size: 13px !important;
    }

    .address-card-area {
        padding-left: 23px !important;
        font-size: 12px !important;
    }

    .extra-address-grid {
        grid-template-columns: 1fr !important;
    }

    .extra-card-actions {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 6px !important;
    }

    .extra-card-actions .btn-action-setdefault {
        flex: 1.3 !important;
        justify-content: center !important;
        min-height: 34px !important;
    }

    .extra-card-actions .btn-action-edit,
    .extra-card-actions .btn-action-delete {
        flex: 1 !important;
        justify-content: center !important;
        min-height: 34px !important;
    }

    /* Tab 3: Password Guidelines */
    .password-guideline-box {
        align-items: flex-start !important;
        padding: 10px 12px !important;
        font-size: 12px !important;
        line-height: 1.4 !important;
    }

    .password-guideline-box i {
        margin-top: 2px !important;
        flex-shrink: 0 !important;
    }
}
```

---

## 4. Perbandingan Geometri DOM Sebelum vs Sesudah (Headless Chrome CDP `375×812`)

| Metrik Geometri | Sebelum Perbaikan (`375×812`) | Sesudah Perbaikan (`375×812`) | Hasil |
| :--- | :--- | :--- | :--- |
| **`tabsWrapperScroll.overflow`** | `100px` (`scrollWidth: 449px`) | **`0px`** (`scrollWidth: 349px`) | ✅ Ketiga tab tampil penuh 100% tanpa terpotong |
| **Dimensi Tombol Tab (`.settings-tab-btn`)** | `140×40px` (Tab ke-3 keluar layar di `right: 455px`) | **`110×47px`** (`right: 128, 243, 357`) | ✅ Target sentuh ergonomis (`47px > 44px`) |
| **Tab 1: Posisi Vertikal Avatar vs Form** | Avatar di bawah tombol simpan (`top: 799px`) | **Avatar di atas form (`top: 411px`)**, Form di `top: 619px` | ✅ Hierarki *Identity-First* sesuai standar mobile |
| **Tab 1 & 3: Lebar Tombol `.btn-save-settings`** | `210px×43px` (*inline* kiri) | **`317px×44px`** (*full-width* `100%`) | ✅ Ergonomis untuk ibu jari (*thumb zone*) |
| **Tab 2: Tinggi `.address-card-topbar`** | `148px` (terpecah 4 baris vertikal) | **`77px`** (2 baris rapi: Baris 1 Label+Edit, Baris 2 Chips) | ✅ Hemat **71px** ruang vertikal kartu |
| **Tab 3: Posisi Kiri Kontainer (`tabsWrapper.left`)** | Bergeser ke `left: 4px` (bug margin `.row`) | **Stabil di `left: 12px`** (`right: 363px`) | ✅ Simetris `12px` kiri & kanan di semua tab |
| **Desktop (`1440×900`)** | `tabsWrapper: 868×54px`, 2 kolom Form + Avatar | **`tabsWrapper: 868×54px`**, 2 kolom Form + Avatar | ✅ **100% Identik Tanpa Perubahan** |

---

## 5. Hasil Pengujian Otomatis & Validasi Gate

1. **Kompilasi Aset Vite (`npm run build`)**: Sukses (`public/build/assets/pelanggan_pages-0pCzqFoF.css`).
2. **Feature Test (`php artisan test --filter=CustomerSettingsTest`)**: **6 passed (35 assertions)**.
3. **Frontend Design Direction Evidence Gate (`check-evidence.js docs/evidence/settings-evidence.json`)**: **PASS (`exitCode: 0`)**.
