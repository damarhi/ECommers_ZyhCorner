# Laporan Eksekusi: Redesain Tombol Cek Ongkir & Implementasi Smart Dynamic Fallback Ongkir

Dokumen ini mendokumentasikan secara lengkap seluruh perbaikan logika backend, penataan ulang antarmuka tombol *"Cek Ongkir"*, kompilasi aset frontend, serta hasil verifikasi pengujian otomatis dan interaktif di halaman Checkout (`resources/views/Pelanggan/checkout.blade.php`).

---

## 1. Analisis Masalah & Akar Penyebab

### A. Penyebab Ongkos Kirim Sebelumnya Selalu Flat Rp. 20.000
1. **Saldo Akun Biteship Sandbox/API Habis**:
   Ketika request dikirim ke `https://api.biteship.com/v1/rates/couriers`, API resmi Biteship mengembalikan status HTTP `400 Bad Request` dengan pesan:
   ```json
   {"success": false, "error": "No sufficient balance to call rates API. Please top up your balance"}
   ```
2. **Fallback Lokal Di-hardcode Statis**:
   Pada implementasi awal `BiteshipService::formatCheckoutRates()`, saat panggilan API gagal, sistem mengembalikan tarif statis seragam untuk ketiga kurir:
   ```php
   $courierCosts = [
       'Gojek' => 20000,
       'Grab' => 20000,
       'Maxim' => 20000,
       'Pick Up' => 0,
   ];
   ```
   Tidak ada perbedaan harga antarkurir dan tidak ada penyesuaian jarak antarzona wilayah tujuan (misalnya tujuan Pangkalan Kerinci dalam kota vs Pekanbaru).

### B. Penyebab Tombol "Cek Ongkir" Sempat Tampak Abu-abu Bawaan Browser
Pada berkas template pelanggan, stylesheet di-*load* menggunakan modul bundle Vite (`@vite(['resources/css/pelanggan_pages.css', ...])`). Saat penambahan kelas CSS `.btn-refresh-shipping`, proses `npm run build` belum dijalankan kembali, sehingga peramban pengguna memuat file statis lama dari `public/build/assets/` yang belum memiliki aturan CSS tersebut. Akibatnya, peramban merendernya sebagai tombol abu-abu *default* (*browser user-agent buttonface*).

---

## 2. Rincian Perubahan Kode Sumber (Source Code Changes)

### A. `app/Services/BiteshipService.php`
Mengubah method `formatCheckoutRates` menjadi sistem **Smart Dynamic Tiering** berbasis zona wilayah tujuan dan karakteristik kurir:
```php
    /**
     * Map rates from Biteship API or standard fallback into Zyh Corner checkout courier options.
     *
     * @param array $ratesResult
     * @param string|null $destinationAreaId
     * @param string|null $destinationAreaName
     * @return array
     */
    public static function formatCheckoutRates(array $ratesResult, ?string $destinationAreaId = null, ?string $destinationAreaName = null): array
    {
        $destId = strtoupper((string) ($destinationAreaId ?? $ratesResult['destination_area_id'] ?? ''));
        $destName = strtolower((string) ($destinationAreaName ?? $ratesResult['destination_area_name'] ?? ''));

        // Default local base tier (Pangkalan Kerinci Kota - store origin area)
        // Differentiated realistic rates: Maxim (ekonomis), Gojek (standar), Grab (ekspres), Pick Up (gratis)
        $courierCosts = [
            'Gojek' => 18000,
            'Grab' => 20000,
            'Maxim' => 12000,
            'Pick Up' => 0,
        ];

        // If destination is in outer Pelalawan subdistricts
        if (in_array($destId, ['ID_PLW_04', 'ID_PLW_05', 'ID_PLW_06', 'ID_PLW_07', 'ID_PLW_08', 'ID_PLW_09', 'ID_PLW_10']) ||
            (str_contains($destName, 'pelalawan') && !str_contains($destName, 'kerinci kota') && !str_contains($destName, 'kerinci barat') && !str_contains($destName, 'kerinci timur'))) {
            $courierCosts = [
                'Gojek' => 28000,
                'Grab' => 30000,
                'Maxim' => 22000,
                'Pick Up' => 0,
            ];
        } elseif (str_starts_with($destId, 'ID_PKU') || str_contains($destName, 'pekanbaru')) {
            // Pekanbaru city zone
            $courierCosts = [
                'Gojek' => 42000,
                'Grab' => 45000,
                'Maxim' => 35000,
                'Pick Up' => 0,
            ];
        } elseif (!empty($destId) && !str_starts_with($destId, 'ID_PLW')) {
            // Other regencies in Riau or national
            $courierCosts = [
                'Gojek' => 48000,
                'Grab' => 50000,
                'Maxim' => 40000,
                'Pick Up' => 0,
            ];
        }

        // If live API returned valid pricing with positive amounts, override with real-time API values
        if (!empty($ratesResult['rates']) && ($ratesResult['source'] ?? '') === 'biteship_api') {
            foreach ($ratesResult['rates'] as $rate) {
                $comp = strtolower($rate['company'] ?? '');
                $courier = strtolower($rate['courier_name'] ?? '');
                $price = (int) ($rate['price'] ?? 0);

                if ($price > 0) {
                    if (str_contains($comp, 'gojek') || str_contains($courier, 'gojek') || str_contains($comp, 'gosend')) {
                        $courierCosts['Gojek'] = $price;
                    } elseif (str_contains($comp, 'grab') || str_contains($courier, 'grab')) {
                        $courierCosts['Grab'] = $price;
                    } elseif (str_contains($comp, 'maxim') || str_contains($courier, 'maxim')) {
                        $courierCosts['Maxim'] = $price;
                    }
                }
            }
        }

        return $courierCosts;
    }
```

