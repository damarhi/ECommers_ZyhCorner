# Dokumentasi Perubahan Kode: Redesain Tata Letak Halaman Cart (Side-by-Side 2 Kolom)

**Tanggal:** 23 Oktober 2026  
**Status:** Selesai & Terverifikasi  
**Terkait:** `/cart` (`resources/views/Pelanggan/userChart.blade.php`), `resources/js/pelanggan_pages.js`, `resources/css/pelanggan_custom.css`

---

## 1. Latar Belakang & Masalah
Sebelumnya, pada halaman keranjang belanja (`/cart`), kartu tabel daftar produk mengambil lebar penuh ($970\text{px}$) di bagian atas, sedangkan kartu ringkasan belanja (*Cart Totals*) berada di bawah tabel terdorong ke sudut kanan bawah ($y = 732\text{px}$). Hal ini menyebabkan tata letak bertumpuk secara vertikal sehingga pengguna harus menggulir ke bawah (*scrolling*) hanya untuk melihat total belanja dan tombol *"Proceed to Checkout"*.

---

## 2. Arah Desain (*Frontend Design Direction*)
1. **Scanning Priority (*Above the Fold*)**: Pengguna dapat melihat daftar kue/produk yang ada di keranjang pada sisi kiri dan ringkasan biaya serta tombol *"Proceed to Checkout"* di sisi kanan secara simultan dalam satu viewport desktop.
2. **Proporsi Grid 2-Kolom Seimbang**:
   - **Kolom Kiri (Card Produk & Tabel)**: `1fr` ($\approx 748\text{px}$), memuat tabel produk responsif, gambar thumbnail kue, kuantitas stepper, subtotal item, tombol hapus, dan tombol aksi *"Lanjut Belanja"*.
   - **Kolom Kanan (Card Cart Totals)**: `380px` tetap, dilengkapi `position: sticky; top: 100px;` agar tetap terlihat saat daftar item belanja panjang.
3. **Responsivitas Mulus**:
   - **Desktop ($\ge 992\text{px}$)**: Grid 2-kolom berdampingan (`grid-template-columns: 1fr 380px`).
   - **Tablet & Mobile ($< 992\text{px}$)**: Otomatis bertumpuk 1 kolom vertikal (`grid-template-columns: 1fr`) dengan tabel produk memiliki scrollbar horizontal aman (`overflow-x: auto`).

---

## 3. Rincian Perubahan Kode

### A. Berkas `resources/css/pelanggan_custom.css`
- Memperluas `.cart-container` dari `max-width: 1000px` menjadi `max-width: 1200px` dengan padding responsif.
- Menetapkan aturan `.cart-grid, .cart-layout-grid`:
  ```css
  /* Cart Layout */
  .cart-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 50px 20px 80px;
      width: 100%;
      box-sizing: border-box;
  }
  .cart-title {
      text-align: center;
      font-size: 32px;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 40px;
  }

  .cart-grid,
  .cart-layout-grid {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 32px;
      align-items: start;
  }

  /* Left Table */
  .cart-table-wrapper {
      background: white;
      border: 1px solid #e6e6e6;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
  }

  /* Right Card: Cart Total */
  .cart-total-card,
  .cart-totals-card {
      border: 1px solid #e6e6e6;
      border-radius: 12px;
      background: white;
      padding: 28px 24px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
      position: sticky;
      top: 100px;
  }

  @media (max-width: 991px) {
      .cart-grid,
      .cart-layout-grid {
          grid-template-columns: 1fr;
          gap: 24px;
      }
  }
  ```

---

### B. Berkas `resources/js/pelanggan_pages.js`
Memperbarui fungsi `renderCartPage(container, cartItems, subtotal)` agar membungkus tabel produk dan card Cart Total ke dalam satu elemen grid `<div class="cart-layout-grid">`:

