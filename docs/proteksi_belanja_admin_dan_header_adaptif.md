# Dokumentasi Proteksi Belanja Admin/Kasir & Header Adaptif (Pilihan 2)

## 1. Latar Belakang & Prinsip Desain (*Frontend Design Direction*)

Sebelumnya, ketika akun dengan peran **Admin** atau **Kasir** membuka etalase toko pelanggan (`127.0.0.1:8000/`):
1. Menu dropdown profil di header menampilkan tautan statis *"Dashboard"* yang mengarah ke `route('dashboard')` (`/dashboard`), yaitu dashboard milik pelanggan. Jika diklik, Admin akan menemui error 403.
2. Admin/Kasir masih dapat mengklik tombol belanja (*"Beli Sekarang"* dan *"Tambah ke Keranjang"*), padahal transaksi Admin/Kasir di toko fisik seharusnya diproses melalui panel POS Kasir (`/admin/pos`), bukan melalui alur keranjang belanja online pelanggan.
3. Rute halaman keranjang belanja penuh (`/cart`) belum memiliki proteksi peran, sehingga dapat diakses oleh Admin/Kasir.

Sesuai instruksi pengguna dan panduan **`frontend-design-direction`**:
- **Zero Layout Shift / Tidak Menyembunyikan Elemen**: Seluruh elemen antarmuka (ikon keranjang di header, badge jumlah, tombol *"Beli Sekarang"*, tombol *"Tambah ke Keranjang"*, modal Quick View) **tetap tampil 100% utuh tanpa ada yang dihilangkan**. Hal ini memungkinkan Admin/Kasir tetap dapat menginspeksi estetika dan kelengkapan katalog toko persis seperti yang dilihat oleh pengunjung/pelanggan.
- **Umpan Balik Santun & Informatif**: Ketika Admin/Kasir berinteraksi dengan tombol belanja, sistem tidak memasukkan barang ke keranjang atau mengalihkan ke checkout, melainkan menampilkan dialog elegan (SweetAlert2 bertema *Zyh Corner*) yang mengedukasi batasan peran dan menyediakan tombol pintas langsung ke **POS Kasir**.

---

## 2. Rincian Perubahan Kode Berkas (*Code Changes*)

### A. Berkas `resources/views/Layout/layout_pelanggan.blade.php`

#### 1. Menu Dropdown Header Adaptif (L111–L117)
Mengganti link statis `route('dashboard')` dengan tautan kondisional berdasarkan peran aktif pengguna:
```blade
@auth
    <div class="profile-dropdown-item profile-dropdown-name">{{ Auth::user()->nama ?? 'User' }}</div>
    
    @if(Auth::user()->role === 'admin')
        <a href="{{ url('/admin/dashboard') }}" class="profile-dropdown-item">
            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard Admin
        </a>
    @elseif(Auth::user()->role === 'kasir')
        <a href="{{ route('orders.index') }}" class="profile-dropdown-item">
            <i class="fa-solid fa-cash-register me-1"></i> Kelola Pesanan
        </a>
    @else
        <a href="{{ route('dashboard') }}" class="profile-dropdown-item">Dashboard</a>
    @endif

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="button" onclick="confirmLogout()" class="profile-dropdown-item">Logout</button>
    </form>
@else
    <a href="{{ route('login') }}" class="profile-dropdown-item">Login</a>
@endauth
```

#### 2. Tautan Footer Adaptif (L204–L208)
Mengarahkan link akun dan pesanan di footer sesuai peran pengguna:
```blade
<div class="footer-links">
    <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? url('/admin/dashboard') : route('dashboard') }}">Akun Saya</a>
    <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? route('orders.index') : route('order.history') }}">Riwayat Pesanan</a>
    <a href="{{ route('userChart') }}">Keranjang Belanja</a>
    <a href="{{ Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']) ? route('settings.index') : route('settings') }}">Pengaturan Profil</a>
</div>
```

#### 3. Tombol Drawer Keranjang Adaptif (L291–L299)
Elemen tombol tetap terlihat 100% sama, namun memanggil `showAdminShoppingNotice()` saat diklik oleh Admin/Kasir:
```blade
<div class="cart-btns">
    @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir']))
        <button type="button" class="btn-checkout" onclick="showAdminShoppingNotice()" style="cursor: pointer; border: none; font-family: inherit;">Checkout</button>
        <button type="button" class="btn-view-cart" onclick="showAdminShoppingNotice()" style="cursor: pointer; border: none; font-family: inherit;">Go To Cart</button>
    @else
        <a href="{{ route('userChart') }}" class="btn-checkout">Checkout</a>
        <a href="{{ route('userChart') }}" class="btn-view-cart">Go To Cart</a>
    @endif
</div>
```

#### 4. Ekstraksi Konfigurasi Global JavaScript (L385–L395)
Menyediakan data peran pengguna dan URL rute yang dibutuhkan skrip frontend:
```blade
<script>
    window.layoutConfig = {
        isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},
        userRole: "{{ Auth::check() ? Auth::user()->role : 'guest' }}",
        loginRoute: "{{ route('login') }}",
        checkoutRoute: "{{ route('checkout') }}",
        posRoute: "{{ route('pos.index') }}",
        adminDashboardRoute: "{{ url('/admin/dashboard') }}",
        successMessage: "{{ session('success') }}",
        searchSuggestionsRoute: "{{ route('search.suggestions') }}"
    };
</script>
```

---