---

### B. `app/Http/Controllers/Pelanggan/CartCheckoutController.php`
Meneruskan `$destinationAreaId` dan `$destinationAreaName` ke `BiteshipService::formatCheckoutRates`:
```php
    public function biteshipRates(Request $request)
    {
        $destinationAreaId = (string) $request->input('destination_area_id', '');
        $destinationAreaName = (string) $request->input('destination_area_name', '');
        $items = $request->input('items', []);

        if (empty($destinationAreaId) && !empty($destinationAreaName)) {
            $matchedAreas = \App\Services\BiteshipService::searchAreas($destinationAreaName);
            if (!empty($matchedAreas)) {
                $destinationAreaId = $matchedAreas[0]['id'] ?? '';
            }
        }

        if (empty($destinationAreaId)) {
            $destinationAreaId = config('services.biteship.origin_area_id', 'ID_PLW_01');
        }

        $ratesResult = \App\Services\BiteshipService::getRates($destinationAreaId, $items);
        $couriers = \App\Services\BiteshipService::formatCheckoutRates($ratesResult, $destinationAreaId, $destinationAreaName);

        return response()->json([
            'success' => true,
            'source' => $ratesResult['source'] ?? 'standard_tiers',
            'destination_area_id' => $destinationAreaId,
            'destination_area_name' => $destinationAreaName,
            'couriers' => $couriers,
            'rates' => $ratesResult['rates'] ?? []
        ]);
    }
```

---

### C. `resources/views/Pelanggan/checkout.blade.php`
1. **Penambahan Scoped Stylesheet pada `@section('styles')`**:
   Menyuntikkan styling langsung ke tag `<head>` agar tombol berpenampilan prima dan kebal terhadap *browser cache* atau dev-server yang belum terkompilasi:
   ```blade
   @section('styles')
   <style>
       .btn-refresh-shipping {
           display: inline-flex;
           align-items: center;
           gap: 7px;
           padding: 6px 14px;
           font-size: 12px;
           font-weight: 600;
           color: #00b207 !important;
           background-color: #ffffff !important;
           border: 1.5px solid #00b207 !important;
           border-radius: 8px !important;
           cursor: pointer;
           transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
           line-height: 1.2;
           box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
           text-decoration: none;
       }
       .btn-refresh-shipping:hover:not(:disabled) {
           background-color: #00b207 !important;
           color: #ffffff !important;
           box-shadow: 0 3px 10px rgba(0, 178, 7, 0.22);
           transform: translateY(-1px);
       }
       .btn-refresh-shipping:active:not(:disabled) {
           transform: translateY(0);
           box-shadow: 0 1px 3px rgba(0, 178, 7, 0.15);
       }
       .btn-refresh-shipping:disabled {
           opacity: 0.7;
           cursor: not-allowed;
           background-color: #f8fafc !important;
           border-color: #cbd5e1 !important;
           color: #64748b !important;
           box-shadow: none;
           transform: none;
       }
       .btn-refresh-shipping:focus-visible {
           outline: 2px solid #00b207;
           outline-offset: 2px;
       }
       .shipping-card-content-simple .shipping-cost-tag {
           margin-left: auto;
           font-size: 12px;
           font-weight: 600;
           color: #475569;
           background-color: #f1f5f9;
           padding: 3px 9px;
           border-radius: 6px;
           border: 1px solid #e2e8f0;
           transition: all 0.2s ease;
       }
       .shipping-card-option.active .shipping-card-content-simple .shipping-cost-tag {
           color: #00b207;
           background-color: #dcfce7;
           border-color: #bbf7d0;
       }
   </style>
   @endsection
   ```

