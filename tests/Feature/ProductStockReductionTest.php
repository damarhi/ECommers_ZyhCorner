<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockReductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_decrements_on_cod_checkout()
    {
        $user = User::create([
            'nama' => 'User Stock Test',
            'email' => 'stocktest@gmail.com',
            'no_hp' => '08123456789',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kue Kategori',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Bolu Pandan',
            'harga' => 45000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        $this->assertEquals(10, $product->stok);

        $response = $this->actingAs($user)->postJson('/checkout', [
            'items' => [
                ['id' => $product->id_produk, 'qty' => 3]
            ],
            'payment_method' => 'COD',
            'address' => 'Jl. Merdeka No 1',
        ]);
        $response->assertStatus(200);
        $product->refresh();
        $this->assertEquals(7, $product->stok);
    }

    public function test_product_stock_decrements_on_pos_offline_checkout()
    {
        $kasir = User::create([
            'nama' => 'Kasir POS Test',
            'email' => 'kasirpos@zyhcorner.com',
            'no_hp' => '08123456788',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kue Kategori 2',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Lapis',
            'harga' => 30000,
            'status_produk' => '15',
            'jenis_produk' => 'showcase',
        ]);

        $this->assertEquals(15, $product->stok);

        $response = $this->actingAs($kasir)->postJson('/admin/pos/checkout', [
            'items' => [
                ['id' => $product->id_produk, 'price' => 30000, 'quantity' => 5]
            ],
            'customer_name' => 'Walk-in Customer',
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals(10, $product->stok);
    }
}
