# Dokumentasi Perbaikan Isolasi Modal Preview & Grid Banner Admin

Dokumentasi ini mencatat diagnosa akar masalah, perbaikan kode, dan pengujian terkait bug tampilan pada modul kelola banner admin (`resources/views/Admin/kelola_carousel/banners.blade.php`), di mana sebelumnya halaman kelola banner hanya menampilkan 1 kartu banner dan modal preview menampilkan konten bertumpuk/rusak saat dibuka.

---

## 1. Analisis Akar Masalah (Root Cause)

### A. Gejala yang Ditemukan
1. Halaman admin `/admin/banners` dengan filter default ("Semua Posisi") hanya merender 1 kartu banner (*Delicious & Fresh Cakes and Desserts* - `home_hero`), padahal di database SQLite terdapat 4 banner aktif lengkap.
2. Ketika tombol preview (ikon mata) pada banner #1 diklik, modal preview terbuka dengan tampilan kacau: seluruh kartu banner lain (Shop, Custom, Promo) beserta form edit, tombol hapus, dan kontrol aksi bersarang di dalam jendela modal preview tersebut.

### B. Penyebab Teknis (HTML Tag Imbalance)
Pada file `resources/views/Admin/kelola_carousel/banners.blade.php`, di dalam modal preview `#previewBannerModal{{ $b->id_banner }}`:
- Baris 245 membuka kontainer pratinjau:
  ```html
  <div class="p-3 bg-light rounded-3 mb-3 border">
  ```
- Di dalam kontainer tersebut terdapat percabangan visual banner (`@if($b->posisi_banner === 'home_hero') ... @endif`).
- Namun, setelah baris 332 (`@endif`), **tag penutup `</div>` untuk kontainer baris 245 tersebut tidak ada (hilang)**.
- Akibatnya, tag penutup di baris 352 yang seharusnya menutup modal `<div class="modal fade" id="previewBannerModal{{ $b->id_banner }}">` malah dipakai untuk menutup elemen kontainer di dalamnya.
- **Efek Domino pada DOM Tree:**
  1. Elemen modal `#previewBannerModal1` **tetap terbuka** sepanjang sisa eksekusi perulangan `@forelse($banners as $b)`.
  2. Modal edit `#editBannerModal1`, kartu Banner #5 (Shop), modal Banner #5, kartu Banner #7 (Custom), dan kartu Banner #9 (Promo) otomatis **bersarang (nested) di dalam modal `#previewBannerModal1`**.
  3. Karena modal Bootstrap memiliki CSS default `display: none;`, browser menyembunyikan semua elemen di dalamnya dari halaman utama. Hanya Banner #1 yang berada di luar modal sehingga hanya Banner #1 yang terlihat di grid.
  4. Ketika pengguna membuka preview modal Banner #1, modal beralih ke `display: block;`, sehingga seluruh elemen yang terperangkap di dalamnya ikut terekspos dalam satu tampilan jendela yang berantakan.

---

## 2. Rincian Perubahan Kode

### A. Template Tampilan: `resources/views/Admin/kelola_carousel/banners.blade.php`

Menambahkan tag penutup `</div>` yang hilang tepat setelah blok `@endif` pada baris 333:

```blade
<<<< SEBELUM:
                                        @else
                                            <div class="p-4 text-start text-white shadow-sm d-flex flex-column justify-content-center position-relative overflow-hidden"
                                                style="border-radius: 18px; min-height: 200px; background-image: linear-gradient(90deg, rgba(26, 13, 8, 0.88) 0%, rgba(26, 13, 8, 0.65) 50%, rgba(26, 13, 8, 0.25) 100%), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                                                <div style="max-width: 70%; z-index: 2;">
                                                    <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 mb-2 rounded-pill shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                                                        <i class="mdi {{ $b->posisi_banner === 'custom_header' ? 'mdi-cake-variant' : 'mdi-tag' }} me-1"></i> {{ $b->subjudul ?? ($b->posisi_banner === 'custom_header' ? 'Custom Bakery Studio' : 'Exclusive Pastry') }}
                                                    </span>
                                                    <h3 class="font-weight-bold text-white mb-2">{{ $b->judul_banner }}</h3>
                                                    <span class="btn btn-sm text-white rounded-pill px-4 mt-2 shadow-sm" style="background: linear-gradient(135deg, #00b207, #009906); font-weight: 700; font-size: 12.5px;">
                                                        {{ $b->posisi_banner === 'custom_header' ? 'Pesan Sekarang' : 'Shop Now' }} <i class="mdi mdi-arrow-right ms-1"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                    <div class="d-flex align-items-center justify-content-between mt-3 px-1">
==== SESUDAH:
                                        @else
                                            <div class="p-4 text-start text-white shadow-sm d-flex flex-column justify-content-center position-relative overflow-hidden"
                                                style="border-radius: 18px; min-height: 200px; background-image: linear-gradient(90deg, rgba(26, 13, 8, 0.88) 0%, rgba(26, 13, 8, 0.65) 50%, rgba(26, 13, 8, 0.25) 100%), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                                                <div style="max-width: 70%; z-index: 2;">
                                                    <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 mb-2 rounded-pill shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                                                        <i class="mdi {{ $b->posisi_banner === 'custom_header' ? 'mdi-cake-variant' : 'mdi-tag' }} me-1"></i> {{ $b->subjudul ?? ($b->posisi_banner === 'custom_header' ? 'Custom Bakery Studio' : 'Exclusive Pastry') }}
                                                    </span>
                                                    <h3 class="font-weight-bold text-white mb-2">{{ $b->judul_banner }}</h3>
                                                    <span class="btn btn-sm text-white rounded-pill px-4 mt-2 shadow-sm" style="background: linear-gradient(135deg, #00b207, #009906); font-weight: 700; font-size: 12.5px;">
                                                        {{ $b->posisi_banner === 'custom_header' ? 'Pesan Sekarang' : 'Shop Now' }} <i class="mdi mdi-arrow-right ms-1"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div> <!-- Menutup <div class="p-3 bg-light rounded-3 mb-3 border"> baris 245 -->

                                    <div class="d-flex align-items-center justify-content-between mt-3 px-1">
>>>>
```

