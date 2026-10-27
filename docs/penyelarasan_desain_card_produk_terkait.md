# Dokumentasi Perubahan Kode: Penyelarasan Desain dan Fungsi Card Produk Terkait

## 1. Latar Belakang & Tujuan
Sebelumnya, section **Produk Terkait (*Related Products*)** pada halaman detail produk (`resources/views/Pelanggan/detail.blade.php`) menggunakan struktur markup dan styling tersendiri (`.rp-card`, `.rp-img`, `.rp-btn-link`). Tampilan kartu ini terasa kaku, tidak memiliki badge diskon/stok habis, tidak mendukung fitur *Quick View*, dan memiliki tombol aksi yang terbelah dua di bagian bawah sehingga tidak konsisten dengan kartu produk utama di halaman katalog belanja (`shop.blade.php`).

Penyelarasan ini dilakukan untuk:
1. **Menghadirkan Konsistensi Visual (*Visual Consistency*)**: Menggunakan desain kartu produk seragam (`.product-card`) dengan radius 16px, background putih bersih, padding rapi, dan efek elevasi hover `translateY(-4px)` dengan bayangan lembut.
2. **Penyetaraan Fungsionalitas (*Feature Parity*)**:
   - Menambahkan badge persentase diskon oranye (`.badge`) saat produk sedang promo.
   - Menambahkan badge hitam `"Habis"` (`.badge.out-of-stock`) saat stok produk kosong.
   - Menambahkan tombol aksi cepat *Quick View* (ikon mata `.action-btn`) saat kartu di-*hover*.
   - Mengganti tombol ganda lama dengan tombol aksi floating bulat `.cart-btn` di sudut kanan bawah kartu yang memicu pratinjau modal *Quick View* (atau tautan WhatsApp untuk produk kustom).
   - Membuat seluruh area kartu dapat diklik (*clickable*) langsung menuju halaman detail produk yang bersangkutan.

---

## 2. File-File dan Kode yang Diubah

### A. Template Detail Produk (`resources/views/Pelanggan/detail.blade.php`)
- **Sebelum:**
  ```blade
  {{-- RELATED PRODUCTS --}}
  @if ($relatedProducts->count() > 0)
      <div>
          <div class="section-header">
              <h2>Produk Terkait</h2>
              <a href="{{ route('shop') }}" class="view-all-link">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
          </div>
          <div class="related-grid">
              @foreach ($relatedProducts as $rp)
                  <div class="rp-card">
                      <div class="rp-img">
                          <img src="{{ $rp->foto_1 ? asset('storage/' . $rp->foto_1) : '' }}" alt="{{ $rp->nama_produk }}">
                      </div>
                      <div class="rp-cat">{{ $rp->kategori->nama_kategori ?? 'Produk' }}</div>
                      <div class="rp-name">{{ $rp->nama_produk }}</div>
                      <div class="rp-price">
                          ...
                      </div>
                      <div style="display:flex; gap:8px; margin-top:10px;">
                          <a href="{{ route('product.detail', $rp->id_produk) }}" class="rp-btn-link" style="flex:1;">Detail</a>
                          <button class="rp-btn-link" style="flex:0 0 40px;" onclick="addToCart(...)">
                              <i class="fa-solid fa-bag-shopping"></i>
                          </button>
                      </div>
                  </div>
              @endforeach
          </div>
      </div>
  @endif
  ```