2. **Penyelarasan Nilai Awal Markup Kurir**:
   - Maxim (default terpilih): `data-cost="12000"`, `Rp. 12.000`
   - Gojek: `data-cost="18000"`, `Rp. 18.000`
   - Grab: `data-cost="20000"`, `Rp. 20.000`
   - Pick Up: `data-cost="0"`, `Gratis`
   - Inisialisasi JS: `window.currentShippingCost = 12000;`

3. **Sinkronisasi Otomatis saat Halaman Selesai Dimuat (`DOMContentLoaded`)**:
   ```javascript
   document.addEventListener('DOMContentLoaded', function() {
       const destinationAreaId = document.getElementById('destinationAreaId')?.value;
       const destinationAreaName = document.getElementById('areaSearchInput')?.value;
       if (destinationAreaId || destinationAreaName) {
           if (typeof window.refreshShippingRates === 'function') {
               window.refreshShippingRates(true);
           }
       }
   });
   ```

---

### D. `resources/css/pelanggan_custom.css`
Memperbarui definisi kelas `.btn-refresh-shipping` dan `.shipping-cost-tag` dengan radius sudut `8px`, efek transisi bezier, padding yang seimbang, dan focus accessibility ring.

---

### E. Kompilasi Aset Statis dengan Vite (`npm run build`)
Menjalankan kompilasi penuh sehingga berkas produksi `public/build/assets/pelanggan_pages-BC87qZsQ.css` dan `public/build/manifest.json` memuat kelas visual terbaru.

---

## 3. Bukti Verifikasi Hasil Pengujian

### A. Pengujian Visual & Interaktif (Headless Chromium)
1. **Pemeriksaan Computed Style Tombol `#btnRefreshShipping`**:
   - `text`: *"Cek Ongkir"*
   - `color`: `rgb(0, 178, 7)` (Hijau khas Zyh Corner)
   - `backgroundColor`: `rgb(255, 255, 255)` (Putih bersih)
   - `border`: `1px solid rgb(0, 178, 7)`
   - `borderRadius`: `8px`
   - `fontSize`: `12px`, `fontWeight`: `600`
2. **Kalkulasi Tarif Berdasarkan Zona Alamat**:
   - **Tujuan Pangkalan Kerinci Kota (`ID_PLW_01`)**:
     - Maxim: `Rp. 12.000`
     - Gojek: `Rp. 18.000`
     - Grab: `Rp. 20.000`
     - Pick Up: `Rp. 0`
   - **Tujuan Pekanbaru (`ID_PKU_*`)**:
     - Maxim: `Rp. 35.000`
     - Gojek: `Rp. 42.000`
     - Grab: `Rp. 45.000`
     - Pick Up: `Rp. 0`
3. **Interaksi Klik Tombol**:
   - Menampilkan spinner berputar dan label *"Memuat..."*.
   - Tombol dinonaktifkan sementara (`disabled = true`).
   - Selesai dalam < 2 detik dan menampilkan SweetAlert toast: *"Tarif ongkir berhasil diperbarui"*.

### B. Pengujian Otomatis (PHPUnit Test Suite)
- Penambahan pengujian unit dan fitur pada `tests/Feature/BiteshipServiceTest.php`:
  - `test_biteship_rates_route_returns_json_and_couriers_mapping`: Lulus.
  - `test_biteship_rates_adapts_to_pekanbaru_zone`: Lulus.
  - `test_format_checkout_rates_maps_api_pricing_accurately`: Lulus.
  - `CheckoutDesignTest::test_checkout_page_renders_with_redesigned_layout_and_contracts`: Lulus.
- **Hasil Akhir Full Suite**: **198 tests passed (1064 assertions) &mdash; 100% Green**.
