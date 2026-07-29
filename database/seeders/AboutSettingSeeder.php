<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutSetting;

class AboutSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutSetting::updateOrCreate(
            ['id_about' => 1],
            [
                'judul_utama' => '100% Trusted Organic Food Store',
                'subjudul_utama' => 'Toko Pastry & Dessert Terpercaya Sejak 2020',
                'gambar_tentang' => null,
                'cerita_singkat' => 'Zyh Corner didirikan dengan cinta dan semangat untuk menyajikan berbagai hidangan kue, pastry, dessert, dan minuman lezat yang diolah dengan bahan-bahan berkualitas tinggi dan higienis. Kami percaya bahwa setiap perayaan dan momen berharga layak dirayakan dengan kelezatan istimewa.',
                'visi' => 'Menjadi toko pastry & bakery pilihan utama yang menghadirkan kebahagiaan manis di setiap momen istimewa keluarga dan pelanggan.',
                'misi' => "1. Menjaga kualitas rasa, kebersihan, dan kesegaran bahan baku terbaik.\n2. Memberikan layanan ramah dan profesional bagi setiap pelanggan.\n3. Berinovasi menciptakan kreasi kue dan dessert yang unik dan menarik.",
                'jam_operasional' => 'Senin - Minggu: 08.00 - 21.00 WIB',
                'alamat_toko' => 'Jl. Jendral Sudirman No. 123, Kota Samarinda, Kalimantan Timur',
                'telepon' => '081234567890',
                'email' => 'info@zyhcorner.com',
                'link_maps' => 'https://maps.google.com',
                'foto_galeri_1' => null,
                'foto_galeri_2' => null,
                'foto_galeri_3' => null,
                'judul_fitur' => '100% Trusted Organic Food Store',
                'deskripsi_fitur' => 'Kami percaya bahwa produk berkualitas lahir dari dedikasi dan standar tinggi. Berikut adalah keunggulan utama yang kami berikan untuk kepuasan Anda.',
                'judul_delivery' => 'We Delivered, You Enjoy Your Order.',
                'deskripsi_delivery' => 'Tidak perlu khawatir kue rusak di jalan. Zyh Corner menyediakan kurir khusus dengan penanganan makanan profesional untuk memastikan pesanan Anda tiba dengan bentuk yang cantik dan rasa segar.',
                'poin_delivery_1' => 'Pengiriman cepat & aman dengan kurir khusus makanan',
                'poin_delivery_2' => 'Kemasan kokoh, higienis, dan ramah lingkungan',
                'poin_delivery_3' => 'Garansi penggantian penuh jika ada cacat dalam pengiriman',
                'judul_tim' => 'Our Awesome Team',
                'deskripsi_tim' => 'Koki pastry dan desainer dekorasi berbakat yang mendedikasikan waktu dan keahlian mereka untuk menciptakan kelezatan manis di Zyh Corner.',
                'judul_testimoni' => 'Client Testimonail',
                'deskripsi_testimoni' => 'Ulasan jujur dan cerita kebahagiaan dari para pelanggan setia kami yang telah merasakan produk dan layanan Zyh Corner.',
            ]
        );
    }
}
