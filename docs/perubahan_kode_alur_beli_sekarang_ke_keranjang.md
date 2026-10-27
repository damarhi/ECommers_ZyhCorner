# Dokumentasi Perubahan Kode: Alur Tombol "Beli Sekarang" Langsung ke Halaman Keranjang

## 1. Ringkasan & Akar Penyebab (*Root Cause*)

Sebelum perbaikan ini dilakukan:
- Tombol **"Beli Sekarang"** (*Buy Now*) memanfaatkan fungsi `window.addToCart(product)` untuk menyimpan produk ke memori (*localStorage*).
- Di dalam fungsi `window.addToCart(product)`, baris terakhir selalu memanggil `window.toggleCart(true)` yang secara otomatis membuka laci *sidebar cart* di sisi kanan layar.
- Akibatnya, saat pelanggan menekan tombol *"Beli Sekarang"*, terjadi efek samping (*side effect*) berupa pembukaan laci sidebar keranjang sesaat sebelum halaman berpindah ke rute pembelian.

Setelah perbaikan ini diterapkan:
- Fungsi `window.addToCart(product, openDrawer = true)` memiliki parameter kontrol opsional `openDrawer` yang bernilai bawaan `true`.
- Alur **"Beli Sekarang"** memanggil `window.addToCart(product, false)`, sehingga laci sidebar **sama sekali tidak terbuka**.
- Browser pengguna **langsung diarahkan secara instan ke Halaman Keranjang Belanja Penuh (`/cart`)**.
- Alur **"Tambah ke Keranjang"** biasa tetap membuka laci sidebar seperti sediakala agar pelanggan dapat lanjut memilih kue/roti lain di halaman yang sama.

---

## 2. Rincian Perubahan Kode Berkas (*Code Changes*)

### A. Berkas `resources/views/Layout/layout_pelanggan.blade.php`
Menambahkan properti rute keranjang `cartRoute: "{{ route('userChart') }}"` pada objek konfigurasi global JavaScript `window.layoutConfig`:

```blade
window.layoutConfig = {
    isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
    userRole: "{{ Auth::check() ? Auth::user()->role : 'guest' }}",
    loginRoute: "{{ route('login') }}",
    checkoutRoute: "{{ route('checkout') }}",
    cartRoute: "{{ route('userChart') }}", // <-- Ditambahkan
    posRoute: "{{ route('pos.index') }}",
    adminDashboardRoute: "{{ url('/admin/dashboard') }}",
    successMessage: "{{ session('success') }}",
    searchSuggestionsRoute: "{{ route('search.suggestions') }}"
};
```

---

### B. Berkas `resources/views/Pelanggan/detail.blade.php`
Menambahkan properti `cartRoute: '{{ route('userChart') }}'` pada objek konfigurasi halaman detail produk `window.detailConfig`:

```blade
window.detailConfig = {
    productId: '{{ $product->id_produk }}',
    productName: '{{ $product->nama_produk }}',
    productPrice: {{ $product->has_diskon ? $product->harga_diskon : $product->harga }},
    bisaCustomTeks: {{ $product->bisa_custom_teks ? 'true' : 'false' }},
    productImg: '{{ reset($fotos) }}',
    checkoutRoute: '{{ route('checkout') }}',
    cartRoute: '{{ route('userChart') }}', // <-- Ditambahkan
    loginRoute: '{{ route('login') }}'
};
```

---

### C. Berkas `resources/js/pelanggan_pages.js`

#### 1. Penambahan Parameter Kontrol `openDrawer` pada `window.addToCart` (L334–L361)
```javascript
// Parameter openDrawer bernilai default true
window.addToCart = function(product, openDrawer = true) {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }
    const existing = window.cart.find(item => item.id === product.id);
    if (existing) {
        existing.qty += (product.qty || 1);
        if (product.bisa_custom_teks !== undefined) {
            existing.bisa_custom_teks = product.bisa_custom_teks;
        }
    } else {
        window.cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            img: product.img,
            qty: product.qty || 1,
            bisa_custom_teks: product.bisa_custom_teks || false,
            custom_text: product.custom_text || ''
        });
    }
    window.saveCart();
    
    // HANYA buka drawer sidebar jika openDrawer bernilai true
    if (openDrawer) {
        window.toggleCart(true);
    }
    return true;
};
```

#### 2. Penyesuaian Fungsi `window.buyNow()` (L1640–L1662)
```javascript
window.buyNow = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }
    if (!window.isLoggedIn) {
        window.location.href = window.detailConfig?.loginRoute || "/login";
        return;
    }

    if (window.detailConfig) {
        const conf = window.detailConfig;
        // Panggil addToCart dengan openDrawer = false
        window.addToCart({
            id: conf.productId,
            name: conf.productName,
            price: conf.productPrice,
            bisa_custom_teks: conf.bisaCustomTeks || false,
            img: conf.productImg,
            qty: parseInt(document.getElementById('qtyInput')?.value || 1)
        }, false);
        
        // Langsung navigasikan ke halaman Keranjang Belanja (/cart)
        window.location.href = conf.cartRoute || window.layoutConfig?.cartRoute || "/cart";
    }
};
```

#### 3. Penyesuaian Modal *Quick View* `pmBuyBtn.onclick` (L122–L131)
```javascript
pmBuyBtn.onclick = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.closeProductModal();
        window.showAdminShoppingNotice();
        return;
    }
    if (!window.isLoggedIn) {
        window.closeProductModal();
        window.location.href = window.layoutConfig?.loginRoute || "/login";
        return;
    }
    // Panggil addToCart dengan openDrawer = false
    window.addToCart({
        id: data.id,
        name: data.name,
        price: price,
        img: data.f1 || '',
        qty: parseInt(document.getElementById('pmQtyInput').value)
    }, false);
    
    window.closeProductModal();
    // Langsung navigasikan ke halaman Keranjang Belanja (/cart)
    window.location.href = window.layoutConfig?.cartRoute || "/cart";
};
```

---

## 3. Bukti Verifikasi Pengujian (*Automated Headless Browser Proof*)

Pengujian dilakukan menggunakan browser headless pada akun pelanggan `nama@gmail.com`:

```
1. Klik Tombol "Beli Sekarang" pada Halaman Detail Produk (/product/1):
   - Status Drawer Sidebar Seketika:
     overlayDisplay: "none"
     sidebarRight: "-400px" (tertutup sempurna)
   - URL Browser Setelah Klik:
     http://clone_pa_zyhcorner.test/cart
   - Isi Keranjang pada Halaman /cart:
     cartItemCount: 1
     Nama Produk: "Black Forest Cake Classic"
     Harga: Rp 100.000
   -> HASIL: LOLOS (Navigasi langsung tanpa membuka drawer sidebar)

2. Pengujian Tombol "Tambah ke Keranjang" pada Katalog (/shop):
   - Pemanggilan window.addToCart() bawaan:
     overlayDisplay: "block"
     cartCount: 2
   -> HASIL: LOLOS (Laci sidebar tetap terbuka untuk kenyamanan lanjut belanja)
```

Tangkapan layar pengujian tersimpan pada:
- `docs/beli_sekarang_direct_cart.png`
- `docs/tambah_keranjang_sidebar_open.png`

---

## 4. Kesimpulan
Seluruh permintaan perbaikan telah rampung dengan presisi: tombol "Beli Sekarang" tidak lagi memicu efek samping pembukaan sidebar, melainkan langsung membawa pembeli ke halaman keranjang belanja (`/cart`).
