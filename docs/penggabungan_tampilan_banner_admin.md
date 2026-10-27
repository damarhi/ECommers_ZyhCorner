# Dokumentasi Penggabungan Tampilan Banner dalam Satu Halaman Kelola Banner

Dokumentasi ini mencatat perubahan pada modul kelola banner admin (`resources/views/Admin/kelola_carousel/banners.blade.php`, `app/Http/Controllers/Admin/BannerController.php`, `database/seeders/BannerSeeder.php`, dan `tests/Feature/AdminBannerTest.php`) untuk menyatukan seluruh kategori banner pelanggan dalam satu grid tampilan tanpa pemisahan tab, serta memastikan seluruh data posisi banner (`home_hero`, `shop_header`, `custom_header`, dan `popup_promo`) terisi dan terpelihara dengan baik.

---

## 1. Latar Belakang Masalah

1. **Keluhan Awal Pengguna:**  
   Pada halaman kelola banner admin (`/admin/banners`), banner kategori Shop (`shop_header`), Custom Produk (`custom_header`), dan Discount (`popup_promo`) sempat tidak terlihat, menyisakan hanya satu banner `home_hero`. Hal ini disebabkan oleh:
   - Pemangkasan data pada `BannerSeeder.php` di siklus pengerjaan sebelumnya.
   - Ketidakjelasan status filter posisi pada antarmuka pengguna admin.
2. **Koreksi Terhadap Pemisahan Tab:**  
   Penyelesaian awal sempat menambahkan bilah navigasi tab/pill kategori di bagian atas toolbar. Pengguna menginginkan antarmuka **tidak dipecah menjadi tab-tab terpisah**, melainkan **seluruh banner dari semua posisi digabungkan langsung dalam satu halaman/grid daftar banner** seperti format awal, dengan badge penanda posisi pada masing-masing kartu banner.

---

## 2. Rincian Perubahan Kode

### A. Tampilan Admin: `resources/views/Admin/kelola_carousel/banners.blade.php`

1. **Menghapus Baris Tab/Pill Kategori:**  
   Menghapus elemen `<div class="d-flex flex-wrap gap-2 mb-3">...</div>` yang sebelumnya menampilkan tombol tab terpisah (`Semua Posisi`, `Home Hero`, `Header Showcase`, `Header Kustom`, `Pop-up Promo`).
2. **Menghapus Alert Filter Mengambang:**  
   Menghapus elemen `<div class="alert alert-info ...">` agar tata letak kembali ringkas dan tidak memakan ruang vertikal.
3. **Menyatukan Grid Banner Bersama:**  
   Seluruh banner dari semua posisi kini tampil langsung di dalam loop `@forelse($banners as $b)` dalam format grid responsif 3-kolom (`col-xl-4`):
   - **Home Hero Banner** (Beranda Utama)
   - **Header Showcase** (Halaman Shop)
   - **Header Produk Kustom** (Halaman Custom Products)
   - **Pop-up Promo / Diskon** (Promo Spesial)
4. **Badge Posisi Visual Pada Setiap Kartu:**  
   Setiap kartu banner dilengkapi badge posisi di sudut kiri atas:
   ```blade
   <div class="position-absolute top-0 start-0 m-3">
       <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 shadow-sm" style="border-radius: 20px; font-size: 11.5px; backdrop-filter: blur(4px);">
           <i class="mdi mdi-pin me-1 text-warning"></i> {{ $b->posisi_label }}
       </span>
   </div>
   ```
5. **Toolbar & Filter Dropdown Opsional:**  
   Dropdown filter posisi tetap dipertahankan di dalam toolbar sebagai alat saring opsional jika admin ingin memfilter secara spesifik, dengan pilihan default *"Semua Posisi"* saat halaman pertama kali dimuat.

---

### B. Controller Admin: `app/Http/Controllers/Admin/BannerController.php`

Menjaga efisiensi method `index()` agar mengambil seluruh data banner secara default:

