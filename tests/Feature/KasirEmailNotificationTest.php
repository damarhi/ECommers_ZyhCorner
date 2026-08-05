<?php

namespace Tests\Feature;

use App\Mail\PesananBaruKasirMail;
use App\Mail\ProdukKadaluarsaKasirMail;
use App\Mail\ReturProdukKasirMail;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use App\Models\Retur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KasirEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesanan_baru_triggers_kasir_email_notification()
    {
        Mail::fake();

        $kasir = User::create([
            'nama' => 'Kasir Zyh',
            'email' => 'kasir@zyhcorner.com',
            'no_hp' => '08123456789',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $kategori = \App\Models\Kategori::create([
            'nama_kategori' => 'Kue Karamel Cat',
            'gambar_kategori' => 'dummy.png',
        ]);

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Kue Karamel',
            'harga' => 50000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        $response = $this->actingAs($kasir)->postJson('/checkout', [
            'items' => [
                ['id' => $product->id_produk, 'qty' => 1]
            ],
            'payment_method' => 'COD',
            'address' => 'Jl. Test No. 123',
        ]);

        $response->assertStatus(200);

        Mail::assertSent(PesananBaruKasirMail::class, function ($mail) {
            return $mail->hasTo('qwertyuiopxaw@gmail.com') && $mail->hasTo('kasir@zyhcorner.com');
        });
    }

    public function test_retur_produk_triggers_kasir_email_notification()
    {
        Mail::fake();

        $user = User::create([
            'nama' => 'Pelanggan Test',
            'email' => 'pelanggan@gmail.com',
            'no_hp' => '08123456780',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $order = Pesanan::create([
            'order_number' => '#TEST12',
            'jenis_penjualan' => 'Online',
            'payment_method' => 'COD',
            'customer_name' => 'Pelanggan Test',
            'email_pembeli' => 'pelanggan@gmail.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 50000,
            'items' => [],
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($user)->postJson("/order-history/{$order->id}/return", [
            'reason' => 'Barang Rusak',
            'description' => 'Kemasan pecah',
            'photo' => $file,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('returs', [
            'customer_name' => 'Pelanggan Test',
            'order_id' => '#TEST12',
        ]);

        $retur = Retur::where('order_id', '#TEST12')->first();
        $this->assertNotNull($retur->foto_retur);
        $this->assertStringContainsString('uploads/returs/', $retur->foto_retur);

        Mail::assertSent(ReturProdukKasirMail::class, function ($mail) {
            return $mail->hasTo('qwertyuiopxaw@gmail.com');
        });

        // Test admin updating return status maintains foto_retur intact
        $admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin_test@gmail.com',
            'no_hp' => '081234567899',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('returs.update', $retur->id), [
            'customer_name' => $retur->customer_name,
            'order_id' => $retur->order_id,
            'reason' => $retur->reason,
            'product_name' => $retur->product_name,
            'return_date' => $retur->return_date,
            'status' => 'Accepted',
        ]);

        $updateResponse->assertRedirect(route('returs.index'));
        $retur->refresh();
        $this->assertEquals('Accepted', $retur->status);
        $this->assertNotNull($retur->foto_retur);
        $this->assertStringContainsString('uploads/returs/', $retur->foto_retur);
    }

    public function test_produk_kadaluarsa_triggers_kasir_email_notification()
    {
        Mail::fake();

        $product = Produk::create([
            'nama_produk' => 'Kue Expired Test',
            'harga' => 30000,
            'stok' => 5,
            'status_produk' => 'Tersedia',
            'jenis_produk' => 'showcase',
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        Produk::checkAndEmptyExpiredProducts();

        Mail::assertSent(ProdukKadaluarsaKasirMail::class, function ($mail) {
            return $mail->hasTo('qwertyuiopxaw@gmail.com');
        });
    }
}
