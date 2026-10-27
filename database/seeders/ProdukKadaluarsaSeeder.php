<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProdukKadaluarsa;
use App\Models\Produk;
use Carbon\Carbon;

class ProdukKadaluarsaSeeder extends Seeder
{
    /**
     * Seed the produk_kadaluarsas table with realistic expired pastry & bakery records.
     */
    public function run(): void
    {
        $produks = Produk::all();

        if ($produks->isEmpty()) {
            return;
        }

        $records = [
            [
                'offset_index'    => 5, // Butter Croissant
                'tanggal_expired' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'jumlah_stok'     => 4,
                'status_tindakan' => 'Perlu Tindakan',
                'catatan'         => 'Croissant batch pagi kemarin tidak terjual, perlu dipindahkan dari rak display.',
            ],
            [
                'offset_index'    => 6, // Almond Danish Pastry
                'tanggal_expired' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'jumlah_stok'     => 3,
                'status_tindakan' => 'Dimusnahkan',
                'catatan'         => 'Sudah dimusnahkan dan dicatat pada log pembuangan limbah pastry.',
            ],
            [
                'offset_index'    => 8, // Fresh Fruit Tartlet
                'tanggal_expired' => Carbon::now()->format('Y-m-d'),
                'jumlah_stok'     => 2,
                'status_tindakan' => 'Perlu Tindakan',
                'catatan'         => 'Buah segar mencapai batas konsumsi optimal hari ini, segera lakukan penarikan.',
            ],
            [
                'offset_index'    => 9, // Garlic Cheese Bread
                'tanggal_expired' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'jumlah_stok'     => 5,
                'status_tindakan' => 'Diarsipkan',
                'catatan'         => 'Telah dicatat sebagai evaluasi penurunan kuota produksi harian.',
            ],
            [
                'offset_index'    => 0, // Black Forest Cake Classic
                'tanggal_expired' => Carbon::now()->subDays(4)->format('Y-m-d'),
                'jumlah_stok'     => 1,
                'status_tindakan' => 'Diretur',
                'catatan'         => 'Bahan krim dari supplier memiliki aroma asam sebelum masa tanggal expired.',
            ],
        ];

        foreach ($records as $item) {
            $product = $produks[$item['offset_index'] % $produks->count()];

            ProdukKadaluarsa::firstOrCreate(
                [
                    'id_produk'       => $product->id_produk,
                    'tanggal_expired' => $item['tanggal_expired'],
                ],
                [
                    'id_produk'       => $product->id_produk,
                    'tanggal_expired' => $item['tanggal_expired'],
                    'jumlah_stok'     => $item['jumlah_stok'],
                    'status_tindakan' => $item['status_tindakan'],
                    'catatan'         => $item['catatan'],
                ]
            );
        }
    }
}
