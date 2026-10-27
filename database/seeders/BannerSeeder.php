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
            // Home Hero Banner 1
            [
                'judul_banner' => 'Delicious & Fresh Cakes and Desserts',
                'subjudul' => 'Welcome to Zyh Corner - Sale up to 30% OFF',
                'gambar_banner' => 'banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png',
                'posisi_banner' => 'home_hero',
                'link_url' => '/shop',
                'urutan' => 1,
                'status' => 'Aktif',
            ],
            // Home Hero Banner 2 (Slideshow)
            [
                'judul_banner' => 'Special Artisanal Pastry & Bakery',
                'subjudul' => 'Fresh Every Day - Diskon Spesial 25% OFF',
                'gambar_banner' => 'banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png',
                'posisi_banner' => 'home_hero',
                'link_url' => '/shop',
                'urutan' => 2,
                'status' => 'Aktif',
            ],

            // Shop Header Banner
            [
                'judul_banner' => 'Sale of the Month - Potongan hingga 50%',
                'subjudul' => 'Best Deals - Potongan Spesial Bulan Ini',
                'gambar_banner' => 'banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png',
                'posisi_banner' => 'shop_header',
                'link_url' => '/shop',
                'urutan' => 1,
                'status' => 'Aktif',
            ],

            // Custom Header Banner
            [
                'judul_banner' => 'Kue Kustom Spesial Acara Anda',
                'subjudul' => 'Desain Kustom & Rasa Pilihan Terbaik',
                'gambar_banner' => 'banners/4KCToSkGBZDsUvU9Z8oBu13EhAQtABOn1oJhmOOa.png',
                'posisi_banner' => 'custom_header',
                'link_url' => '/custom-products',
                'urutan' => 1,
                'status' => 'Aktif',
            ],

            // Pop-up / Middle Promo Banner
            [
                'judul_banner' => 'Summer Sale 75% OFF',
                'subjudul' => 'Promo Diskon Terbatas Akhir Bulan',
                'gambar_banner' => 'banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png',
                'posisi_banner' => 'popup_promo',
                'link_url' => '/shop',
                'urutan' => 1,
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
