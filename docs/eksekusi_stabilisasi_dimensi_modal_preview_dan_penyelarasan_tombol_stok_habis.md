# Dokumentasi Eksekusi: Stabilisasi Dimensi Modal Preview, Perapian Tipografi Harga, dan Penyelarasan Tombol Stok Habis

## 1. Latar Belakang & Akar Masalah (Root Causes)

Berdasarkan investigasi terhadap 4 screenshot pratinjau modal produk (`#productModal`), ditemukan beberapa penyebab inkonsistensi tata letak antarmuka:

1. **Perbedaan Dimensi & Ukuran Modal Antar Gambar**:
   * **Gambar #1 (Black Forest)**: Hanya memiliki 1 foto (`foto_1`). Logika JavaScript sebelumnya menyembunyikan strip thumbnail `#pmThumbnailsContainer` (`display: none;`). Karena kolom galeri kiri (`.pm-gallery`) tidak memiliki lebar tetap (`width`), hilangnya kolom thumbnail selebar 72px menyebabkan galeri menyusut dan proporsi modal berubah (tampak lebih sempit/gepeng).
   * **Gambar #1, #2, #3 (Stok Habis)**: Saat stok produk bernilai `0`, JavaScript menambahkan kelas `.pm-actions.is-out-of-stock`. Di CSS lama, kelas ini mengubah `flex-direction: column`, melebarkan tombol beli ke `width: 100%`, dan menyembunyikan tombol keranjang (`.pm-cart-btn { display: none !important; }`). Akibatnya, area tombol aksi berubah dari 1 baris horizontal menjadi 2 baris vertikal, mendorong elemen footer dan menambah tinggi modal secara drastis (*layout shift*).
   * **Gambar #4 (Stok Tersedia)**: Memiliki tata letak asli 1 baris horizontal (`[- 1 +]` + `[Beli Sekarang]` + `[🛒]`), sehingga tinggi modal jauh lebih ringkas.
   * **Ketiadaan Penguncian Dimensi**: Modal sebelumnya hanya memakai `max-height: 90vh;` tanpa tinggi (`height`) atau lebar (`width`) tetap di desktop, sehingga ukurannya mengembang dan mengempis mengikuti teks deskripsi dan status stok.

2. **Penyebab Adanya Tanda Panah Ke Atas / Ke Bawah pada Gambar 4 (dan Gambar 2)**:
   * Pada `resources/js/pelanggan_pages.js`, terdapat logika:
     ```javascript
     if (thumbPrev) thumbPrev.style.display = images.length > 3 ? 'flex' : 'none';
     if (thumbNext) thumbNext.style.display = images.length > 3 ? 'flex' : 'none';
     ```
   * Produk pada **Gambar #4** (*Chocolate Almond Biscotti*) dan **Gambar #2** (*Matcha Layer Cake*) memiliki **4 foto** (`foto_1` s/d `foto_4`), sehingga `images.length > 3` terpenuhi dan panah navigasi `#pmThumbPrev` serta `#pmThumbNext` diaktifkan untuk menggulir thumbnail vertikal.
   * Pada **Gambar #3**, hanya terdapat **3 foto** (`images.length == 3`), sehingga panah navigasi disembunyikan.
   * Pada **Gambar #1**, hanya ada **1 foto** sehingga seluruh kontainer thumbnail disembunyikan.

---

## 2. Arahan Desain & Keputusan Arsitektur (`/skill:frontend-design-direction`)

1. **Prinsip *Zero Layout Shift***:
   * Mengunci dimensi desktop `.product-modal` secara presisi: `width: 960px; height: 560px; overflow: hidden;`.
   * Mengunci lebar kolom galeri kiri `.pm-gallery` ke `width: 440px; flex-shrink: 0; height: 100%;`.
   * Jika produk hanya memiliki 1 foto, `#pmThumbnailsContainer` disembunyikan dan foto utama `.pm-main-image` otomatis melebar memenuhi 440px tanpa pernah menggeser atau mengubah lebar kolom detail kanan (`.pm-details`).
   * Kolom detail kanan (`.pm-details`) dikunci ke `flex: 1; height: 100%; overflow-y: auto;` dengan scrollbar tipis agar elemen tidak pernah meluap keluar modal.

