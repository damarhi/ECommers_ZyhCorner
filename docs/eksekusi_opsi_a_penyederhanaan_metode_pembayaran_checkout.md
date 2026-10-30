# Laporan Eksekusi Opsi A: Penyederhanaan Metode Pembayaran Checkout & Penguncian E-Wallet

**Tanggal:** 30 Oktober 2026  
**Status:** Selesai (100% Green / Zero Regression)  
**Tautan Terkait:** `resources/views/Pelanggan/checkout.blade.php`, `resources/js/pelanggan_pages.js`, `resources/css/pelanggan_custom.css`, `tests/Feature/CheckoutDesignTest.php`

---

## 1. Latar Belakang & Masalah

Pada formulir ringkasan pesanan halaman Checkout (`/checkout`), bagian **Metode Pembayaran** sebelumnya memiliki kompleksitas visual dan teknis:
1. **Dropdown Sub-Chips Bank Rekening (`#transferSubOptions`)**:
   Saat opsi *Transfer Bank & Virtual Account* dipilih, muncul sub-pilihan chip bank (BRI, BCA, Mandiri) di dalam kartu. Hal ini menciptakan kesan hierarki berulang (*cards inside cards*) dan membingungkan pelanggan, karena sistem checkout menggunakan verifikasi rekening dinamis toko / Midtrans payment gateway secara terpusat tanpa perlu pelanggan memilih jenis bank di awal form checkout.
2. **Opsi E-Wallet & QRIS Digital yang Belum Siap Digunakan**:
   Opsi e-wallet menampilkan sub-chips DANA dan QRIS yang bisa dipilih, padahal integrasi gateway dompet digital belum dibuka sepenuhnya untuk transaksi langsung di checkout.

### Solusi: Opsi A
- **Menghilangkan sub-pilihan rekening bank (`#transferSubOptions` & radio `name="bankSelect"`)**: Menjadikan kartu *Transfer Bank & Virtual Account* sebagai opsi tunggal yang bersih, elegan, dan langsung dapat dipilih tanpa dropdown tambahan.
- **Mengunci Opsi E-Wallet & QRIS Digital (`disabled` / locked)**:
  - Radio button diberi atribut `disabled`.
  - Kontainer kartu diberi kelas CSS `.payment-option-item.is-locked` dengan latar abu-abu lembut (`#f8fafc`), batas `#e2e8f0`, visual pudar (`opacity: 0.6`), dan kursor non-aktif (`cursor: not-allowed`).
  - Diberikan badge penanda status abu-abu netral: `<i class="fa-solid fa-lock"></i> Segera Hadir`.
  - Sub-chips `#ewalletSubOptions` dan radio `name="walletSelect"` dihilangkan sepenuhnya.
  - Fungsi JavaScript `selectPaymentMethod` memiliki guard untuk menolak event klik pada opsi e-wallet.

---

## 2. Rincian Perubahan Kode

### A. View Blade Checkout (`resources/views/Pelanggan/checkout.blade.php`)

#### 1. Penambahan Scoped Stylesheet untuk State Terkunci
Pada blok `@section('styles')`, ditambahkan aturan CSS spesifik untuk elemen `.payment-option-item.is-locked`:

```css
    .payment-option-item.is-locked {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    .payment-option-item.is-locked * {
        cursor: not-allowed !important;
    }
    .payment-option-item.is-locked:hover {
        border-color: #e2e8f0 !important;
        background-color: #f8fafc !important;
        box-shadow: none !important;
    }
```

#### 2. Pembaruan Markup Metode Pembayaran
Menghapus `#transferSubOptions` dan `#ewalletSubOptions`, serta mengunci opsi E-Wallet:

```html
                        <!-- Transfer Bank -->
                        <div class="payment-option-item" onclick="selectPaymentMethod('transfer')">
                            <div class="payment-option-header">
                                <input type="radio" id="payTransfer" name="paymentGroup" value="Transfer">
                                <label for="payTransfer" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                    <div>
                                        <div class="payment-title">Transfer Bank & Virtual Account</div>
                                        <div class="payment-subtitle">Transfer manual atau Virtual Account terverifikasi</div>
                                    </div>
                                    <div class="payment-logos-preview">
                                        <span class="bank-pill">BCA</span>
                                        <span class="bank-pill">BRI</span>
                                        <span class="bank-pill">Mandiri</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- E-Wallet (Terkunci / Tidak Aktif) -->
                        <div class="payment-option-item is-locked">
                            <div class="payment-option-header">
                                <input type="radio" id="payEwallet" name="paymentGroup" value="E-Wallet" disabled>
                                <label for="payEwallet" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                    <div>
                                        <div class="payment-title" style="color: #64748b;">E-Wallet & QRIS Digital</div>
                                        <div class="payment-subtitle" style="color: #94a3b8; font-size: 11.5px;">Layanan dompet digital sedang dalam integrasi sistem</div>
                                    </div>
                                    <span class="payment-badge-tag locked" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-lock"></i> Segera Hadir
                                    </span>
                                </label>
                            </div>
                        </div>
```

#### 3. Logika Inline `window.selectPaymentMethod`
Ditambahkan helper inline pada blok `<script>` `checkout.blade.php`:

```javascript
    window.selectPaymentMethod = function(method) {
        if (method === 'ewallet') return;
        document.querySelectorAll('.payment-option-item:not(.is-locked)').forEach(el => el.classList.remove('active'));
        const codRadio = document.getElementById('payCod');
        const transferRadio = document.getElementById('payTransfer');
        if (method === 'cod' && codRadio) {
            codRadio.checked = true;
            const parent = codRadio.closest('.payment-option-item');
            if (parent) parent.classList.add('active');
        } else if (method === 'transfer' && transferRadio) {
            transferRadio.checked = true;
            const parent = transferRadio.closest('.payment-option-item');
            if (parent) parent.classList.add('active');
        }
    };
```