Dengan penambahan tag tersebut:
- Kontainer visual tertutup rapi pada baris 333.
- Baris 349 menutup `modal-body`.
- Baris 350 menutup `modal-content`.
- Baris 351 menutup `modal-dialog`.
- Baris 352 menutup `<div class="modal fade" id="previewBannerModal{{ $b->id_banner }}">`.
- Seluruh kartu banner dan modal berikutnya kini berada di level yang tepat pada hierarki grid utama.

---

### B. Uji Regresi Otomatis: `tests/Feature/AdminBannerTest.php`

Menambahkan pengujian DOM `test_modals_are_properly_isolated_and_not_nested()` untuk menggaransi bahwa bug bersarangnya modal dan hilangnya kartu banner di grid tidak akan terulang di masa mendatang:

```php
public function test_modals_are_properly_isolated_and_not_nested(): void
{
    $admin = User::factory()->create(['role' => 'admin']);

    $b1 = Banner::create(['judul_banner' => 'Banner A', 'gambar_banner' => 'banners/a.jpg', 'posisi_banner' => 'home_hero', 'status' => 'Aktif']);
    $b2 = Banner::create(['judul_banner' => 'Banner B', 'gambar_banner' => 'banners/b.jpg', 'posisi_banner' => 'shop_header', 'status' => 'Aktif']);

    $response = $this->actingAs($admin)->get(route('banners.index'));

    $response->assertStatus(200);
    $html = $response->getContent();

    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $modalA = $xpath->query('//div[@id="previewBannerModal' . $b1->id_banner . '"]')->item(0);
    $this->assertNotNull($modalA);

    // Pastikan modal Banner B tidak bersarang di dalam modal Banner A
    $nestedModalB = $xpath->query('.//div[@id="previewBannerModal' . $b2->id_banner . '"]', $modalA);
    $this->assertEquals(0, $nestedModalB->length, 'Preview modal B must not be nested inside preview modal A');

    // Pastikan tidak ada kartu banner yang terperangkap di dalam modal
    $trappedCards = $xpath->query('.//div[contains(@class, "banner-card")]', $modalA);
    $this->assertEquals(0, $trappedCards->length, 'No banner card should be nested inside a preview modal');

    // Pastikan kedua kartu banner tampil di level grid utama
    $allCards = $xpath->query('//div[contains(@class, "banner-card")]');
    $this->assertEquals(2, $allCards->length);
}
```

---

## 3. Hasil Verifikasi dan Validasi

### A. Verifikasi Struktur DOM (Tinker)
```
Total visible banner cards in grid: 4
Is previewBannerModal5 inside previewBannerModal1? NO
Is editBannerModal1 inside previewBannerModal1? NO
Number of banner cards inside previewBannerModal1: 0

Card #1: [Home Hero Banner] Delicious & Fresh Cakes and Desserts
Card #2: [Header Produk Showcase] Sale of the Month - Potongan hingga 50%
Card #3: [Header Produk Kustom] Kue Kustom Spesial Acara Anda
Card #4: [Pop-up Promo] Summer Sale 75% OFF
```

### B. Hasil Uji PHPUnit Feature Test
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
✓ modals are properly isolated and not nested

Tests: 9 passed (36 assertions)
```
Semua kartu banner kini tampil berdampingan secara responsif, dan setiap modal preview berdiri sendiri secara terisolasi tanpa saling tumpang tindih.
