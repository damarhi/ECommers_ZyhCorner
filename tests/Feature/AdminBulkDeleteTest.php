<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Customer;
use App\Models\Cassier;
use App\Models\Pesanan;
use App\Models\Retur;
use App\Models\Diskon;
use App\Models\Banner;

class AdminBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_delete_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kat = Kategori::create(['nama_kategori' => 'Pastry', 'gambar_kategori' => 'dummy.png']);

        $p1 = Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Croissant A',
            'harga' => 15000,
            'status_produk' => 'Tersedia',
        ]);
        $p2 = Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Croissant B',
            'harga' => 18000,
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($admin)->post(route('products.bulk-delete'), [
            'ids' => [$p1->id_produk, $p2->id_produk]
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('produks', ['id_produk' => $p1->id_produk]);
        $this->assertDatabaseMissing('produks', ['id_produk' => $p2->id_produk]);
    }

    public function test_admin_can_bulk_delete_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $c1 = Kategori::create(['nama_kategori' => 'Kategori 1', 'gambar_kategori' => 'k1.png']);
        $c2 = Kategori::create(['nama_kategori' => 'Kategori 2', 'gambar_kategori' => 'k2.png']);

        $response = $this->actingAs($admin)->post(route('categories.bulk-delete'), [
            'ids' => [$c1->id_kategori, $c2->id_kategori]
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('kategoris', ['id_kategori' => $c1->id_kategori]);
        $this->assertDatabaseMissing('kategoris', ['id_kategori' => $c2->id_kategori]);
    }

    public function test_admin_can_bulk_delete_customers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cust1 = Customer::create(['name' => 'Budi', 'status' => 'Active']);
        $cust2 = Customer::create(['name' => 'Siti', 'status' => 'Active']);

        $response = $this->actingAs($admin)->post(route('customers.bulk-delete'), [
            'ids' => [$cust1->id, $cust2->id]
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $cust1->id]);
        $this->assertDatabaseMissing('customers', ['id' => $cust2->id]);
    }

    public function test_admin_can_bulk_delete_cassiers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cas1 = Cassier::create(['name' => 'Kasir 1', 'email' => 'kasir1@test.com', 'password' => bcrypt('password'), 'status' => 'Active']);
        $cas2 = Cassier::create(['name' => 'Kasir 2', 'email' => 'kasir2@test.com', 'password' => bcrypt('password'), 'status' => 'Active']);

        $response = $this->actingAs($admin)->post(route('cassiers.bulk-delete'), [
            'ids' => [$cas1->id, $cas2->id]
        ]);

        $response->assertRedirect(route('cassiers.index'));
        $this->assertDatabaseMissing('cassiers', ['id' => $cas1->id]);
        $this->assertDatabaseMissing('cassiers', ['id' => $cas2->id]);
    }

    public function test_admin_can_bulk_delete_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ord1 = Pesanan::create([
            'order_number' => '#ORD-BULK-1',
            'customer_name' => 'Pelanggan 1',
            'total' => 50000,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
        ]);
        $ord2 = Pesanan::create([
            'order_number' => '#ORD-BULK-2',
            'customer_name' => 'Pelanggan 2',
            'total' => 75000,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)->post(route('orders.bulk-delete'), [
            'ids' => [$ord1->id, $ord2->id]
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseMissing('pesanans', ['id' => $ord1->id]);
        $this->assertDatabaseMissing('pesanans', ['id' => $ord2->id]);
    }

    public function test_admin_can_bulk_delete_returs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $r1 = Retur::create([
            'customer_name' => 'User 1',
            'order_id' => '#ORD-1',
            'product_name' => 'Product A',
            'reason' => 'Rusak',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);
        $r2 = Retur::create([
            'customer_name' => 'User 2',
            'order_id' => '#ORD-2',
            'product_name' => 'Product B',
            'reason' => 'Cacat',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        $response = $this->actingAs($admin)->post(route('returs.bulk-delete'), [
            'ids' => [$r1->id, $r2->id]
        ]);

        $response->assertRedirect(route('returs.index'));
        $this->assertDatabaseMissing('returs', ['id' => $r1->id]);
        $this->assertDatabaseMissing('returs', ['id' => $r2->id]);
    }
}
