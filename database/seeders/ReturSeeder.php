<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Retur;
use Carbon\Carbon;

class ReturSeeder extends Seeder
{
    /**
     * Seed the returs (returns) table with dummy data.
     */
    public function run(): void
    {
        Retur::query()->delete();

        $returs = [
            [
                'customer_name' => 'Siti Nurhaliza',
                'order_id'      => '#ZYH001',
                'reason'        => 'Produk tidak sesuai dengan deskripsi',
                'return_date'   => Carbon::now()->subDays(28)->toDateString(),
                'product_name'  => 'Red Velvet Cake',
                'status'        => 'Completed',
            ],
            [
                'customer_name' => 'Budi Santoso',
                'order_id'      => '#ZYH003',
                'reason'        => 'Kue rusak saat pengiriman',
                'return_date'   => Carbon::now()->subDays(18)->toDateString(),
                'product_name'  => 'Chocolate Brownies',
                'status'        => 'Completed',
            ],
            [
                'customer_name' => 'Dewi Lestari',
                'order_id'      => '#ZYH013',
                'reason'        => 'Rasa tidak sesuai ekspektasi',
                'return_date'   => Carbon::now()->subDays(20)->toDateString(),
                'product_name'  => 'Cheese Cake Original',
                'status'        => 'Completed',
            ],
            [
                'customer_name' => 'Fajar Pratama',
                'order_id'      => '#ZYH007',
                'reason'        => 'Pesanan salah / tidak sesuai order',
                'return_date'   => Carbon::now()->subDays(13)->toDateString(),
                'product_name'  => 'Tiramisu Cake',
                'status'        => 'Processing',
            ],
            [
                'customer_name' => 'Hendra Gunawan',
                'order_id'      => '#ZYH010',
                'reason'        => 'Produk sudah mendekati tanggal expired',
                'return_date'   => Carbon::now()->subDays(3)->toDateString(),
                'product_name'  => 'Croissant Almond',
                'status'        => 'New',
            ],
            [
                'customer_name' => 'Rizky Ramadhan',
                'order_id'      => '#ZYH012',
                'reason'        => 'Kemasan terbuka / seal rusak',
                'return_date'   => Carbon::now()->subDays(16)->toDateString(),
                'product_name'  => 'Banana Cake',
                'status'        => 'Completed',
            ],
            [
                'customer_name' => 'Rina Wulandari',
                'order_id'      => '#ZYH006',
                'reason'        => 'Alergi terhadap bahan yang tidak tercantum',
                'return_date'   => Carbon::now()->subDays(4)->toDateString(),
                'product_name'  => 'Peanut Butter Cookie',
                'status'        => 'Processing',
            ],
            [
                'customer_name' => 'Putri Ayu',
                'order_id'      => '#ZYH018',
                'reason'        => 'Jumlah item kurang dari yang dipesan',
                'return_date'   => Carbon::now()->subDays(12)->toDateString(),
                'product_name'  => 'Donut Glazed',
                'status'        => 'Completed',
            ],
            [
                'customer_name' => 'Fajar Pratama',
                'order_id'      => '#ZYH008',
                'reason'        => 'Dekorasi kue tidak sesuai pesanan',
                'return_date'   => Carbon::now()->subDays(9)->toDateString(),
                'product_name'  => 'Birthday Cake Custom',
                'status'        => 'Rejected',
            ],
            [
                'customer_name' => 'Maya Sari',
                'order_id'      => '#ZYH011',
                'reason'        => 'Produk terlambat sampai dan sudah tidak layak',
                'return_date'   => Carbon::now()->subDays(1)->toDateString(),
                'product_name'  => 'Matcha Roll Cake',
                'status'        => 'New',
            ],
        ];

        foreach ($returs as $retur) {
            Retur::create($retur);
        }
    }
}
