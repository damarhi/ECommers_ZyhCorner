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
        // ── Admin & Default User Accounts ─────────────────────────
        User::firstOrCreate(
            ['email' => 'nama@gmail.com'],
            [
                'nama' => 'nama_nama',
                'no_hp' => 8123123123,
                'password' => bcrypt('password'),
                'role' => 'pelanggan',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nama' => 'Admin Zyh Corner',
                'no_hp' => 8123456789,
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // ── Call Individual Seeders ────────────────────────────────
        $this->call([
            UserPelangganSeeder::class,  // Akun pelanggan dummy
            CustomerSeeder::class,        // Data customer (admin panel)
            PesananSeeder::class,         // Pesanan & penjualan
            ReturSeeder::class,           // Data return
            DiskonSeeder::class,          // Data diskon promo
            BannerSeeder::class,          // Data banner promo
            AboutSettingSeeder::class,    // Data pengaturan About Us
        ]);
    }
}
