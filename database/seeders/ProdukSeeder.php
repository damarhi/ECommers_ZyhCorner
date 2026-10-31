<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kueCake = Kategori::where('nama_kategori', 'Kue & Cake')->first();
        $rotiPastry = Kategori::where('nama_kategori', 'Roti & Pastry')->first();
        $dessert = Kategori::where('nama_kategori', 'Dessert & Buah Segar')->first();
        $kustom = Kategori::where('nama_kategori', 'Produk Kustom & Gift')->first();
        $snack = Kategori::where('nama_kategori', 'Cemilan & Snack')->first();

        $products = [
            // Kategori: Kue & Cake
            [
                'id_kategori' => $kueCake ? $kueCake->id_kategori : null,
                'nama_produk' => 'Black Forest Cake Classic',
                'harga' => 125000,
                'status_produk' => 'Tersedia',
                 'deskripsi' => 'Kue bolu cokelat klasik khas Jerman dengan lapisan krim segar dan buah ceri manis yang lembut.',
                 'bisa_custom_teks' => true,
                 'jenis_produk' => 'showcase',
                'is_signature' => true,
                 'tanggal_expired' => now()->addDays(7),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => null,
                'foto_3' => null,
                'foto_4' => null,
            ],
            [
                'id_kategori' => $kueCake ? $kueCake->id_kategori : null,
                'nama_produk' => 'Red Velvet Special Cream Cheese',
                'harga' => 140000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Kue red velvet lembut dengan lapisan krim keju premium yang gurih, wangi dan manis seimbang.',
                'bisa_custom_teks' => true,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(6),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => null,
                'foto_4' => null,
            ],
            [
                'id_kategori' => $kueCake ? $kueCake->id_kategori : null,
                'nama_produk' => 'New York Baked Cheesecake',
                'harga' => 150000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Cheesecake panggang otentik dengan tekstur lembut, padat dan lumer di mulut berbalut remah biskuit mentega.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(5),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => null,
            ],
            [
                'id_kategori' => $kueCake ? $kueCake->id_kategori : null,
                'nama_produk' => 'Matcha Green Tea Layer Cake',
                'harga' => 135000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Kue lapis teh hijau matcha Jepang asli dengan aroma menenangkan dan manis yang pas di setiap gigitan.',
                'bisa_custom_teks' => true,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(7),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => 'products/about_cake_showcase.png',
            ],

            // Kategori: Roti & Pastry
            [
                'id_kategori' => $rotiPastry ? $rotiPastry->id_kategori : null,
                'nama_produk' => 'Butter Croissant Paris',
                'harga' => 22000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Croissant renyah berlapis dengan mentega Prancis aromatik berkualitas tinggi, renyah di luar dan lembut di dalam.',
                 'bisa_custom_teks' => false,
                 'jenis_produk' => 'showcase',
                'is_signature' => true,
                 'tanggal_expired' => now()->addDays(3),
                'foto_2' => null,
                'foto_3' => null,
                'foto_4' => null,
            ],
            [
                'id_kategori' => $rotiPastry ? $rotiPastry->id_kategori : null,
                'nama_produk' => 'Pain Au Chocolat',
                'harga' => 26000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Pastry renyah dengan isian cokelat hitam leleh premium di bagian tengahnya.',
                 'bisa_custom_teks' => false,
                 'jenis_produk' => 'showcase',
                'is_signature' => true,
                 'tanggal_expired' => now()->addDays(3),
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => null,
                'foto_4' => null,
            ],
            [
                'id_kategori' => $rotiPastry ? $rotiPastry->id_kategori : null,
                'nama_produk' => 'Almond Danish Pastry',
                'harga' => 28000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Pastry manis dengan krim custard lembut dan taburan kacang almond renyah yang dipanggang keemasan.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(3),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => null,
            ],

            // Kategori: Dessert & Buah Segar
            [
                'id_kategori' => $dessert ? $dessert->id_kategori : null,
                'nama_produk' => 'Tiramisu Cup Tradizionale',
                'harga' => 38000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Dessert khas Italia dalam cup dengan biskuit kopi ladyfinger dan keju mascarpone bertabur bubuk cokelat kakao murni.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(4),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => 'products/about_cake_showcase.png',
            ],
            [
                'id_kategori' => $dessert ? $dessert->id_kategori : null,
                'nama_produk' => 'Fresh Fruit Tartlet',
                'harga' => 32000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Tartlet mini renyah diisi krim vanilla dan dihias aneka potongan buah segar musiman segar.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(2),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => null,
                'foto_3' => null,
                'foto_4' => null,
            ],

            // Kategori: Produk Kustom & Gift
            [
                'id_kategori' => $kustom ? $kustom->id_kategori : null,
                'nama_produk' => 'Custom Birthday Cake Tingkat',
                'harga' => 250000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Kue ulang tahun bertingkat dengan desain kustom, pilihan warna tema, dan tulisan ucapan sesuai permintaan Anda.',
                'bisa_custom_teks' => true,
                'jenis_produk' => 'kustom',
                'tanggal_expired' => now()->addDays(7),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => null,
                'foto_4' => null,
            ],
            [
                'id_kategori' => $kustom ? $kustom->id_kategori : null,
                'nama_produk' => 'Custom Cupcake Box Hampers',
                'harga' => 110000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Satu kotak hampers isi 6 cupcake lezat berhiaskan ornamen kustom dan kartu ucapan spesial.',
                'bisa_custom_teks' => true,
                'jenis_produk' => 'kustom',
                'tanggal_expired' => now()->addDays(5),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => null,
            ],

            // Kategori: Cemilan & Snack
            [
                'id_kategori' => $snack ? $snack->id_kategori : null,
                'nama_produk' => 'Cheese Sago Cookies',
                'harga' => 45000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Kue kering sagu keju renyah lumer di mulut dengan parutan keju edam gurih khas Zyh Corner.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(30),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => 'products/about_cake_showcase.png',
            ],
            [
                'id_kategori' => $snack ? $snack->id_kategori : null,
                'nama_produk' => 'Chocolate Almond Biscotti',
                'harga' => 40000,
                'status_produk' => 'Tersedia',
                'deskripsi' => 'Biskuit garing khas Italia panggang ganda dengan potongan almond renyah dan cokelat lezat, cocok untuk teman kopi.',
                'bisa_custom_teks' => false,
                'jenis_produk' => 'showcase',
                'tanggal_expired' => now()->addDays(30),
                'foto_1' => 'products/about_cake_coffee.png',
                'foto_2' => 'products/about_cake_showcase.png',
                'foto_3' => 'products/about_cake_coffee.png',
                'foto_4' => 'products/about_cake_showcase.png',
            ],
        ];

        foreach ($products as $data) {
            Produk::updateOrCreate(
                ['nama_produk' => $data['nama_produk']],
                $data
            );
        }
    }
}
