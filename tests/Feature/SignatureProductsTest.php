<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;

class SignatureProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_signature_flag(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Pastry Eksklusif',
            'gambar_kategori' => 'test.jpg'
        ]);

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'nama_produk' => 'Croissant Almond Signature',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 45000,
            'status_produk' => 15,
            'deskripsi' => 'Resep croissant artisan dengan almond praline.',
            'is_signature' => 1,
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Croissant Almond Signature',
            'is_signature' => 1,
        ]);
    }

    public function test_admin_can_update_product_signature_flag(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Cakes',
            'gambar_kategori' => 'test.jpg'
        ]);

        $product = Produk::create([
            'nama_produk' => 'Classic Cheesecake',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 60000,
            'status_produk' => 10,
            'deskripsi' => 'Cheesecake lembut.',
            'is_signature' => false,
        ]);

        $response = $this->actingAs($admin)->put(route('products.update', $product->id_produk), [
            'nama_produk' => 'Classic Cheesecake Updated',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 65000,
            'status_produk' => 8,
            'deskripsi' => 'Cheesecake resep rahasia chef.',
            'is_signature' => 1,
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('produks', [
            'id_produk' => $product->id_produk,
            'is_signature' => 1,
        ]);
    }

    public function test_homepage_displays_zyh_signatures_column_and_badge(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Tart Premium',
            'gambar_kategori' => 'test.jpg'
        ]);

        $sigProduct = Produk::create([
            'nama_produk' => 'Royal Opera Cake',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 95000,
            'status_produk' => 5,
            'deskripsi' => 'Lapisan ganache dan buttercream espresso.',
            'is_signature' => true,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertSee('Zyh Signatures');
        $response->assertSee('Mahakarya');
        $response->assertSee('Royal Opera Cake');
        $response->assertSee('signature-badge');
    }

    public function test_signature_products_uses_smart_fallback_when_less_than_three(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Bakery',
            'gambar_kategori' => 'test.jpg'
        ]);

        // Only 1 curated signature product
        $sig1 = Produk::create([
            'nama_produk' => 'Signature Tiramisu',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 85000,
            'status_produk' => 10,
            'is_signature' => true,
        ]);

        // 2 regular showcase products
        $reg1 = Produk::create([
            'nama_produk' => 'Premium Black Forest',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 75000,
            'status_produk' => 8,
            'is_signature' => false,
        ]);

        $reg2 = Produk::create([
            'nama_produk' => 'Matcha Roll Cake',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 55000,
            'status_produk' => 12,
            'is_signature' => false,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $signatureProducts = $response->viewData('signatureProducts');
        $this->assertCount(3, $signatureProducts);
        $this->assertTrue($signatureProducts->first()->is_signature);
        $this->assertEquals('Signature Tiramisu', $signatureProducts->first()->nama_produk);
    }

    public function test_signature_products_are_displayed_even_when_out_of_stock_and_prioritizes_stock(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Artisan Pastry',
            'gambar_kategori' => 'test.jpg'
        ]);

        // 2 signature products with 0 stock
        $sigOutOfStock1 = Produk::create([
            'nama_produk' => 'Butter Croissant Paris',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 22000,
            'status_produk' => '0',
            'is_signature' => true,
        ]);

        $sigOutOfStock2 = Produk::create([
            'nama_produk' => 'Pain Au Chocolat',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 26000,
            'status_produk' => '0',
            'is_signature' => true,
        ]);

        // 1 signature product with available stock
        $sigInStock = Produk::create([
            'nama_produk' => 'Black Forest Cake Classic',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 125000,
            'status_produk' => '5',
            'is_signature' => true,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $signatureProducts = $response->viewData('signatureProducts');
        $this->assertCount(3, $signatureProducts);

        // Product with stock > 0 should be first
        $this->assertEquals('Black Forest Cake Classic', $signatureProducts->first()->nama_produk);

        // Out of stock products must still be in the collection
        $names = $signatureProducts->pluck('nama_produk')->toArray();
        $this->assertContains('Butter Croissant Paris', $names);
        $this->assertContains('Pain Au Chocolat', $names);

        // Assert view renders them with signature badge and Habis badge
        $response->assertSee('Butter Croissant Paris');
        $response->assertSee('Pain Au Chocolat');
        $response->assertSee('Black Forest Cake Classic');
        $response->assertSee('Habis');
    }

    public function test_product_detail_page_displays_signature_badge(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Pengantin',
            'gambar_kategori' => 'wedding.jpg'
        ]);

        $sigProduct = Produk::create([
            'nama_produk' => 'Royal Velvet Cake',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 150000,
            'status_produk' => '5',
            'deskripsi' => 'Mahakarya red velvet lembut.',
            'stok' => 5,
            'is_signature' => true,
        ]);

        $response = $this->get(route('product.detail', $sigProduct->id_produk));
        $response->assertStatus(200);

        // Check signature badge on detail page
        $response->assertSee('detail-signature-badge');
        $response->assertSee('Zyh Signature');
        $response->assertSee('pmSignatureBadge');
        // Ensure info rows remain standard like regular products (no redundant Koleksi row)
        $response->assertDontSee('Mahakarya Signature Series');
        $response->assertDontSee('Mahakarya Zyh Signature Series');
    }

    public function test_product_detail_page_regular_product_does_not_display_signature_badge(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Roti Manis',
            'gambar_kategori' => 'roti.jpg'
        ]);

        $regProduct = Produk::create([
            'nama_produk' => 'Roti Cokelat Klasik',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 15000,
            'status_produk' => '5',
            'deskripsi' => 'Roti manis isi cokelat.',
            'stok' => 10,
            'is_signature' => false,
        ]);

        $response = $this->get(route('product.detail', $regProduct->id_produk));
        $response->assertStatus(200);

        $response->assertDontSee('detail-signature-badge');
        $response->assertDontSee('Mahakarya Signature Series');
    }

    public function test_product_detail_page_displays_expired_info_for_signature_product(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Spesial',
            'gambar_kategori' => 'special.jpg'
        ]);

        $sigProduct = Produk::create([
            'nama_produk' => 'Black Forest Cake Signature',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 125000,
            'status_produk' => '0',
            'deskripsi' => 'Kue bolu cokelat klasik khas Jerman.',
            'is_signature' => true,
            'tanggal_expired' => '2026-10-29',
        ]);

        $response = $this->get(route('product.detail', $sigProduct->id_produk));
        $response->assertStatus(200);

        $response->assertSee('Expired');
        $response->assertSee('29 Oct 2026');
        $response->assertSee('SKU');
        $response->assertSee('Stok Habis');
    }

    public function test_admin_update_product_preserves_tanggal_expired(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create([
            'nama_kategori' => 'Artisan Bread',
            'gambar_kategori' => 'bread.jpg'
        ]);

        $product = Produk::create([
            'nama_produk' => 'Butter Croissant Paris',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 22000,
            'status_produk' => '10',
            'is_signature' => false,
            'tanggal_expired' => '2026-11-05',
        ]);

        $response = $this->actingAs($admin)->put(route('products.update', $product->id_produk), [
            'nama_produk' => 'Butter Croissant Paris',
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'harga' => 22000,
            'status_produk' => '10',
            'is_signature' => 1,
            'tanggal_expired' => '2026-11-05',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('produks', [
            'id_produk' => $product->id_produk,
            'is_signature' => 1,
            'tanggal_expired' => '2026-11-05',
        ]);
    }
}
