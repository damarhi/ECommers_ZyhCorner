<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Diskon;

class AdminDiskonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }

    public function test_admin_can_access_discounts_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('discounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Diskon & Promo', false);
    }

    public function test_admin_can_create_discount(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Nastar Keju',
            'harga' => 100000,
            'status_produk' => 'Tersedia',
        ]);

        $postData = [
            'id_produk' => [$produk->id_produk],
            'nama_diskon' => 'Promo Lebaran 20%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 20,
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($admin)->post(route('discounts.store'), $postData);

        $response->assertRedirect(route('discounts.index'));

        $this->assertDatabaseHas('diskons', [
            'id_produk' => $produk->id_produk,
            'nama_diskon' => 'Promo Lebaran 20%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 20,
            'status' => 'Aktif',
        ]);
    }

    public function test_admin_can_create_discount_for_multiple_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue',
            'gambar_kategori' => 'dummy.png',
        ]);

        $p1 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Produk A',
            'harga' => 50000,
            'status_produk' => 'Tersedia',
        ]);

        $p2 = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Produk B',
            'harga' => 75000,
            'status_produk' => 'Tersedia',
        ]);

        $postData = [
            'id_produk' => [$p1->id_produk, $p2->id_produk],
            'nama_diskon' => 'Promo Kembar 15%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 15,
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($admin)->post(route('discounts.store'), $postData);

        $response->assertRedirect(route('discounts.index'));

        $this->assertDatabaseHas('diskons', [
            'id_produk' => $p1->id_produk,
            'nama_diskon' => 'Promo Kembar 15%',
        ]);

        $this->assertDatabaseHas('diskons', [
            'id_produk' => $p2->id_produk,
            'nama_diskon' => 'Promo Kembar 15%',
        ]);
    }

    public function test_admin_can_toggle_discount_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Lapis Surabaya',
            'harga' => 120000,
            'status_produk' => 'Tersedia',
        ]);

        $diskon = Diskon::create([
            'id_produk' => $produk->id_produk,
            'nama_diskon' => 'Flash Sale 10k',
            'tipe_diskon' => 'nominal',
            'nilai_diskon' => 10000,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('discounts.toggle', $diskon->id_diskon));

        $response->assertRedirect(route('discounts.index'));

        $diskon->refresh();
        $this->assertEquals('Nonaktif', $diskon->status);
    }

    public function test_admin_can_bulk_delete_discounts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Kering',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Nastar',
            'harga' => 80000,
            'status_produk' => 'Tersedia',
        ]);

        $d1 = Diskon::create([
            'id_produk' => $produk->id_produk,
            'nama_diskon' => 'Diskon 1',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'status' => 'Aktif',
        ]);

        $d2 = Diskon::create([
            'id_produk' => $produk->id_produk,
            'nama_diskon' => 'Diskon 2',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 15,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('discounts.bulk-delete'), [
            'ids' => [$d1->id_diskon, $d2->id_diskon],
        ]);

        $response->assertRedirect(route('discounts.index'));

        $this->assertDatabaseMissing('diskons', ['id_diskon' => $d1->id_diskon]);
        $this->assertDatabaseMissing('diskons', ['id_diskon' => $d2->id_diskon]);
    }
}
