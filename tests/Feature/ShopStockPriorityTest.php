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

    public function test_customer_can_search_products_by_keyword(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Pastry',
            'gambar_kategori' => 'dummy.png',
        ]);

        $p1 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Croissant Keju',
            'deskripsi' => 'Pastry lezat isi keju melimpah',
            'harga' => 25000,
            'status_produk' => 'Tersedia',
        ]);

        $p2 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Bolu Cokelat',
            'deskripsi' => 'Bolu lembut dengan rasa cokelat manis',
            'harga' => 35000,
            'status_produk' => 'Tersedia',
        ]);

        // Search for 'Croissant'
        $response = $this->get(route('shop', ['search' => 'Croissant']));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Croissant Keju', $products->first()->nama_produk);

        // Search by description 'lembut'
        $responseDesc = $this->get(route('shop', ['search' => 'lembut']));
        $responseDesc->assertStatus(200);
        $productsDesc = $responseDesc->viewData('products');
        $this->assertCount(1, $productsDesc);
        $this->assertEquals('Bolu Cokelat', $productsDesc->first()->nama_produk);
    }

    public function test_search_suggestions_api_returns_matching_products(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Dessert',
            'gambar_kategori' => 'dummy.png',
        ]);

        Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Tiramisu Layer Cake',
            'harga' => 45000,
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->get(route('search.suggestions', ['q' => 'Tiramisu']));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Tiramisu Layer Cake',
            'price' => 'Rp. 45.000',
            'category' => 'Dessert',
        ]);
    }
}
