<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailStockInfoTest extends TestCase
{
    use RefreshDatabase;

    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);
    }

    public function test_detail_page_renders_abundant_stock_information(): void
    {
        $product = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Bolu Gulung Spesial',
            'harga' => 45000,
            'status_produk' => '8',
        ]);

        $response = $this->get(route('product.detail', $product->id_produk));

        $response->assertStatus(200);
        $response->assertSee('Tersedia: 8 unit');
        $response->assertSee('Ketersediaan Stok');
        $response->assertSee('8 unit (Ready Stock / Siap Kirim)');
        $response->assertDontSee('Tersisa 8 unit');
    }

    public function test_detail_page_renders_low_stock_warning_information(): void
    {
        $product = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Chiffon Pandan',
            'harga' => 55000,
            'status_produk' => '2',
        ]);

        $response = $this->get(route('product.detail', $product->id_produk));

        $response->assertStatus(200);
        $response->assertSee('Sisa 2 unit lagi (Segera Habis!)');
        $response->assertSee('Sisa 2 unit (Segera Habis)');
        $response->assertDontSee('Tersisa 2 unit');
    }

    public function test_detail_page_renders_out_of_stock_information(): void
    {
        $product = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Kue Lapis Legit',
            'harga' => 120000,
            'status_produk' => '0',
        ]);

        $response = $this->get(route('product.detail', $product->id_produk));

        $response->assertStatus(200);
        $response->assertSee('Stok Habis');
        $response->assertSee('Habis (Sedang Diproduksi Ulang)');
        $response->assertSee('is-out-of-stock');
    }

    public function test_detail_page_renders_custom_product_preorder_information(): void
    {
        $product = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'jenis_produk' => 'kustom',
            'nama_produk' => 'Kue Pengantin Kustom 3 Tingkat',
            'harga' => 750000,
            'status_produk' => '10',
        ]);

        $response = $this->get(route('product.detail', $product->id_produk));

        $response->assertStatus(200);
        $response->assertSee('Pre-Order / Dibuat Sesuai Pesanan');
        $response->assertDontSee('Made by Order');
        $response->assertSee('Pre-Order (Dibuat Sesuai Pesanan)');
    }
}
