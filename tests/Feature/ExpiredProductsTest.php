<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\ProdukKadaluarsa;
use Carbon\Carbon;

class ExpiredProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_expired_products_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('products.expired.index'));

        $response->assertStatus(200);
        $response->assertSee('Produk Kadaluarsa');
    }

    public function test_expired_showcase_product_is_automatically_synced_to_dataset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        // Showcase product with expired date in the past
        $expiredProduct = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti Kadaluarsa',
            'harga' => 15000,
            'status_produk' => 'Tersedia',
            'tanggal_expired' => Carbon::now()->subDays(2)->format('Y-m-d'),
        ]);

        // Showcase product with future expired date (should NOT sync)
        $validProduct = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti Segar',
            'harga' => 20000,
            'status_produk' => 'Tersedia',
            'tanggal_expired' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);

        // Custom product (should NOT sync even if expired date passed)
        $customProduct = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'kustom',
            'nama_produk' => 'Kue Kustom',
            'harga' => 50000,
            'status_produk' => 'Berdasarkan Pesanan',
            'tanggal_expired' => null,
        ]);

        // Access index page to trigger automatic sync
        $response = $this->actingAs($admin)->get(route('products.expired.index'));
        $response->assertStatus(200);

        // Verify dataset table contains expired product
        $this->assertDatabaseHas('produk_kadaluarsas', [
            'id_produk' => $expiredProduct->id_produk,
            'status_tindakan' => 'Perlu Tindakan',
        ]);

        // Verify showcase product stock is automatically emptied to 0
        $this->assertDatabaseHas('produks', [
            'id_produk' => $expiredProduct->id_produk,
            'status_produk' => '0',
        ]);

        $this->assertDatabaseMissing('produk_kadaluarsas', [
            'id_produk' => $validProduct->id_produk,
        ]);

        $this->assertDatabaseMissing('produk_kadaluarsas', [
            'id_produk' => $customProduct->id_produk,
        ]);
    }

    public function test_admin_can_update_expired_product_status_and_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Bolu Gulung',
            'harga' => 35000,
            'status_produk' => 'Tersedia',
            'tanggal_expired' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $expiredRecord = ProdukKadaluarsa::create([
            'id_produk' => $produk->id_produk,
            'tanggal_expired' => $produk->tanggal_expired,
            'jumlah_stok' => '5 Pcs',
            'status_tindakan' => 'Perlu Tindakan',
            'catatan' => 'Menunggu pemusnahan',
        ]);

        $response = $this->actingAs($admin)->put(route('products.expired.update', $expiredRecord->id_expired), [
            'status_tindakan' => 'Dimusnahkan',
            'catatan' => 'Telah dimusnahkan oleh pihak gudang.',
        ]);

        $response->assertRedirect(route('products.expired.index'));

        $this->assertDatabaseHas('produk_kadaluarsas', [
            'id_expired' => $expiredRecord->id_expired,
            'status_tindakan' => 'Dimusnahkan',
            'catatan' => 'Telah dimusnahkan oleh pihak gudang.',
        ]);
    }

    public function test_customer_accessing_shop_automatically_syncs_expired_products(): void
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $expiredProduct = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti Expired Sebelum Login',
            'harga' => 10000,
            'status_produk' => 'Tersedia',
            'tanggal_expired' => Carbon::now()->subDay()->format('Y-m-d'),
        ]);

        // Customer (guest/unauthenticated) visits shop route
        $response = $this->get(route('shop'));
        $response->assertStatus(200);

        // Verify showcase product stock is automatically emptied to 0
        $this->assertDatabaseHas('produks', [
            'id_produk' => $expiredProduct->id_produk,
            'status_produk' => '0',
        ]);

        // Verify record in produk_kadaluarsas
        $this->assertDatabaseHas('produk_kadaluarsas', [
            'id_produk' => $expiredProduct->id_produk,
            'status_tindakan' => 'Perlu Tindakan',
        ]);
    }

    public function test_admin_can_bulk_delete_expired_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Roti',
            'gambar_kategori' => 'dummy.png',
        ]);

        $p1 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti A',
            'harga' => 10000,
            'status_produk' => 'Tersedia',
        ]);

        $p2 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti B',
            'harga' => 12000,
            'status_produk' => 'Tersedia',
        ]);

        $exp1 = ProdukKadaluarsa::create([
            'id_produk' => $p1->id_produk,
            'tanggal_expired' => now()->toDateString(),
            'jumlah_stok' => '3 Pcs',
            'status_tindakan' => 'Perlu Tindakan',
        ]);

        $exp2 = ProdukKadaluarsa::create([
            'id_produk' => $p2->id_produk,
            'tanggal_expired' => now()->toDateString(),
            'jumlah_stok' => '4 Pcs',
            'status_tindakan' => 'Perlu Tindakan',
        ]);

        $response = $this->actingAs($admin)->post(route('products.expired.bulk-delete'), [
            'ids' => [$exp1->id_expired, $exp2->id_expired]
        ]);

        $response->assertRedirect(route('products.expired.index'));

        $this->assertDatabaseMissing('produk_kadaluarsas', ['id_expired' => $exp1->id_expired]);
        $this->assertDatabaseMissing('produk_kadaluarsas', ['id_expired' => $exp2->id_expired]);
    }

    public function test_multiple_expirations_for_same_product_creates_new_distinct_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Pastry Batch',
            'gambar_kategori' => 'dummy.png',
        ]);

        // 1st Batch Expired Product
        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Bolu Gulung Pisang',
            'harga' => 30000,
            'status_produk' => '10 Pcs',
            'tanggal_expired' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        // First sync -> Creates 1st expired record
        Produk::checkAndEmptyExpiredProducts();

        $this->assertEquals(1, ProdukKadaluarsa::where('id_produk', $product->id_produk)->count());
        $firstRecord = ProdukKadaluarsa::where('id_produk', $product->id_produk)->first();

        // Admin restocks the product with a new expiry date
        $product->status_produk = '15 Pcs';
        $product->tanggal_expired = Carbon::yesterday()->format('Y-m-d');
        $product->save();

        // Second sync -> Creates 2nd NEW expired record with distinct id_expired
        Produk::checkAndEmptyExpiredProducts();

        $this->assertEquals(2, ProdukKadaluarsa::where('id_produk', $product->id_produk)->count());

        $records = ProdukKadaluarsa::where('id_produk', $product->id_produk)->get();
        $this->assertNotEquals($records[0]->id_expired, $records[1]->id_expired);
    }
}
