<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserPelangganSeeder extends Seeder
{
    /**
     * Seed the users table with dummy pelanggan (customer) accounts.
     */
    public function run(): void
    {
        $pelangganAccounts = [
            [
                'nama'     => 'Siti Nurhaliza',
                'email'    => 'siti.nurhaliza@gmail.com',
                'no_hp'    => 81234567890,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Budi Santoso',
                'email'    => 'budi.santoso@gmail.com',
                'no_hp'    => 82345678901,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Dewi Lestari',
                'email'    => 'dewi.lestari@gmail.com',
                'no_hp'    => 83456789012,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Ahmad Hidayat',
                'email'    => 'ahmad.hidayat@gmail.com',
                'no_hp'    => 84567890123,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Rina Wulandari',
                'email'    => 'rina.wulandari@gmail.com',
                'no_hp'    => 85678901234,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Fajar Pratama',
                'email'    => 'fajar.pratama@gmail.com',
                'no_hp'    => 86789012345,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Lina Marlina',
                'email'    => 'lina.marlina@gmail.com',
                'no_hp'    => 87890123456,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Hendra Gunawan',
                'email'    => 'hendra.gunawan@gmail.com',
                'no_hp'    => 88901234567,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Maya Sari',
                'email'    => 'maya.sari@gmail.com',
                'no_hp'    => 89012345678,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
            [
                'nama'     => 'Rizky Ramadhan',
                'email'    => 'rizky.ramadhan@gmail.com',
                'no_hp'    => 81123456789,
                'password' => Hash::make('password123'),
                'role'     => 'pelanggan',
            ],
        ];

        foreach ($pelangganAccounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                $account
            );
        }
    }
}
