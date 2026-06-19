<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::firstOrCreate(
            ['email' => 'nama@gmail.com'],
            [
                'nama' => 'nama_nama',
                'no_hp' => 8123123123,
                'password' => bcrypt('password'),
                'role' => 'pelanggan',
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nama' => 'Admin Zyh Corner',
                'no_hp' => 8123456789,
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        if (\App\Models\Pesanan::count() <= 2) {
            \App\Models\Pesanan::query()->delete();

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH101',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran',
                'total' => 135000.00,
            ]);
            
            \App\Models\Pesanan::create([
                'order_number' => '#ZYH102',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Paid',
                'order_status' => 'selesai',
                'total' => 45000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH103',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Pending',
                'order_status' => 'proses',
                'total' => 25000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH104',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Pending',
                'order_status' => 'on the way',
                'total' => 75000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH105',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Paid',
                'order_status' => 'delivered',
                'total' => 60000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH106',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Cancelled',
                'order_status' => 'batal',
                'total' => 15000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH107',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Pending',
                'order_status' => 'sedang dikirim',
                'total' => 30000.00,
            ]);

            \App\Models\Pesanan::create([
                'order_number' => '#ZYH108',
                'jenis_penjualan' => 'Online',
                'customer_name' => 'nama_nama',
                'email_pembeli' => 'nama@gmail.com',
                'payment_status' => 'Pending',
                'order_status' => 'ready',
                'total' => 120000.00,
            ]);
        }
    }
}
