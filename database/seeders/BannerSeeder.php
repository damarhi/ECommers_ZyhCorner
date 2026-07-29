<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample default banner images (create directory if needed)
        Storage::disk('public')->makeDirectory('banners');

        $banners = [
            // Home Hero Banners (4 Banners)
            [
                'judul_banner' => 'Delicious & Fresh Cakes and Desserts',
                'subjudul' => 'Welcome to Zyh Corner - Sale up to 30% OFF',
                'gambar_banner' => 'banners/hero_banner_1.jpg',
                'posisi_banner' => 'home_hero',
                'link_url' => '/shop',
                'urutan' => 1,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Kue Ulang Tahun & Spesial Anniversary',
                'subjudul' => 'Desain Anggun & Rasa Premium Zyh Corner',
                'gambar_banner' => 'banners/hero_banner_2.jpg',
                'posisi_banner' => 'home_hero',
                'link_url' => '/custom-products',
                'urutan' => 2,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Fresh Pastry & Croissant Dipanggang Setiap Hari',
                'subjudul' => 'Kelembutan Hakiki dengan Butter Impor Terbaik',
                'gambar_banner' => 'banners/hero_banner_3.jpg',
                'posisi_banner' => 'home_hero',
                'link_url' => '/shop',
                'urutan' => 3,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Diskon Promo Hemat Paket Catering & Pesta',
                'subjudul' => 'Hemat hingga 45% untuk Pemesanan Jumlah Besar',
                'gambar_banner' => 'banners/hero_banner_4.jpg',
                'posisi_banner' => 'home_hero',
                'link_url' => '/shop',
                'urutan' => 4,
                'status' => 'Aktif',
            ],

            // Shop Header Banners (2 Banners)
            [
                'judul_banner' => 'Sale of the Month - Potongan hingga 50%',
                'subjudul' => 'Best Deals - Potongan Spesial Bulan Ini',
                'gambar_banner' => 'banners/shop_header_banner.jpg',
                'posisi_banner' => 'shop_header',
                'link_url' => '/shop',
                'urutan' => 1,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Koleksi Best Seller Cake Terfavorit Minggu Ini',
                'subjudul' => 'Cobalah Cita Rasa Paling Populer di Zyh Corner',
                'gambar_banner' => 'banners/shop_header_banner_2.jpg',
                'posisi_banner' => 'shop_header',
                'link_url' => '/shop',
                'urutan' => 2,
                'status' => 'Aktif',
            ],

            // Custom Header Banners (2 Banners)
            [
                'judul_banner' => 'Kue Kustom Spesial Acara Anda',
                'subjudul' => 'Desain Kustom & Rasa Pilihan Terbaik',
                'gambar_banner' => 'banners/custom_header_banner.jpg',
                'posisi_banner' => 'custom_header',
                'link_url' => '/custom-products',
                'urutan' => 1,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Bebas Kustomisasi Bentuk, Warna & Ucapan',
                'subjudul' => 'Konsultasikan Ide Kue Impian Anda Secara Gratis',
                'gambar_banner' => 'banners/custom_header_banner_2.jpg',
                'posisi_banner' => 'custom_header',
                'link_url' => '/custom-products',
                'urutan' => 2,
                'status' => 'Aktif',
            ],

            // Pop-up / Middle Promo Banners (2 Banners)
            [
                'judul_banner' => 'Summer Sale 75% OFF',
                'subjudul' => 'Promo Diskon Terbatas Akhir Bulan',
                'gambar_banner' => 'banners/summer_sale_banner.jpg',
                'posisi_banner' => 'popup_promo',
                'link_url' => '/shop',
                'urutan' => 1,
                'status' => 'Aktif',
            ],
            [
                'judul_banner' => 'Voucher Potongan Ongkir Seluruh Wilayah',
                'subjudul' => 'Gunakan Kode Promo ZYHBEBASONGKIR Saat Checkout',
                'gambar_banner' => 'banners/summer_sale_banner_2.jpg',
                'posisi_banner' => 'popup_promo',
                'link_url' => '/shop',
                'urutan' => 2,
                'status' => 'Aktif',
            ],
        ];

        foreach ($banners as $b) {
            Banner::firstOrCreate(
                [
                    'judul_banner' => $b['judul_banner'],
                    'posisi_banner' => $b['posisi_banner'],
                ],
                $b
            );
        }
    }
}
