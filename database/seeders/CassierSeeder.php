<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cassier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CassierSeeder extends Seeder
{
    /**
     * Seed the cassiers table with realistic cashier accounts.
     */
    public function run(): void
    {
        $cassiers = [
            [
                'name'     => 'Kasir Pagi - Rina',
                'email'    => 'kasir.rina@zyhcorner.com',
                'password' => 'kasir123',
                'status'   => 'Active',
                'no_hp'    => 81298765432,
            ],
            [
                'name'     => 'Kasir Siang - Dimas',
                'email'    => 'kasir.dimas@zyhcorner.com',
                'password' => 'kasir123',
                'status'   => 'Active',
                'no_hp'    => 81298765433,
            ],
            [
                'name'     => 'Kasir Malam - Sarah',
                'email'    => 'kasir.sarah@zyhcorner.com',
                'password' => 'kasir123',
                'status'   => 'Active',
                'no_hp'    => 81298765434,
            ],
            [
                'name'     => 'Kasir Cadangan - Eko',
                'email'    => 'kasir.eko@zyhcorner.com',
                'password' => 'kasir123',
                'status'   => 'Deactive',
                'no_hp'    => 81298765435,
            ],
        ];

        foreach ($cassiers as $c) {
            Cassier::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name'     => $c['name'],
                    'email'    => $c['email'],
                    'password' => Hash::make($c['password']),
                    'status'   => $c['status'],
                    'image'    => null,
                ]
            );

            // Also ensure corresponding User record with role kasir exists
            User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'nama'     => $c['name'],
                    'email'    => $c['email'],
                    'password' => Hash::make($c['password']),
                    'role'     => 'kasir',
                    'no_hp'    => $c['no_hp'],
                ]
            );
        }
    }
}
