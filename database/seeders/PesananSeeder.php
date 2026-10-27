<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pesanan;
use Carbon\Carbon;

class PesananSeeder extends Seeder
{
    /**
     * Seed the pesanans (orders/sales) table with dummy data.
     * Covers both Online and Offline sales types with various statuses.
     */
    public function run(): void
    {
        Pesanan::query()->delete();

        $pesanans = [
            // ── Online Orders ──────────────────────────────────────
            [
                'order_number'    => '#ZYH001',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Siti Nurhaliza',
                'email_pembeli'   => 'siti.nurhaliza@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 185000.00,
                'created_at'      => Carbon::now()->subDays(30),
            ],
            [
                'order_number'    => '#ZYH002',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Siti Nurhaliza',
                'email_pembeli'   => 'siti.nurhaliza@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 95000.00,
                'created_at'      => Carbon::now()->subDays(25),
            ],
            [
                'order_number'    => '#ZYH003',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Budi Santoso',
                'email_pembeli'   => 'budi.santoso@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'delivered',
                'total'           => 120000.00,
                'created_at'      => Carbon::now()->subDays(20),
            ],
            [
                'order_number'    => '#ZYH004',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Dewi Lestari',
                'email_pembeli'   => 'dewi.lestari@gmail.com',
                'payment_status'  => 'Pending',
                'order_status'    => 'menunggu pembayaran',
                'total'           => 250000.00,
                'created_at'      => Carbon::now()->subDays(2),
            ],
            [
                'order_number'    => '#ZYH005',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Ahmad Hidayat',
                'email_pembeli'   => 'ahmad.hidayat@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'proses',
                'total'           => 75000.00,
                'created_at'      => Carbon::now()->subDays(3),
            ],
            [
                'order_number'    => '#ZYH006',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Rina Wulandari',
                'email_pembeli'   => 'rina.wulandari@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'on the way',
                'total'           => 160000.00,
                'created_at'      => Carbon::now()->subDays(5),
            ],
            [
                'order_number'    => '#ZYH007',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Fajar Pratama',
                'email_pembeli'   => 'fajar.pratama@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 340000.00,
                'created_at'      => Carbon::now()->subDays(15),
            ],
            [
                'order_number'    => '#ZYH008',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Fajar Pratama',
                'email_pembeli'   => 'fajar.pratama@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'delivered',
                'total'           => 210000.00,
                'created_at'      => Carbon::now()->subDays(10),
            ],
            [
                'order_number'    => '#ZYH009',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Lina Marlina',
                'email_pembeli'   => 'lina.marlina@gmail.com',
                'payment_status'  => 'Cancelled',
                'order_status'    => 'batal',
                'total'           => 85000.00,
                'created_at'      => Carbon::now()->subDays(8),
            ],
            [
                'order_number'    => '#ZYH010',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Hendra Gunawan',
                'email_pembeli'   => 'hendra.gunawan@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'sedang dikirim',
                'total'           => 135000.00,
                'created_at'      => Carbon::now()->subDays(4),
            ],
            [
                'order_number'    => '#ZYH011',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Maya Sari',
                'email_pembeli'   => 'maya.sari@gmail.com',
                'payment_status'  => 'Pending',
                'order_status'    => 'ready',
                'total'           => 45000.00,
                'created_at'      => Carbon::now()->subDays(1),
            ],
            [
                'order_number'    => '#ZYH012',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Rizky Ramadhan',
                'email_pembeli'   => 'rizky.ramadhan@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 290000.00,
                'created_at'      => Carbon::now()->subDays(18),
            ],
            [
                'order_number'    => '#ZYH013',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Dewi Lestari',
                'email_pembeli'   => 'dewi.lestari@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 175000.00,
                'created_at'      => Carbon::now()->subDays(22),
            ],
            [
                'order_number'    => '#ZYH014',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Budi Santoso',
                'email_pembeli'   => 'budi.santoso@gmail.com',
                'payment_status'  => 'Pending',
                'order_status'    => 'menunggu pembayaran',
                'total'           => 65000.00,
                'created_at'      => Carbon::now()->subHours(6),
            ],
            [
                'order_number'    => '#ZYH015',
                'jenis_penjualan' => 'Online',
                'customer_name'   => 'Siti Nurhaliza',
                'email_pembeli'   => 'siti.nurhaliza@gmail.com',
                'payment_status'  => 'Paid',
                'order_status'    => 'proses',
                'total'           => 320000.00,
                'created_at'      => Carbon::now()->subDays(1),
            ],

            // ── Offline (POS) Orders ──────────────────────────────
            [
                'order_number'    => '#ZYH016',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Walk-in Customer',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 55000.00,
                'created_at'      => Carbon::now()->subDays(28),
            ],
            [
                'order_number'    => '#ZYH017',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Walk-in Customer',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 30000.00,
                'created_at'      => Carbon::now()->subDays(27),
            ],
            [
                'order_number'    => '#ZYH018',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Putri Ayu',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 145000.00,
                'created_at'      => Carbon::now()->subDays(14),
            ],
            [
                'order_number'    => '#ZYH019',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Eko Prasetyo',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 90000.00,
                'created_at'      => Carbon::now()->subDays(12),
            ],
            [
                'order_number'    => '#ZYH020',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Walk-in Customer',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 40000.00,
                'created_at'      => Carbon::now()->subDays(7),
            ],
            [
                'order_number'    => '#ZYH021',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Nur Fadilah',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 200000.00,
                'created_at'      => Carbon::now()->subDays(6),
            ],
            [
                'order_number'    => '#ZYH022',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Walk-in Customer',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 25000.00,
                'created_at'      => Carbon::now()->subDays(3),
            ],
            [
                'order_number'    => '#ZYH023',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Dian Permata',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 150000.00,
                'created_at'      => Carbon::now()->subDays(1),
            ],
            [
                'order_number'    => '#ZYH024',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Walk-in Customer',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 70000.00,
                'created_at'      => Carbon::now()->subHours(3),
            ],
            [
                'order_number'    => '#ZYH025',
                'jenis_penjualan' => 'Offline',
                'customer_name'   => 'Yoga Aditya',
                'email_pembeli'   => null,
                'payment_status'  => 'Paid',
                'order_status'    => 'selesai',
                'total'           => 115000.00,
                'created_at'      => Carbon::now(),
            ],
        ];

        $produks = \App\Models\Produk::all();
        foreach ($pesanans as $index => $pesananData) {
            if ($produks->isNotEmpty()) {
                $offset = $index % $produks->count();
                $selected = $produks->slice($offset, 2);
                if ($selected->isEmpty()) {
                    $selected = $produks->take(2);
                }
                $orderItems = [];
                $calcTotal = 0;
                foreach ($selected as $prod) {
                    $qty = (($index + $prod->id_produk) % 2) + 1;
                    $price = $prod->harga;
                    $orderItems[] = [
                        'id' => $prod->id_produk,
                        'nama_produk' => $prod->nama_produk,
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $price * $qty,
                        'img' => $prod->foto_1 ? asset('storage/' . $prod->foto_1) : asset('image/logo_circle.png')
                    ];
                    $calcTotal += ($price * $qty);
                }
                $pesananData['items'] = $orderItems;
                $pesananData['total'] = $calcTotal;
            }
            Pesanan::create($pesananData);
        }
    }
}
