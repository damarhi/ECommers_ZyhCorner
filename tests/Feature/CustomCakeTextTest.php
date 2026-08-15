<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomCakeTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_bisa_custom_teks_on_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'nama_produk' => 'Kue Ulang Tahun Specia',
            'harga' => 150000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
            'bisa_custom_teks' => '1',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'Kue Ulang Tahun Specia',
            'bisa_custom_teks' => 1,
        ]);
    }

    public function test_checkout_saves_item_custom_text_in_order()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $product = Produk::create([
            'nama_produk' => 'Kue Tart Cokelat',
            'harga' => 200000,
            'status_produk' => '10',
            'bisa_custom_teks' => 1,
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.post'), [
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'price' => 200000,
                    'qty' => 1,
                    'bisa_custom_teks' => true,
                    'custom_text' => 'Selamat Ulang Tahun Ke-25 Budi!'
                ]
            ],
            'address' => 'Jl. Merdeka No. 123',
            'payment_method' => 'COD',
            'shipping_method' => 'Maxim',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $pesanan = Pesanan::first();
        $this->assertNotNull($pesanan);
        $this->assertEquals('Selamat Ulang Tahun Ke-25 Budi!', $pesanan->items[0]['custom_text']);
    }
}
