<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'store_name',
                'value' => 'Zyh Corner',
                'label' => 'Nama Toko',
                'group' => 'general',
            ],
            [
                'key' => 'store_phone',
                'value' => '08123456789',
                'label' => 'Nomor WhatsApp / Telepon',
                'group' => 'contact',
            ],
            [
                'key' => 'store_email',
                'value' => 'contact@zyhcorner.com',
                'label' => 'Email Layanan Pelanggan',
                'group' => 'contact',
            ],
            [
                'key' => 'store_address',
                'value' => 'Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654',
                'label' => 'Alamat Lengkap Toko',
                'group' => 'contact',
            ],
            [
                'key' => 'store_hours',
                'value' => 'Senin - Minggu (08:00 - 21:00)',
                'label' => 'Jam Operasional Toko',
                'group' => 'general',
            ],
            [
                'key' => 'store_tagline',
                'value' => 'Pastry & Bakery Berkualitas',
                'label' => 'Slogan / Tagline Toko',
                'group' => 'general',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/zyhcorner',
                'label' => 'Instagram',
                'group' => 'social',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/zyhcorner',
                'label' => 'Facebook',
                'group' => 'social',
            ],
            [
                'key' => 'social_tiktok',
                'value' => 'https://tiktok.com/@zyhcorner',
                'label' => 'TikTok',
                'group' => 'social',
            ],
            [
                'key' => 'bank_name',
                'value' => 'Bank Central Asia (BCA)',
                'label' => 'Nama Bank Toko',
                'group' => 'payment',
            ],
            [
                'key' => 'bank_account_number',
                'value' => '8295-0192-38',
                'label' => 'Nomor Rekening Bank',
                'group' => 'payment',
            ],
            [
                'key' => 'bank_account_holder',
                'value' => 'ZYH CORNER PASTRY',
                'label' => 'Atas Nama Rekening',
                'group' => 'payment',
            ],
        ];

        foreach ($settings as $setting) {
            StoreSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        StoreSetting::clearCache();
    }
}
