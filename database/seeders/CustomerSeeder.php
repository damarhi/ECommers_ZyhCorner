<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Seed the customers table with dummy data.
     */
    public function run(): void
    {
        Customer::query()->delete();

        $customers = [
            [
                'name'         => 'Siti Nurhaliza',
                'location'     => 'Jakarta Selatan',
                'orders_count' => 12,
                'spent'        => 1850000.00,
                'contact'      => '081234567890',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Budi Santoso',
                'location'     => 'Bandung',
                'orders_count' => 8,
                'spent'        => 960000.00,
                'contact'      => '082345678901',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Dewi Lestari',
                'location'     => 'Surabaya',
                'orders_count' => 15,
                'spent'        => 2340000.00,
                'contact'      => '083456789012',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Ahmad Hidayat',
                'location'     => 'Yogyakarta',
                'orders_count' => 3,
                'spent'        => 375000.00,
                'contact'      => '084567890123',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Rina Wulandari',
                'location'     => 'Semarang',
                'orders_count' => 6,
                'spent'        => 720000.00,
                'contact'      => '085678901234',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Fajar Pratama',
                'location'     => 'Malang',
                'orders_count' => 20,
                'spent'        => 3100000.00,
                'contact'      => '086789012345',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Lina Marlina',
                'location'     => 'Bekasi',
                'orders_count' => 1,
                'spent'        => 85000.00,
                'contact'      => '087890123456',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Hendra Gunawan',
                'location'     => 'Tangerang',
                'orders_count' => 9,
                'spent'        => 1125000.00,
                'contact'      => '088901234567',
                'status'       => 'Inactive',
            ],
            [
                'name'         => 'Maya Sari',
                'location'     => 'Depok',
                'orders_count' => 4,
                'spent'        => 480000.00,
                'contact'      => '089012345678',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Rizky Ramadhan',
                'location'     => 'Bogor',
                'orders_count' => 7,
                'spent'        => 890000.00,
                'contact'      => '081123456789',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Putri Ayu',
                'location'     => 'Jakarta Barat',
                'orders_count' => 11,
                'spent'        => 1650000.00,
                'contact'      => '082234567890',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Dian Permata',
                'location'     => 'Cirebon',
                'orders_count' => 2,
                'spent'        => 150000.00,
                'contact'      => '083345678901',
                'status'       => 'Inactive',
            ],
            [
                'name'         => 'Eko Prasetyo',
                'location'     => 'Solo',
                'orders_count' => 5,
                'spent'        => 625000.00,
                'contact'      => '084456789012',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Nur Fadilah',
                'location'     => 'Jakarta Timur',
                'orders_count' => 14,
                'spent'        => 2100000.00,
                'contact'      => '085567890123',
                'status'       => 'Active',
            ],
            [
                'name'         => 'Yoga Aditya',
                'location'     => 'Medan',
                'orders_count' => 0,
                'spent'        => 0.00,
                'contact'      => '086678901234',
                'status'       => 'Active',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