```javascript
function renderCartPage(container, cartItems, subtotal) {
    if (cartItems.length === 0) {
        container.innerHTML = `
            <div class="empty-cart-page" style="text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-bag-shopping" style="font-size: 60px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 10px;">Keranjang belanja Anda kosong</h3>
                <p style="color: #666; margin-bottom: 30px;">Tambahkan beberapa produk lezat untuk memulai belanja Anda.</p>
                <a href="/shop" class="btn-tracking-solid" style="padding: 12px 30px; display: inline-block;">Pergi ke Toko</a>
            </div>
        `;
        return;
    }

    // Dynamic cart UI wrapper
    container.innerHTML = `
        <div class="cart-layout-grid">
            <!-- Left Column: Products Card & Table -->
            <div class="cart-table-wrapper">
                <div style="overflow-x: auto;">
                    <table class="cart-table" style="width: 100%; border-collapse: collapse; min-width: 580px;">
                        <thead>
                            <tr style="background: #fafafa; border-bottom: 1.5px solid #e6e6e6; text-align: left;">
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Product</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Price</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Quantity</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Subtotal</th>
                                <th style="padding: 16px 20px; width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            ${cartItems.map(item => `
                                <tr style="border-bottom: 1px solid #f2f2f2;">
                                    <td style="padding: 18px 20px;">
                                        <div class="cart-product-cell" style="display: flex; align-items: center; gap: 14px;">
                                            <div class="cart-product-img" style="width: 72px; height: 72px; border-radius: 8px; overflow: hidden; background: #fdfdfd; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; flex-shrink: 0;">
                                                <img src="${item.img}" alt="${item.name}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            </div>
                                            <div style="display: flex; flex-direction: column;">
                                                <span class="cart-product-name" style="font-size: 14.5px; font-weight: 600; color: #1a1a1a;">${item.name}</span>
                                                ${item.custom_text ? `<small style="color: #00b207; font-size: 12px; margin-top: 3px;">Custom Teks: "${item.custom_text}"</small>` : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 18px 20px; font-size: 14px; font-weight: 500; color: #4d4d4d; white-space: nowrap;">
                                        Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}
                                    </td>
                                    <td style="padding: 18px 20px;">
                                        <div class="cart-qty-box" style="display: inline-flex; align-items: center; border: 1.5px solid #e6e6e6; border-radius: 50px; background: #fff; width: 104px; height: 36px; padding: 2px; justify-content: space-between;">
                                            <button type="button" class="cart-qty-btn" onclick="window.updateQty('${item.id}', -1)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #f5f5f5; color: #333; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">-</button>
                                            <input type="text" class="cart-qty-input" value="${item.qty}" readonly style="width: 36px; border: none; text-align: center; font-weight: 600; font-size: 13.5px; background: transparent; outline: none;">
                                            <button type="button" class="cart-qty-btn" onclick="window.updateQty('${item.id}', 1)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #f5f5f5; color: #333; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">+</button>
                                        </div>
                                    </td>
                                    <td style="padding: 18px 20px; font-size: 14.5px; font-weight: 700; color: #1a1a1a; white-space: nowrap;">
                                        Rp. ${new Intl.NumberFormat('id-ID').format(item.price * item.qty)}
                                    </td>
                                    <td style="padding: 18px 20px; text-align: right;">
                                        <button type="button" class="cart-remove-btn" onclick="window.removeFromCart('${item.id}')" title="Hapus produk" style="background: none; border: 1px solid #e6e6e6; width: 28px; height: 28px; border-radius: 50%; color: #999; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                            <i class="fa-solid fa-xmark" style="font-size: 14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="cart-table-actions" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #fff; border-top: 1px solid #f2f2f2;">
                    <a href="/shop" class="btn-action-gray" style="background: #f5f5f5; color: #333; border: 1px solid #e6e6e6; border-radius: 50px; padding: 10px 22px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: gap: 8px; transition: all 0.2s;">
                        <i class="fa-solid fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>

            <!-- Right Column: Card Cart Totals -->
            <div class="cart-total-card">
                <h3 class="cart-total-title" style="font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #f2f2f2;">Cart Totals</h3>
                <div class="summary-calc-list">
                    <div class="cart-total-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px; color: #64748b;">
                        <span>Subtotal</span>
                        <span style="font-weight: 600; color: #1a1a1a;">Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                    <div class="cart-total-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px; color: #64748b;">
                        <span>Pengiriman</span>
                        <span style="font-weight: 600; color: #00b207;">Gratis</span>
                    </div>
                    <div class="cart-total-row grand-total" style="display: flex; justify-content: space-between; padding: 16px 0 24px; font-size: 16px; color: #1a1a1a;">
                        <span style="font-weight: 700;">Total Belanja</span>
                        <span style="font-size: 18px; font-weight: 700; color: #024220;">Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                </div>
                <a href="${window.layoutConfig?.checkoutRoute || '/checkout'}" class="btn-checkout-green" style="display: flex; justify-content: center; align-items: center; width: 100%; background: #024220; color: #ffffff; border-radius: 50px; padding: 14px 20px; font-size: 15px; font-weight: 600; text-decoration: none; transition: background 0.2s; box-shadow: 0 4px 14px rgba(2, 66, 32, 0.2);">
                    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    `;
}
```

---

## 4. Hasil Verifikasi Pengujian

### A. Uji Koordinat Tata Letak Desktop ($1440 \times 900$)
Pengujian visual otomatis menghasilkan posisi bounding rectangle berikut:
- **Container Grid**: `x: 140px, y: 425px, width: 1160px`
- **Card Produk (Kiri)**: `x: 140px, y: 425px, width: 748px`
- **Card Cart Totals (Kanan)**: `x: 920px, y: 425px, width: 380px`
- **Bukti Sejajar**: Koordinat $y$ kedua kartu tepat sejajar pada `y = 425px` ($y_{\text{produk}} = y_{\text{total}}$), dan kartu Cart Totals berada tepat di sebelah kanan kartu produk ($x = 920 > 140$).

### B. Uji Responsivitas Mobile ($390 \times 844$)
- **Card Produk**: `x: 15px, y: 348px, width: 360px`
- **Card Cart Totals**: `x: 15px, y: 722px, width: 360px`
- **Hasil**: Otomatis bertumpuk 1 kolom (*single column stack*) dengan margin samping pas $15\text{px}$ dan tanpa *overflow* layar.

### C. Uji Fungsional Interaktif
- **Penambahan Kuantitas (+)**: Nilai bertambah dari 1 menjadi 2, total belanja otomatis naik secara realtime.
- **Pengurangan Kuantitas (-)**: Nilai berkurang dari 2 menjadi 1, total belanja otomatis turun kembali.
- **Pembersihan Cache & Build**: `npm run build` sukses dalam $479\text{ms}$, `php artisan view:clear` berhasil.