```php
public function index(Request $request)
{
    $query = Banner::query();

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('judul_banner', 'like', '%' . $search . '%')
              ->orWhere('subjudul', 'like', '%' . $search . '%');
        });
    }

    if ($request->filled('posisi')) {
        $query->where('posisi_banner', $request->input('posisi'));
    }

    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    $banners = $query->orderBy('urutan', 'asc')->latest()->paginate(10)->withQueryString();

    // Rangkuman Statistik KPI
    $totalBanners = Banner::count();
    $totalAktif = Banner::where('status', 'Aktif')->count();
    $totalPosisi = Banner::distinct('posisi_banner')->count('posisi_banner');

    return view('Admin.kelola_carousel.banners', compact(
        'banners',
        'totalBanners',
        'totalAktif',
        'totalPosisi'
    ));
}
```

---

### C. Seeder Basis Data: `database/seeders/BannerSeeder.php`

Memastikan data default untuk seluruh 4 kategori banner selalu tersedia secara idempoten dengan aset file gambar yang valid di disk storage (`public/storage/banners/`):

1. **Home Hero Banner (`home_hero`)**: `"Delicious & Fresh Cakes and Desserts"` (`banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png`)
2. **Shop Header Banner (`shop_header`)**: `"Sale of the Month - Potongan hingga 50%"` (`banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png`)
3. **Custom Header Banner (`custom_header`)**: `"Kue Kustom Spesial Acara Anda"` (`banners/4KCToSkGBZDsUvU9Z8oBu13EhAQtABOn1oJhmOOa.png`)
4. **Popup Promo Banner (`popup_promo`)**: `"Summer Sale 75% OFF"` (`banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png`)

---

### D. Pengujian Otomatis: `tests/Feature/AdminBannerTest.php`

Menambahkan dan menyesuaikan test suite untuk memvalidasi:
- Penayangan seluruh kategori posisi banner secara bersamaan dalam satu grid tunggal tanpa tab.
- Operasi fungsionalitas CRUD, toggle status, bulk delete, dan penyaringan opsional via dropdown.

```php
public function test_banners_page_displays_all_positions_in_unified_grid(): void
{
    $admin = User::factory()->create(['role' => 'admin']);

    Banner::create(['judul_banner' => 'Hero Banner 1', 'gambar_banner' => 'banners/h1.jpg', 'posisi_banner' => 'home_hero', 'status' => 'Aktif']);
    Banner::create(['judul_banner' => 'Shop Banner 1', 'gambar_banner' => 'banners/s1.jpg', 'posisi_banner' => 'shop_header', 'status' => 'Aktif']);
    Banner::create(['judul_banner' => 'Custom Banner 1', 'gambar_banner' => 'banners/c1.jpg', 'posisi_banner' => 'custom_header', 'status' => 'Aktif']);
    Banner::create(['judul_banner' => 'Promo Banner 1', 'gambar_banner' => 'banners/p1.jpg', 'posisi_banner' => 'popup_promo', 'status' => 'Aktif']);

    $response = $this->actingAs($admin)->get(route('banners.index'));

    $response->assertStatus(200);
    $response->assertSee('Hero Banner 1');
    $response->assertSee('Shop Banner 1');
    $response->assertSee('Custom Banner 1');
    $response->assertSee('Promo Banner 1');
    $response->assertSee('Home Hero Banner');
    $response->assertSee('Header Produk Showcase');
    $response->assertSee('Header Produk Kustom');
    $response->assertSee('Pop-up Promo');
}
```

---

## 3. Hasil Pengujian & Verifikasi

1. **Eksekusi Automated Tests:**
   ```
   php artisan test tests/Feature/AdminBannerTest.php
   
   PASS  Tests\Feature\AdminBannerTest
   ✓ admin can access banners page
   ✓ admin can create banner with image
   ✓ admin can update banner
   ✓ admin can toggle banner status
   ✓ admin can delete banner
   ✓ admin can bulk delete banners
   ✓ banners page displays all positions in unified grid
   ✓ banners page filters by position

   Tests: 8 passed (31 assertions)
   ```

2. **Verifikasi Output Render HTML:**
   - Bilah tab (`Quick Category Filter Pills`) dipastikan **tidak ada** (`NO`).
   - Seluruh banner dari 4 posisi tampil bersamaan:
     - `Delicious & Fresh Cakes and Desserts` (`home_hero`) -> **YES**
     - `Sale of the Month - Potongan hingga 50%` (`shop_header`) -> **YES**
     - `Kue Kustom Spesial Acara Anda` (`custom_header`) -> **YES**
     - `Summer Sale 75% OFF` (`popup_promo`) -> **YES**
   - Halaman admin `/admin/banners` merespons dengan status `200 OK`.