2. **Perapian Tipografi Harga**:
   * Harga baru (`.pm-new-price`) diperkecil dari `26px; font-weight: 800;` menjadi **`font-size: 20px; font-weight: 700; letter-spacing: -0.3px;`**.
   * Harga coret (`.pm-old-price`) disesuaikan ke **`font-size: 13.5px;`**.
   * Badge diskon (`.pm-discount-badge`) diperkecil menjadi **`font-size: 11px; padding: 2.5px 7px; border-radius: 5px;`**.
   * Kotak harga (`.pm-price-area`) diperkecil paddingnya menjadi **`padding: 8px 14px; margin-bottom: 12px; border-radius: 10px;`**.

3. **Penyelarasan Kontrol Aksi Identik (1 Baris Horizontal)**:
   * Menghapus aturan `flex-direction: column` dan `display: none !important` pada `.pm-actions.is-out-of-stock`.
   * Tata letak saat stok habis dibuat **100% identik** dengan saat stok tersedia:
     * Kuantiti: `[- 0 +]` dengan `opacity: 0.5; pointer-events: none;` dan `input.disabled = true`.
     * Tombol Beli: `[Stok Habis 🚫]` dengan `flex: 1; background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; cursor: not-allowed; pointer-events: none;`.
     * Tombol Keranjang: `[🛒]` **tetap ditampilkan** (`display: inline-flex`), berstatus dinonaktifkan (`disabled`) dengan `background: #f1f5f9; color: #94a3b8; border: 1.5px solid #e2e8f0; cursor: not-allowed; pointer-events: none;`.

---

## 3. Rincian Perubahan Kode

### A. `public/css/pelanggan.css` & `resources/css/pelanggan.css`

1. **Penguncian Dimensi Modal & Galeri**:
   ```css
   .product-modal {
       background: #ffffff;
       border-radius: 20px;
       width: 960px;
       max-width: 95vw;
       height: 560px;
       max-height: 90vh;
       position: relative;
       padding: 26px 30px;
       box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
       display: flex;
       flex-direction: column;
       overflow: hidden;
       animation: pmSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
       scrollbar-width: thin;
       box-sizing: border-box;
   }

   .product-modal-content {
       display: flex;
       gap: 28px;
       align-items: stretch;
       height: 100%;
       overflow: hidden;
   }

   .pm-gallery {
       display: flex;
       gap: 14px;
       width: 440px;
       flex-shrink: 0;
       align-items: stretch;
       height: 100%;
   }

   .pm-main-image {
       flex: 1;
       background: #f8fafc;
       border: 1px solid #f1f5f9;
       border-radius: 16px;
       overflow: hidden;
       display: flex;
       justify-content: center;
       align-items: center;
       padding: 8px;
       height: 100%;
       max-height: 100%;
       position: relative;
       box-sizing: border-box;
   }

   .pm-details {
       flex: 1;
       display: flex;
       flex-direction: column;
       justify-content: space-between;
       height: 100%;
       overflow-y: auto;
       padding-right: 4px;
       scrollbar-width: thin;
   }
   ```

2. **Tipografi Harga Rapi & Ringkas**:
   ```css
   .pm-price-area {
       display: flex;
       align-items: baseline;
       gap: 10px;
       padding: 8px 14px;
       background: #f8fafc;
       border-radius: 10px;
       border: 1px solid #f1f5f9;
       margin-bottom: 12px;
   }

   .pm-new-price {
       color: #00b207;
       font-size: 20px;
       font-weight: 700;
       letter-spacing: -0.3px;
   }

   .pm-old-price {
       color: #94a3b8;
       text-decoration: line-through;
       font-size: 13.5px;
       font-weight: 500;
   }

   .pm-discount-badge {
       background: #fef2f2;
       color: #ef4444;
       padding: 2.5px 7px;
       border-radius: 5px;
       font-size: 11px;
       font-weight: 700;
       border: 1px solid #fee2e2;
   }
   ```