### B. Berkas `resources/js/pelanggan_pages.js`

#### 1. Fungsi Edukasi Dialog `showAdminShoppingNotice()`
Menampilkan pop-up dialog SweetAlert2 yang selaras dengan tema aplikasi:
```javascript
window.showAdminShoppingNotice = function() {
    const role = window.layoutConfig?.userRole || '';
    const roleLabel = role === 'admin' ? 'Administrator' : 'Kasir Toko';
    const posRoute = window.layoutConfig?.posRoute || '/admin/pos';

    if (window.Swal) {
        Swal.fire({
            icon: 'info',
            title: 'Akses Belanja Khusus Pelanggan',
            html: `Akun Anda terdaftar sebagai <b>${roleLabel}</b>.<br><br>Fitur keranjang belanja dan checkout online hanya diperuntukkan bagi pelanggan toko.<br>Untuk memproses transaksi penjualan langsung di toko, silakan gunakan menu <b>POS Kasir</b>.`,
            confirmButtonText: '<i class="fa-solid fa-cash-register me-1"></i> Buka POS Kasir',
            confirmButtonColor: '#024220',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = posRoute;
            }
        });
    } else {
        if (confirm(`Akses Belanja Khusus Pelanggan.\n\nAkun Anda terdaftar sebagai ${roleLabel}.\nFitur keranjang belanja dan checkout online hanya diperuntukkan bagi pelanggan toko.\n\nApakah Anda ingin membuka menu POS Kasir?`)) {
            window.location.href = posRoute;
        }
    }
};
```

#### 2. Proteksi pada `window.addToCart(product)`
```javascript
window.addToCart = function(product) {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }
    // ... Alur keranjang normal untuk pelanggan ...
};
```

#### 3. Proteksi pada `window.buyNow()`
```javascript
window.buyNow = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }
    // ... Alur checkout normal untuk pelanggan ...
};
```

#### 4. Proteksi pada Modal Quick View (`pmBuyBtn` & `pmCartBtn`)
```javascript
pmBuyBtn.onclick = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.closeProductModal();
        window.showAdminShoppingNotice();
        return;
    }
    // ...
};

pmCartBtn.onclick = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.closeProductModal();
        window.showAdminShoppingNotice();
        return;
    }
    // ...
};
```

---

### C. Berkas `app/Http/Controllers/Pelanggan/CartCheckoutController.php`

Menambahkan proteksi server-side pada fungsi `userChart()`:
```php
public function userChart()
{
    if (Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir'])) {
        abort(403, 'Akses Ditolak: Halaman keranjang belanja hanya diperuntukkan bagi pelanggan toko.');
    }

    return view('Pelanggan.userChart');
}
```

---

## 3. Bukti Verifikasi Pengujian (*Automated End-to-End Tests*)

Pengujian dilakukan menggunakan browser headless pada URL lokal `http://clone_pa_zyhcorner.test`:

### Skenario 1: Pengguna Berperan Admin (`admin@gmail.com`)
1. **Pemeriksaan Menu Dropdown Header**:
   - Link Profil: `Dashboard Admin` $\to$ mengarah ke `http://clone_pa_zyhcorner.test/admin/dashboard` *(Lolos - screenshot di `docs/admin_header_dropdown.png`)*.
2. **Pemeriksaan Tombol "Tambah ke Keranjang"**:
   - Return value: `false`.
   - Jumlah keranjang di memori: `0` (tidak bertambah).
   - Dialog SweetAlert2 muncul dengan judul: *"Akses Belanja Khusus Pelanggan"* dan teks edukasi mengarahkan ke POS Kasir *(Lolos - screenshot di `docs/admin_shopping_protection_alert.png`)*.
3. **Pemeriksaan Tombol "Beli Sekarang" pada Halaman Detail Produk (`/product/1`)**:
   - SweetAlert2 muncul seketika, browser tidak dialihkan ke `/checkout`, URL tetap pada `/product/1` *(Lolos - screenshot di `docs/admin_buy_now_protection.png`)*.
4. **Pemeriksaan Akses URL Langsung `/cart`**:
   - Ditahan dengan status **403 Forbidden**.
   - Halaman menampilkan pesan: *"Akses Ditolak: Halaman keranjang belanja hanya diperuntukkan bagi pelanggan toko."*
   - Tombol utama: *"Kembali ke Dashboard Admin"* mengarah ke `/admin/dashboard` *(Lolos - screenshot di `docs/admin_cart_403_access.png`)*.

### Skenario 2: Pengguna Berperan Pelanggan (`nama@gmail.com`)
1. **Pemeriksaan Menu Dropdown Header**:
   - Link Profil: `Dashboard` $\to$ mengarah ke `http://clone_pa_zyhcorner.test/dashboard` *(Lolos)*.
2. **Pemeriksaan Akses Halaman Keranjang (`/cart`)**:
   - Halaman terbuka sempurna (`hasCartContent: true`, `is403: false`) *(Lolos)*.
3. **Alur Belanja**:
   - Penambahan barang ke keranjang dan proses checkout berfungsi 100% normal tanpa hambatan.

---

## 4. Kesimpulan
Semua kriteria kebutuhan telah terpenuhi secara presisi:
- Tidak ada elemen visual yang disembunyikan atau dihapus.
- Peran Admin/Kasir terlindungi secara interaktif di sisi antarmuka dan diamankan di sisi server.
- Dropdown navigasi header beradaptasi secara cerdas sesuai hak akses pengguna.
