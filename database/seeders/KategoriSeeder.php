<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'nama_kategori' => 'Kue & Cake',
                'gambar_kategori' => 'kategori_images/kategori_cake.png',
            ],
            [
                'nama_kategori' => 'Roti & Pastry',
                'gambar_kategori' => 'kategori_images/kategori_pastry.png',
            ],
            [
                'nama_kategori' => 'Dessert & Buah Segar',
                'gambar_kategori' => 'kategori_images/kategori_dessert.png',
            ],
            [
                'nama_kategori' => 'Produk Kustom & Gift',
                'gambar_kategori' => 'kategori_images/kategori_kustom.png',
            ],
            [
                'nama_kategori' => 'Cemilan & Snack',
                'gambar_kategori' => 'kategori_images/kategori_snack.png',
            ],
        ];

        $categoryModels = [];
        foreach ($categoriesData as $data) {
            $categoryModels[$data['nama_kategori']] = Kategori::firstOrCreate(
                ['nama_kategori' => $data['nama_kategori']],
                ['gambar_kategori' => $data['gambar_kategori']]
            );
        }

        // Terapkan/Assign kategori ke setiap produk yang ada berdasarkan kata kunci atau kategori default
        $kueCakeId = $categoryModels['Kue & Cake']->id_kategori;
        $rotiPastryId = $categoryModels['Roti & Pastry']->id_kategori;
        $dessertId = $categoryModels['Dessert & Buah Segar']->id_kategori;
        $kustomId = $categoryModels['Produk Kustom & Gift']->id_kategori;
        $snackId = $categoryModels['Cemilan & Snack']->id_kategori;

        $allProducts = Produk::all();

        foreach ($allProducts as $product) {
            $nameLower = strtolower($product->nama_produk);
            $jenis = strtolower($product->jenis_produk ?? '');

            if ($jenis === 'kustom' || str_contains($nameLower, 'custom') || str_contains($nameLower, 'kustom')) {
                $product->id_kategori = $kustomId;
            } elseif (
                str_contains($nameLower, 'salad') || 
                str_contains($nameLower, 'fruit') || 
                str_contains($nameLower, 'tiramisu') || 
                str_contains($nameLower, 'parfait') || 
                str_contains($nameLower, 'dessert') ||
                str_contains($nameLower, 'pudding') ||
                str_contains($nameLower, 'yogurt')
            ) {
                $product->id_kategori = $dessertId;
            } elseif (
                str_contains($nameLower, 'cake') || 
                str_contains($nameLower, 'cheesecake') || 
                str_contains($nameLower, 'chiffon') || 
                str_contains($nameLower, 'red velvet')
            ) {
                $product->id_kategori = $kueCakeId;
            } elseif (
                str_contains($nameLower, 'tart') || 
                str_contains($nameLower, 'brownies') || 
                str_contains($nameLower, 'flan') || 
                str_contains($nameLower, 'slice') || 
                str_contains($nameLower, 'pie') || 
                str_contains($nameLower, 'puff') || 
                str_contains($nameLower, 'eclair') || 
                str_contains($nameLower, 'roll') || 
                str_contains($nameLower, 'donut') || 
                str_contains($nameLower, 'croissant') ||
                str_contains($nameLower, 'bread') ||
                str_contains($nameLower, 'pastry') ||
                str_contains($nameLower, 'pain')
            ) {
                $product->id_kategori = $rotiPastryId;
            } else {
                // Default ke Kue & Cake atau Snack jika tidak cocok kata kunci
                $product->id_kategori = ($product->id_produk % 2 === 0) ? $snackId : $kueCakeId;
            }

            $product->save();
        }
    }
}
