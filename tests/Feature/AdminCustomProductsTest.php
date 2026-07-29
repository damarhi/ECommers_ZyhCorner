<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;

class AdminCustomProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_custom_products_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Custom',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'kustom',
            'nama_produk' => 'Kue Tart Custom',
            'harga' => 250000,
            'status_produk' => 'Tersedia',
            'deskripsi' => 'Kue tart kustom sesuai permintaan.',
        ]);

        $response = $this->actingAs($admin)->get(route('products.custom'));

        $response->assertStatus(200);
        $response->assertSee('Produk Kustom');
        $response->assertSee('Kue Tart Custom');
        $response->assertSee('Kue Custom');
    }

    public function test_custom_products_separated_from_showcase(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue',
            'gambar_kategori' => 'dummy.png',
        ]);

        // Custom Product
        Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'kustom',
            'nama_produk' => 'Kue Ulang Tahun Kustom',
            'harga' => 300000,
            'status_produk' => 'Tersedia',
            'deskripsi' => 'Kue kustom khusus.',
        ]);

        // Showcase Product
        Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti Tawar Regular',
            'harga' => 15000,
            'status_produk' => 'Tersedia',
            'deskripsi' => 'Roti tawar biasa.',
        ]);

        // Custom products page should show custom product and not showcase product
        $responseCustom = $this->actingAs($admin)->get(route('products.custom'));
        $responseCustom->assertStatus(200);
        $responseCustom->assertSee('Kue Ulang Tahun Kustom');
        $responseCustom->assertDontSee('Roti Tawar Regular');

        // Showcase products page should show showcase product and not custom product
        $responseShowcase = $this->actingAs($admin)->get(route('products.index'));
        $responseShowcase->assertStatus(200);
        $responseShowcase->assertSee('Roti Tawar Regular');
        $responseShowcase->assertDontSee('Kue Ulang Tahun Kustom');
    }

    public function test_custom_product_store_with_price_range(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Custom',
            'gambar_kategori' => 'dummy.png',
        ]);

        $postData = [
            'nama_produk' => 'Cupcake Custom Range',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'kustom',
            'harga' => 20000,
            'harga_max' => 75000,
            'status_produk' => 'Berdasarkan Pesanan',
            'deskripsi' => 'Deskripsi cupcake custom.',
        ];

        $response = $this->actingAs($admin)->post(route('products.store'), $postData);

        $response->assertRedirect(route('products.custom'));

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Cupcake Custom Range',
            'jenis_produk' => 'kustom',
            'harga' => 20000,
            'harga_max' => 75000,
            'status_produk' => 'Berdasarkan Pesanan',
        ]);

        // Check range display on custom products page
        $pageResponse = $this->actingAs($admin)->get(route('products.custom'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Rp. 20.000 - Rp. 75.000');
    }
}
