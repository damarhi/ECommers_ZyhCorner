<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diskon;
use App\Models\Produk;
use Carbon\Carbon;

class DiskonSeeder extends Seeder
{
    public function run(): void
    {
        $produks = Produk::all();

        if ($produks->isEmpty()) {
            return;
        }

        $samplePromos = [
            [
                'nama_diskon' => 'Promo Spesial 20%',
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 20,
                'tanggal_mulai' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'status' => 'Aktif',
            ],
            [
                'nama_diskon' => 'Flash Sale Rp 15.000',
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 15000,
                'tanggal_mulai' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'status' => 'Aktif',
            ],
            [
                'nama_diskon' => 'Diskon Akhir Pekan 10%',
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 10,
                'tanggal_mulai' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'status' => 'Aktif',
            ],
            [
                'nama_diskon' => 'Promo Pelanggan Baru Rp 10.000',
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 10000,
                'tanggal_mulai' => Carbon::now()->subMonth()->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'status' => 'Nonaktif',
            ],
        ];

        foreach ($samplePromos as $index => $promo) {
            $produk = $produks[$index % $produks->count()];
            
            Diskon::firstOrCreate(
                [
                    'id_produk' => $produk->id_produk,
                    'nama_diskon' => $promo['nama_diskon'],
                ],
                array_merge($promo, [
                    'id_produk' => $produk->id_produk,
                ])
            );
        }
    }
}
