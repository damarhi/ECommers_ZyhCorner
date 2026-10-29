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
            'payment_method' => 'Cash',
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals(10, $product->stok);

        $pesanan = Pesanan::where('customer_name', 'Walk-in Customer')->latest()->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals('Cash', $pesanan->payment_method);
        $this->assertEquals('Offline', $pesanan->jenis_penjualan);
    }

    public function test_pos_offline_checkout_rejects_out_of_stock_product()
    {
        $kasir = User::create([
            'nama' => 'Kasir POS OutOfStock',
            'email' => 'kasiroos@zyhcorner.com',
            'no_hp' => '08123456787',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kategori OOS',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Bolu Habis',
            'harga' => 25000,
            'status_produk' => '0',
            'jenis_produk' => 'showcase',
        ]);

        $response = $this->actingAs($kasir)->postJson('/admin/pos/checkout', [
            'items' => [
                ['id' => $product->id_produk, 'price' => 25000, 'quantity' => 1]
            ],
            'customer_name' => 'Walk-in Customer',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('sudah habis', $response->json('message'));
    }

    public function test_pos_offline_checkout_rejects_excessive_quantity()
    {
        $kasir = User::create([
            'nama' => 'Kasir POS Excessive',
            'email' => 'kasirexcess@zyhcorner.com',
            'no_hp' => '08123456786',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kategori Low',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Kering',
            'harga' => 50000,
            'status_produk' => '2',
            'jenis_produk' => 'showcase',
        ]);

        $response = $this->actingAs($kasir)->postJson('/admin/pos/checkout', [
            'items' => [
                ['id' => $product->id_produk, 'price' => 50000, 'quantity' => 5]
            ],
            'customer_name' => 'Walk-in Customer',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('tidak mencukupi', $response->json('message'));
    }

    public function test_pos_offline_checkout_works_with_browser_keyed_items()
    {
        $kasir = User::create([
            'nama' => 'Kasir Browser Payload',
            'email' => 'kasirbrowser@zyhcorner.com',
            'no_hp' => '08123456785',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kategori Keyed',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Roti Manis',
            'harga' => 12000,
            'status_produk' => '8',
            'jenis_produk' => 'showcase',
        ]);

        // Browser sends an object keyed by ID
        $browserPayloadItems = [
            (string) $product->id_produk => [
                'id' => $product->id_produk,
                'name' => 'Roti Manis',
                'price' => 12000,
                'quantity' => 2,
                'image' => ''
            ]
        ];

        $response = $this->actingAs($kasir)->postJson('/admin/pos/checkout', [
            'items' => $browserPayloadItems,
            'payment_method' => 'Transfer',
            'customer_name' => 'Ibu Rina',
        ]);

        $response->assertStatus(200);
        $product->refresh();
        $this->assertEquals(6, $product->stok);

        $order = Pesanan::where('customer_name', 'Ibu Rina')->first();
        $this->assertNotNull($order);
        $this->assertEquals('Transfer', $order->payment_method);
        $this->assertEquals(24000, $order->total);
    }

    public function test_kasir_can_add_stock_from_pos_modal()
    {
        $kasir = User::create([
            'nama' => 'Kasir Restock Test',
            'email' => 'kasirrestock@zyhcorner.com',
            'no_hp' => '08123456784',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Bolu Gulung',
            'harga' => 35000,
            'status_produk' => '5',
            'jenis_produk' => 'showcase',
            'tanggal_expired' => '2026-11-01',
        ]);

        $this->assertEquals(5, $product->stok);

        $response = $this->actingAs($kasir)->postJson(route('pos.add-stock', $product->id_produk), [
            'additional_stock' => 15,
            'tanggal_expired' => '2026-11-05',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'new_stock' => 20,
            'product_id' => $product->id_produk,
        ]);

        $product->refresh();
        $this->assertEquals(20, $product->stok);
        $this->assertEquals('2026-11-05', $product->tanggal_expired);
    }

    public function test_add_stock_from_pos_validates_input()
    {
        $kasir = User::create([
            'nama' => 'Kasir Invalid Restock',
            'email' => 'kasirinvalid@zyhcorner.com',
            'no_hp' => '08123456783',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Nastar',
            'harga' => 45000,
            'status_produk' => '2',
            'jenis_produk' => 'showcase',
        ]);

        $response = $this->actingAs($kasir)->postJson(route('pos.add-stock', $product->id_produk), [
            'additional_stock' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['additional_stock']);
    }

    public function test_pos_page_prioritizes_products_with_stock()
    {
        $kasir = User::create([
            'nama' => 'Kasir Priority Test',
            'email' => 'kasirpriority@zyhcorner.com',
            'no_hp' => '08123456782',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kategori Priority',
            'gambar_kategori' => 'dummy.png',
        ]);

        // Created recently but has 0 stock
        $outOfStock = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Habis Baru',
            'harga' => 30000,
            'status_produk' => '0',
            'jenis_produk' => 'showcase',
            'created_at' => now(),
        ]);

        // Created earlier but has 10 stock
        $inStock = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Tersedia Lama',
            'harga' => 40000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($kasir)->get(route('pos.index'));
        $response->assertStatus(200);

        // In-stock product must be ordered first before out-of-stock product
        $products = $response->viewData('products');
        $this->assertEquals($inStock->id_produk, $products->first()->id_produk);
        $this->assertEquals($outOfStock->id_produk, $products->last()->id_produk);

        $response->assertSee('Tersedia: 10');
        $response->assertSee('Stok Habis');
    }
}
