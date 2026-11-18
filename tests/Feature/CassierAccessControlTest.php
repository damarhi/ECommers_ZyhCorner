<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CassierAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_access_allowed_pages(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir);

        // Access Kasir Shift Dashboard
        $this->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Shift Kasir');

        // Access orders, POS, returs, transaction history (without lifetime omzet / financial report button)
        $this->get(route('orders.index'))->assertStatus(200);
        $this->get(route('pos.index'))->assertStatus(200);
        $this->get(route('returs.index'))->assertStatus(200);
        $this->get(route('all-sales.index'))
            ->assertStatus(200)
            ->assertSee('Riwayat Transaksi Toko')
            ->assertDontSee('Total Omset Penjualan')
            ->assertDontSee('Cetak Laporan Penjualan');

        // Access Profile Settings (without Store Info tab)
        $this->get(route('settings.index'))
            ->assertStatus(200)
            ->assertSee('Kasir Toko')
            ->assertDontSee('Simpan Info Toko');

        // Access Expired Products (Read-Only Showcase Check)
        $this->get(route('products.expired.index'))
            ->assertStatus(200)
            ->assertSee('Mode Cek Etalase (Lihat Saja)');

        // Access Customer Contact Messages (Read & Update Status, without Delete)
        $msg = \App\Models\ContactMessage::create([
            'nama' => 'Tamu CS',
            'email' => 'tamu@zyhcorner.com',
            'subjek' => 'Tanya Jam Buka',
            'pesan' => 'Buka jam berapa kak?',
            'status' => 'Belum Dibaca',
        ]);
        $this->get(route('messages.index'))->assertStatus(200)->assertSee('Tamu CS');
        $this->patch(route('messages.update-status', $msg->id), ['status' => 'Dibaca'])->assertRedirect();
        $this->assertEquals('Dibaca', $msg->fresh()->status);

        // Access Produk Showcase (Index, Create, Store, Edit, Update — without Delete)
        $this->get(route('products.index'))->assertStatus(200)->assertSee('Produk Showcase');
        $this->get(route('products.create'))->assertStatus(200)->assertDontSee('value="kustom"', false);

        $this->post(route('products.store'), [
            'nama_produk' => 'Donat Gula Kasir',
            'harga' => 12000,
            'status_produk' => '15',
            'jenis_produk' => 'kustom', // tampered -> forced to showcase
        ])->assertRedirect(route('products.index'));

        $createdProd = \App\Models\Produk::where('nama_produk', 'Donat Gula Kasir')->firstOrFail();
        $this->assertEquals('showcase', $createdProd->jenis_produk);

        $this->get(route('products.edit', $createdProd->id_produk))->assertStatus(200);
        $this->put(route('products.update', $createdProd->id_produk), [
            'nama_produk' => 'Donat Gula Kasir Update',
            'harga' => 13000,
            'status_produk' => '20',
        ])->assertRedirect(route('products.index'));
        $this->assertEquals('20', $createdProd->fresh()->status_produk);
    }

    public function test_kasir_is_redirected_from_restricted_pages(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir);

        // Financial reports & full omzet Excel export remain Admin-only
        $this->get(route('all-sales.report'))->assertRedirect(route('orders.index'));
        $this->get(route('export.all-sales'))->assertRedirect(route('orders.index'));

        // Expired product write/delete & Contact Message delete remain Admin-only
        $msg = \App\Models\ContactMessage::create([
            'nama' => 'Pesan Penting',
            'email' => 'penting@zyhcorner.com',
            'subjek' => 'Komplain',
            'pesan' => 'Isi pesan',
            'status' => 'Belum Dibaca',
        ]);
        $this->delete(route('messages.destroy', $msg->id))->assertRedirect(route('orders.index'));
        $this->post(route('messages.bulk-delete'), ['ids' => [$msg->id]])->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('contact_messages', ['id' => $msg->id]);

        // Deleting products & accessing/editing Custom Products remain Admin-only
        $showcaseProd = \App\Models\Produk::create([
            'nama_produk' => 'Kue Sus Showcase',
            'harga' => 10000,
            'status_produk' => '8',
            'jenis_produk' => 'showcase',
        ]);
        $kustomProd = \App\Models\Produk::create([
            'nama_produk' => 'Kue Pengantin Kustom',
            'harga' => 500000,
            'harga_max' => 900000,
            'status_produk' => 'Berdasarkan Pesanan',
            'jenis_produk' => 'kustom',
        ]);

        $this->delete(route('products.destroy', $showcaseProd->id_produk))->assertRedirect(route('orders.index'));
        $this->post(route('products.bulk-delete'), ['ids' => [$showcaseProd->id_produk]])->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('produks', ['id_produk' => $showcaseProd->id_produk]);

        $this->get(route('products.custom'))->assertRedirect(route('orders.index'));
        $this->get(route('products.edit', $kustomProd->id_produk))->assertRedirect(route('orders.index'));
        $this->put(route('products.update', $kustomProd->id_produk), [
            'nama_produk' => 'Tampered Kustom',
            'harga' => 1000,
            'status_produk' => '1',
        ])->assertRedirect(route('orders.index'));

        // Try to access categories
        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');

        // Try to access discounts & banners
        $this->get(route('discounts.index'))->assertRedirect(route('orders.index'));
        $this->get(route('banners.index'))->assertRedirect(route('orders.index'));

        // Try to access customers & customer export
        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        $this->get(route('export.customers'))->assertRedirect(route('orders.index'));

        // Try to access cassiers
        $response = $this->get(route('cassiers.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');
    }

    public function test_admin_can_access_all_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Access dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        // Access categories
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);

        // Access customers
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);

        // Access cassiers
        $response = $this->get(route('cassiers.index'));
        $response->assertStatus(200);

        // Access orders
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);
    }
}