---

### B. Stylesheet Pelanggan (`resources/css/pelanggan_custom.css`)

Menambahkan kelas `.payment-option-item.is-locked` dan `.payment-badge-tag.locked` pada CSS utama pelanggan:

```css
    .payment-option-item.is-locked {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    .payment-option-item.is-locked * {
        cursor: not-allowed !important;
    }
    .payment-option-item.is-locked:hover {
        border-color: #e2e8f0 !important;
        background-color: #f8fafc !important;
        box-shadow: none !important;
    }
    .payment-badge-tag.locked {
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
    }
```

---

### C. Modul Interaktivitas JavaScript (`resources/js/pelanggan_pages.js`)

Menambahkan guard clause `if (method === 'ewallet') return;` dan menyelaraskan selektor agar kartu yang terkunci tidak terpengaruh toggle active:

```javascript
window.selectPaymentMethod = function(method) {
    if (method === 'ewallet') return;

    // Toggle active style
    document.querySelectorAll('.payment-option-item:not(.is-locked)').forEach(item => item.classList.remove('active'));
    
    const codRadio = document.getElementById('payCod');
    const transferRadio = document.getElementById('payTransfer');

    const transferSub = document.getElementById('transferSubOptions');
    const ewalletSub = document.getElementById('ewalletSubOptions');

    if (transferSub) transferSub.style.display = 'none';
    if (ewalletSub) ewalletSub.style.display = 'none';

    if (method === 'cod' && codRadio) {
        codRadio.checked = true;
        const parent = codRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
    } else if (method === 'transfer' && transferRadio) {
        transferRadio.checked = true;
        const parent = transferRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
        if (transferSub) transferSub.style.display = 'block';
    }
};
```

---

### D. Penyesuaian Feature Test (`tests/Feature/CheckoutDesignTest.php`)

Menyesuaikan assertion kontrak checkout untuk memastikan sub-chips tidak lagi dimuat di DOM, opsi e-wallet berstatus `disabled`, dan badge `Segera Hadir` muncul:

```php
        $response->assertSee('id="payCod"', false);
        $response->assertSee('id="payTransfer"', false);
        $response->assertSee('id="payEwallet"', false);
        $response->assertSee('disabled', false);
        $response->assertSee('Segera Hadir', false);
        $response->assertDontSee('id="transferSubOptions"', false);
        $response->assertDontSee('id="ewalletSubOptions"', false);
        $response->assertSee('btn-place-order', false);
        $response->assertSee('name="paymentGroup"', false);
        $response->assertSee('name="shippingGroup"', false);
        $response->assertDontSee('name="bankSelect"', false);
        $response->assertDontSee('name="walletSelect"', false);
```

---

## 3. Kompilasi Aset Frontend

Eksekusi `npm run build` sukses dilakukan:
```bash
vite v8.0.9 building client environment for production...
public/build/assets/pelanggan_pages-Ca1jM6zM.css  142.23 kB │ gzip: 24.49 kB
public/build/assets/pelanggan_pages-DyNXIQZ_.js    70.47 kB │ gzip: 14.94 kB
✓ built in 538ms
```

---

## 4. Hasil Verifikasi

### A. Verifikasi Interaktif Headless Chromium
Pengujian langsung terhadap antarmuka halaman checkout menghasilkan data state berikut:

1. **State Awal (Default)**:
   - `codChecked: true`, `codActive: true` (COD aktif sebagai opsi pembayaran utama).
   - `transferChecked: false`, `transferActive: false`.
   - `ewalletChecked: false`, `ewalletDisabled: true`.
   - `hasTransferSub: false` (sub-options bank berhasil dihilangkan).
   - `hasEwalletSub: false` (sub-options e-wallet berhasil dihilangkan).
   - `ewalletLockedClass: true` (kelas `.is-locked` diterapkan dengan visual muted).
   - `lockedBadgeText: "Segera Hadir"`.

2. **State Setelah Klik Transfer Bank**:
   - `codChecked: false`, `codActive: false`.
   - `transferChecked: true`, `transferActive: true` (border hijau `#00b207` dan radio aktif).

3. **State Saat Mencoba Klik E-Wallet yang Terkunci**:
   - `transferChecked: true`, `transferActive: true` (pilihan tetap berada di Transfer Bank).
   - `ewalletChecked: false`, `ewalletActive: false` (klik diblokir secara total baik dari level radio input `disabled` maupun fungsi JavaScript guard).

### B. Verifikasi PHPUnit Test Suite
Menjalankan seluruh 198 pengujian fitur dan unit:
```
Tests:    198 passed (1065 assertions)
Duration: 42.36s
Status:   100% Green (Zero Regression)
```

---

## 5. Kesimpulan

Opsi A telah berhasil diimplementasikan secara komprehensif:
1. Kartu metode pembayaran checkout kini tampil lebih bersih, flat, dan ringkas tanpa kartu bersarang (*no cards inside cards*).
2. Pilihan rekening bank dihilangkan tanpa mengganggu alur transfer bank / Midtrans gateway.
3. Opsi E-Wallet & QRIS Digital terkunci dengan status "Segera Hadir" yang informatif dan ramah pengguna.
4. Seluruh fungsionalitas dan test suite aplikasi terjaga 100% stabil.
