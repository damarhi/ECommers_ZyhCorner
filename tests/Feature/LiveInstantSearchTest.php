<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveInstantSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_renders_instant_search_attributes_and_script(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
        ]);

        $category = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $category->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Lapis Legit Special',
            'harga' => 85000,
            'stok' => 15,
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($kasir)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('live-instant-search.js');
        $response->assertSee('pos-product-item');
        $response->assertSee('data-search=', false);
        $response->assertSee(strtolower('Lapis Legit Special'));
    }

    public function test_admin_tables_render_instant_search_attributes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Kategori::create([
            'nama_kategori' => 'Roti & Pastry',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $category->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Croissant Almond',
            'harga' => 32000,
            'stok' => 10,
            'status_produk' => 'Tersedia',
        ]);

        // Test Products Table
        $responseProducts = $this->actingAs($admin)->get(route('products.index'));
        $responseProducts->assertStatus(200);
        $responseProducts->assertSee('live-instant-search.js');
        $responseProducts->assertSee('data-search=', false);
        $responseProducts->assertSee(strtolower('Croissant Almond'));

        // Test Messages Table
        $responseMessages = $this->actingAs($admin)->get(route('messages.index'));
        $responseMessages->assertStatus(200);
        $responseMessages->assertSee('live-instant-search.js');
        $responseMessages->assertSee('name="search"', false);
    }

    public function test_shop_page_renders_instant_search_attributes_for_customers(): void
    {
        $category = Kategori::create([
            'nama_kategori' => 'Tart & Cake',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $category->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Black Forest Cake',
            'deskripsi' => 'Kue tart cokelat klasik dengan cherry segar',
            'harga' => 125000,
            'stok' => 5,
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->get(route('shop'));

        $response->assertStatus(200);
        $response->assertSee('live-instant-search.js');
        $response->assertSee('shop-product-col');
        $response->assertSee('data-search=', false);
        $response->assertSee(strtolower('Black Forest Cake'));
    }

    public function test_kasir_role_cannot_access_admin_private_entities(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
        ]);

        // Kasir cannot access messages
        $responseMessages = $this->actingAs($kasir)->get(route('messages.index'));
        $this->assertTrue(in_array($responseMessages->getStatusCode(), [302, 403]));

        // Kasir cannot access customers
        $responseCustomers = $this->actingAs($kasir)->get(route('customers.index'));
        $this->assertTrue(in_array($responseCustomers->getStatusCode(), [302, 403]));
    }
}