3. **Penyelarasan Tombol Aksi Stok Habis**:
   ```css
   .pm-actions {
       display: flex;
       align-items: center;
       gap: 12px;
       margin-bottom: 16px;
       padding-bottom: 16px;
       border-bottom: 1px solid #f1f5f9;
   }
   .pm-actions.is-out-of-stock {
       display: flex;
       align-items: center;
       gap: 12px;
   }

   .pm-buy-btn.disabled,
   .pm-buy-btn:disabled {
       background-color: #f1f5f9 !important;
       color: #94a3b8 !important;
       border: 1px solid #e2e8f0 !important;
       cursor: not-allowed !important;
       box-shadow: none !important;
       transform: none !important;
       pointer-events: none !important;
   }

   .pm-cart-btn.disabled,
   .pm-cart-btn:disabled {
       background-color: #f1f5f9 !important;
       color: #94a3b8 !important;
       border: 1.5px solid #e2e8f0 !important;
       cursor: not-allowed !important;
       box-shadow: none !important;
       transform: none !important;
       pointer-events: none !important;
   }
   ```

4. **Penyesuaian Responsif Mobile (Breakpoint 900px)**:
   ```css
   @media (max-width: 900px) {
       .product-modal {
           width: 95vw;
           height: auto;
           max-height: 90vh;
           padding: 24px 20px;
           overflow-y: auto;
       }
       .product-modal-content {
           flex-direction: column;
           gap: 20px;
           height: auto;
           overflow: visible;
       }
       .pm-gallery {
           width: 100%;
           flex-direction: column-reverse;
           gap: 12px;
           height: auto;
       }
       .pm-main-image {
           min-height: 320px;
           max-height: 380px;
           height: auto;
       }
       .pm-details {
           height: auto;
           overflow-y: visible;
       }
   }
   ```

---

### B. `resources/css/pelanggan_custom.css`

* Menghapus aturan lama yang mengubah tata letak tombol aksi ke vertikal dan menyembunyikan tombol keranjang:
  ```css
  /* Sebelum */
  .pm-actions.is-out-of-stock {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 14px;
  }
  .pm-actions.is-out-of-stock .pm-buy-btn {
      width: 100%;
      max-width: 360px;
      flex: none;
      ...
  }
  .pm-actions.is-out-of-stock .pm-cart-btn {
      display: none !important;
  }

  /* Sesudah */
  .pm-actions.is-out-of-stock {
      display: flex;
      align-items: center;
      gap: 12px;
  }
  ```

---

### C. `resources/js/pelanggan_pages.js`

* Mengubah logika penanganan tombol keranjang saat `currentStock <= 0` agar **tetap ditampilkan** dengan status dinonaktifkan:
  ```javascript
  // Sebelum:
  if (pmCartBtn) {
      pmCartBtn.style.display = 'none';
      pmCartBtn.classList.add('disabled');
      pmCartBtn.disabled = true;
      pmCartBtn.onclick = null;
  }

  // Sesudah:
  if (pmCartBtn) {
      pmCartBtn.style.display = 'inline-flex';
      pmCartBtn.classList.add('disabled');
      pmCartBtn.disabled = true;
      pmCartBtn.onclick = null;
  }
  ```

---

### D. `resources/css/pelanggan_pages.css` & Kompilasi Vite

* Menambahkan aturan stabilisasi dimensi, tipografi harga, dan tata letak aksi pada `resources/css/pelanggan_pages.css`.
* Berhasil mengompilasi ulang bundel Vite via `npm run build` (`public/build/assets/pelanggan_pages-*.css` dan `.js`).

---

## 4. Hasil Verifikasi & Pengujian Otomatis

1. **Pengujian Suite Otomatis (`php artisan test`)**:
   * Seluruh **211 tests** lulus dengan **1138 assertions** tanpa kegagalan:
     ```
     Tests:    211 passed (1138 assertions)
     Duration: 50.12s
     ```
2. **Pembersihan Berkas Uji**:
   * Direktori `public/uploads/` telah dibersihkan dari artefak gambar hasil pengujian menggunakan `git clean -fd public/uploads/`.
