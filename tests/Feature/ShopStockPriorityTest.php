<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Produk;
use App\Models\Kategori;

class ShopStockPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_prioritizes_products_with_stock(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        // Create an out-of-stock product created recently
        $outOfStock = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Produk Habis Stock',
            'harga' => 50000,
            'status_produk' => '0',
            'created_at' => now(),
        ]);

        // Create an in-stock product created earlier
        $inStock = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Produk Ada Stock',
            'harga' => 60000,
            'status_produk' => '10',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('shop'));

        $response->assertStatus(200);

        // Fetch products passed to view
        $products = $response->viewData('products');
        
        $this->assertCount(2, $products);
        // The first product in the list should be the in-stock product even though outOfStock is newer
        $this->assertEquals('Produk Ada Stock', $products->first()->nama_produk);
        $this->assertEquals('Produk Habis Stock', $products->last()->nama_produk);
    }
}
