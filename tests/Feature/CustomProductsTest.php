<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;

class CustomProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_products_page_renders_successfully(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Nastar Premium',
            'harga' => 85000,
            'status_produk' => 'tersedia',
            'deskripsi' => 'Nastar lezat dengan selai nanas asli.',
        ]);

        $response = $this->get(route('custom-products'));

        $response->assertStatus(200);
        $response->assertSee('Custom Products');
        $response->assertSee('Nastar Premium');
        $response->assertSee('Rp. 85.000');
        // Assert breadcrumbs wrapper is not rendered
        $response->assertDontSee('breadcrumb-wrapper');
        // Assert we see the WhatsApp link for Nastar Premium
        $response->assertSee('https://wa.me/628123456789');
    }

    public function test_custom_product_detail_page_renders_whatsapp_button(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Lapis Legit',
            'harga' => 150000,
            'status_produk' => 'tersedia',
            'deskripsi' => 'Lapis legit premium rasa original.',
        ]);

        // Request detail page with ?custom=1
        $response = $this->get(route('product.detail', ['id' => $produk->id_produk, 'custom' => 1]));

        $response->assertStatus(200);
        // Assert breadcrumbs point to Custom Products
        $response->assertSee('Custom Products');
        // Assert Beli Sekarang is replaced with WhatsApp button
        $response->assertSee('Pesan via WhatsApp');
        $response->assertDontSee('Beli Sekarang');
    }
}