- **Sesudah:**
  ```blade
  {{-- RELATED PRODUCTS --}}
  @if ($relatedProducts->count() > 0)
      <div style="margin-top: 50px;">
          <div class="section-header">
              <h2>Produk Terkait</h2>
              <a href="{{ request()->query('custom') == 1 ? route('custom-products') : route('shop') }}" class="view-all-link">
                  Lihat Semua <i class="fa-solid fa-arrow-right"></i>
              </a>
          </div>
          <div class="product-grid related-grid">
              @foreach ($relatedProducts as $rp)
                  <div class="product-card" style="cursor: pointer;"
                      onclick="window.location.href='{{ route('product.detail', $rp->id_produk) }}{{ request()->query('custom') == 1 ? '?custom=1' : '' }}'">
                      <div class="product-image">
                          @if ($rp->has_diskon)
                              <div class="badge" style="background-color: #ff8a00; color: white;">{{ $rp->diskon_badge }}</div>
                          @elseif ($rp->stok == 0)
                              <div class="badge out-of-stock">Habis</div>
                          @endif

                          <div class="product-actions">
                              <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                  data-id="{{ $rp->id_produk }}" data-name="{{ $rp->nama_produk }}"
                                  data-price="{{ $rp->harga_diskon }}"
                                  data-old-price="{{ $rp->has_diskon ? 'Rp. ' . number_format($rp->harga, 0, ',', '.') : '' }}"
                                  data-discount-badge="{{ $rp->diskon_badge ?? '' }}"
                                  data-desc="{{ $rp->deskripsi }}"
                                  data-cat="{{ $rp->kategori->nama_kategori ?? 'Uncategorized' }}"
                                  data-sale="{{ $rp->has_diskon ? '1' : '0' }}" data-stock="{{ $rp->stok }}"
                                  @if(request()->query('custom') == 1) data-custom="1" @endif
                                  data-f1="{{ $rp->foto_1 ? asset('storage/' . $rp->foto_1) : '' }}"
                                  data-f2="{{ $rp->foto_2 ? asset('storage/' . $rp->foto_2) : '' }}"
                                  data-f3="{{ $rp->foto_3 ? asset('storage/' . $rp->foto_3) : '' }}"
                                  data-f4="{{ $rp->foto_4 ? asset('storage/' . $rp->foto_4) : '' }}">
                                  <i class="fa-regular fa-eye"></i>
                              </div>
                          </div>

                          <a href="{{ route('product.detail', $rp->id_produk) }}{{ request()->query('custom') == 1 ? '?custom=1' : '' }}" style="display:block;" onclick="event.stopPropagation();">
                              @if ($rp->foto_1)
                                  <img src="{{ asset('storage/' . $rp->foto_1) }}" alt="{{ $rp->nama_produk }}">
                              @endif
                          </a>
                      </div>
                      <div class="product-info">
                          <a href="{{ route('product.detail', $rp->id_produk) }}{{ request()->query('custom') == 1 ? '?custom=1' : '' }}"
                              class="product-name" onclick="event.stopPropagation();">{{ $rp->nama_produk }}</a>
                          <div class="product-price">
                              @if ($rp->has_diskon)
                                  Rp. {{ number_format($rp->harga_diskon, 0, ',', '.') }}
                                  <span class="old" style="font-size: 13px; text-decoration: line-through; color: #999; margin-left: 5px;">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</span>
                              @else
                                  Rp. {{ number_format($rp->harga, 0, ',', '.') }}
                              @endif
                          </div>

                          @if (request()->query('custom') == 1)
                              @if ($rp->stok > 0)
                                  @php
                                      $storeName = $storeSettings['store_name'] ?? 'Zyh Corner';
                                      $rpMessage = urlencode("Halo " . $storeName . ", saya tertarik untuk memesan Custom Product: " . $rp->nama_produk . " (Harga: Rp. " . number_format($rp->has_diskon ? $rp->harga_diskon : $rp->harga, 0, ',', '.') . "). Mohon info lebih lanjut.");
                                      $rpWaUrl = "https://wa.me/" . ($storeSettings['wa_number'] ?? '628123456789') . "?text=" . $rpMessage;
                                  @endphp
                                  <a href="{{ $rpWaUrl }}" target="_blank" class="cart-btn" style="background-color: #25d366; color: white;" title="Pesan via WhatsApp" onclick="event.stopPropagation();">
                                      <i class="fa-brands fa-whatsapp"></i>
                                  </a>
                              @else
                                  <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                      <i class="fa-solid fa-ban"></i>
                                  </div>
                              @endif
                          @else
                              @if ($rp->stok > 0)
                                  <div class="cart-btn" title="Lihat Preview & Pesan"
                                      onclick="event.stopPropagation(); openProductModal(this);"
                                      data-id="{{ $rp->id_produk }}" data-name="{{ $rp->nama_produk }}"
                                      data-price="{{ $rp->harga_diskon }}"
                                      data-old-price="{{ $rp->has_diskon ? 'Rp. ' . number_format($rp->harga, 0, ',', '.') : '' }}"
                                      data-discount-badge="{{ $rp->diskon_badge ?? '' }}"
                                      data-desc="{{ $rp->deskripsi }}"
                                      data-cat="{{ $rp->kategori->nama_kategori ?? 'Uncategorized' }}"
                                      data-sale="{{ $rp->has_diskon ? '1' : '0' }}" data-stock="{{ $rp->stok }}"
                                      data-f1="{{ $rp->foto_1 ? asset('storage/' . $rp->foto_1) : '' }}"
                                      data-f2="{{ $rp->foto_2 ? asset('storage/' . $rp->foto_2) : '' }}"
                                      data-f3="{{ $rp->foto_3 ? asset('storage/' . $rp->foto_3) : '' }}"
                                      data-f4="{{ $rp->foto_4 ? asset('storage/' . $rp->foto_4) : '' }}">
                                      <i class="fa-solid fa-bag-shopping"></i>
                                  </div>
                              @else
                                  <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                      <i class="fa-solid fa-ban"></i>
                                  </div>
                              @endif
                          @endif
                      </div>
                  </div>
              @endforeach
          </div>
      </div>
  @endif
  ```

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)
- Menghapus aturan selektor yang sudah tidak terpakai (*dead styles*): `.rp-card`, `.rp-card:hover`, `.rp-img`, `.rp-cat`, `.rp-name`, `.rp-price`, `.rp-stars`, `.rp-btn-link`.
- Memperbarui gaya `.view-all-link` agar selaras.
- Menambahkan aturan responsif `.related-grid` untuk breakpoint layar kecil (< 480px) menjadi 1 kolom (`grid-template-columns: 1fr`).

---

## 3. Hasil Pengujian & Verifikasi

1. **Vite Bundler Compilation**:
   ```bash
   npm run build
   ```
   *Hasil:* Sukses tanpa peringatan/kesalahan (`built in 464ms`).
2. **Pembersihan Cache Tampilan**:
   ```bash
   php artisan view:clear
   ```
   *Hasil:* Sukses (`Compiled views cleared successfully`).
3. **Uji Regresi Otomatis**:
   ```bash
   php artisan test
   ```
   *Hasil:* **90 passed (370 assertions)**, 0 failed.
4. **Verifikasi Interaktivitas**:
   - Klik kartu mengarahkan pelanggan langsung ke halaman detail produk terkait.
   - Klik ikon mata membuka pop-up modal Quick View dengan data produk terkait yang lengkap.
   - Klik tombol keranjang/tas belanja membuka modal untuk pemilihan kuantitas dan pembelian langsung.
   - Badge diskon dan status stok habis muncul sesuai kondisi masing-masing produk terkait.
