<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use App\Models\Retur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Wave3StockSchemaAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_restore_is_idempotent_across_duplicate_webhooks_and_calls(): void
    {
        $serverKey = 'SB-Mid-server-wave3-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $produk = Produk::create([
            'nama_produk' => 'Croissant Butter',
            'harga' => 25000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        $order = Pesanan::create([
            'order_number' => '#IDEMP01',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Andi',
            'email_pembeli' => 'andi@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 50000,
            'items' => [
                ['id' => $produk->id_produk, 'name' => 'Croissant Butter', 'price' => 25000, 'qty' => 2],
            ],
        ]);

        Produk::reduceStockForOrder($order);
        $this->assertEquals(8, $produk->fresh()->stok);

        $sig = hash('sha512', '#IDEMP01' . '200' . '50000.00' . $serverKey);
        $payload = [
            'order_id' => '#IDEMP01',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => $sig,
            'transaction_status' => 'expire',
            'payment_type' => 'qris',
        ];

        // First expire webhook restores +2 -> 10
        $this->postJson('/midtrans-callback', $payload)->assertStatus(200);
        $this->assertEquals(10, $produk->fresh()->stok);
        $this->assertNotNull($order->fresh()->stock_restored_at);

        // Duplicate expire webhook + manual restore call must NOT restore stock a second time
        $this->postJson('/midtrans-callback', $payload)->assertStatus(200);
        Produk::restoreStockForOrder($order->fresh());
        $this->assertEquals(10, $produk->fresh()->stok);
    }

    public function test_midtrans_webhook_rejects_mock_key_gross_mismatch_and_paid_downgrade(): void
    {
        $order = Pesanan::create([
            'order_number' => '#MIDSEC1',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Rina',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 100000,
            'items' => [],
        ]);

        // 1. Mock server key must be rejected on public webhook
        config(['services.midtrans.server_key' => 'mock']);
        $mockSig = hash('sha512', '#MIDSEC1200100000.00mock');
        $this->postJson('/midtrans-callback', [
            'order_id' => '#MIDSEC1',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $mockSig,
            'transaction_status' => 'settlement',
        ])->assertStatus(403);

        // 2. Real key with mismatched gross_amount must be rejected (400)
        $realKey = 'SB-Mid-server-real-test';
        config(['services.midtrans.server_key' => $realKey]);
        $tamperedSig = hash('sha512', '#MIDSEC12001000.00' . $realKey);
        $this->postJson('/midtrans-callback', [
            'order_id' => '#MIDSEC1',
            'status_code' => '200',
            'gross_amount' => '1000.00',
            'signature_key' => $tamperedSig,
            'transaction_status' => 'settlement',
        ])->assertStatus(400);
        $this->assertEquals('Pending', $order->fresh()->payment_status);

        // 3. Valid settlement marks Paid; subsequent late expire webhook must NOT overwrite Paid
        $validSig = hash('sha512', '#MIDSEC1200100000.00' . $realKey);
        $this->postJson('/midtrans-callback', [
            'order_id' => '#MIDSEC1',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $validSig,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);
        $this->assertEquals('Paid', $order->fresh()->payment_status);

        $this->postJson('/midtrans-callback', [
            'order_id' => '#MIDSEC1',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => $validSig,
            'transaction_status' => 'expire',
        ])->assertStatus(200);
        $this->assertEquals('Paid', $order->fresh()->payment_status);
        $this->assertNotEquals('batal', $order->fresh()->order_status);
    }

    public function test_canceling_paid_awaiting_order_preserves_paid_status_for_refund_tracking(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $order = Pesanan::create([
            'user_id' => $user->user_id,
            'order_number' => '#PAIDLOCK',
            'jenis_penjualan' => 'Online',
            'customer_name' => $user->nama,
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'menunggu konfirmasi',
            'total' => 80000,
            'items' => [],
        ]);

        $this->actingAs($user)
            ->postJson(route('order.cancel', $order->id), ['cancel_reason' => 'Salah pilih varian'])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('batal', $order->order_status);
        $this->assertEquals('Paid', $order->payment_status);
    }

    public function test_anti_idor_on_returns_email_change_continuity_and_no_address_leak(): void
    {
        $budi1 = User::factory()->create([
            'nama' => 'Budi Santoso',
            'email' => 'budi1@example.com',
            'no_hp' => '081100001111',
            'role' => 'pelanggan',
        ]);

        $budi2 = User::factory()->create([
            'nama' => 'Budi Santoso',
            'email' => 'budi2@example.com',
            'no_hp' => null,
            'alamat' => null,
            'role' => 'pelanggan',
        ]);

        // Unrelated Customer record with same name and null contact must NOT leak its location to budi2
        Customer::create([
            'user_id' => $budi1->user_id,
            'name' => 'Budi Santoso',
            'contact' => null,
            'location' => 'Alamat Rahasia Budi 1 No. 99',
            'status' => 'Active',
        ]);

        $this->actingAs($budi2)->get(route('settings'))->assertStatus(200);
        $this->assertNull($budi2->fresh()->alamat);

        $realProduct = Produk::create([
            'nama_produk' => 'Lapis Legit Prunes Asli',
            'harga' => 120000,
            'status_produk' => '5',
            'jenis_produk' => 'showcase',
        ]);

        Produk::create([
            'nama_produk' => 'Produk Acak Lainnya',
            'harga' => 15000,
            'status_produk' => '20',
            'jenis_produk' => 'showcase',
        ]);

        $orderBudi1 = Pesanan::create([
            'user_id' => $budi1->user_id,
            'order_number' => '#BUDI1ORD',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi Santoso',
            'email_pembeli' => 'budi1@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 120000,
            'items' => [
                ['id' => $realProduct->id_produk, 'name' => 'Lapis Legit Prunes Asli', 'price' => 120000, 'qty' => 1],
            ],
        ]);

        // Verify customer_phone accessor never crashes when querying Customer without 'email' column
        $budi2Order = Pesanan::create([
            'user_id' => $budi2->user_id,
            'order_number' => '#BUDI2ORD',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi Santoso',
            'email_pembeli' => 'budi2@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 15000,
            'items' => [],
        ]);
        $this->assertNull($budi2Order->customer_phone);

        $returBudi1 = Retur::create([
            'user_id' => $budi1->user_id,
            'customer_name' => 'Budi Santoso',
            'order_id' => '#BUDI1ORD',
            'reason' => 'Kemasan penyok',
            'return_date' => now()->toDateString(),
            'product_name' => 'Lapis Legit Prunes Asli',
            'status' => 'New',
        ]);

        // budi2 (same customer_name!) MUST get 404 when trying to read budi1's return detail
        $this->actingAs($budi2)
            ->get(route('return.detail', $returBudi1->id))
            ->assertStatus(404);

        // budi1 changes email in settings and still accesses order & accurate return detail items
        $this->actingAs($budi1)->postJson(route('settings.account'), [
            'nama' => 'Budi Santoso',
            'email' => 'budi1.new@example.com',
            'no_hp' => '081100001111',
        ])->assertStatus(200);

        $this->actingAs($budi1->fresh())
            ->get(route('order.detail', $orderBudi1->id))
            ->assertStatus(200);

        $retDetailResp = $this->actingAs($budi1->fresh())
            ->get(route('return.detail', $returBudi1->id));
        $retDetailResp->assertStatus(200);
        $retDetailResp->assertSee('Lapis Legit Prunes Asli');
        $retDetailResp->assertDontSee('Produk Acak Lainnya');
    }

    public function test_sales_report_and_exports_handle_string_expired_stock_shipping_discount_and_formula_injection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Cake',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Tiramisu Box',
            'harga' => 100000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        // Legacy non-numeric jumlah_stok ('Tersedia' & '5 Pcs') that previously caused PHP 8.3 TypeError
        ProdukKadaluarsa::create([
            'id_produk' => $produk->id_produk,
            'tanggal_expired' => now()->toDateString(),
            'jumlah_stok' => 'Tersedia',
            'status_tindakan' => 'Perlu Tindakan',
        ]);

        // Order with Rp 10,000 item discount (90,000) + Rp 20,000 shipping = Rp 110,000 total
        // plus formula-injection payload in customer_name
        Pesanan::create([
            'order_number' => '#RPT-FIX-01',
            'jenis_penjualan' => 'Online',
            'customer_name' => '=CMD|\' /C calc\'!A0',
            'email_pembeli' => 'buyer@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 110000,
            'shipping_cost' => 20000,
            'items' => [
                [
                    'id' => $produk->id_produk,
                    'name' => 'Tiramisu Box',
                    'price' => 90000,
                    'original_price' => 100000,
                    'qty' => 1,
                ],
            ],
        ]);

        // 1. Sales report renders 200 and calculates exact Rp 10.000 discount (not 0!) & Bruto Rp 120.000
        $reportResp = $this->actingAs($admin)->get(route('all-sales.report'));
        $reportResp->assertStatus(200);
        $this->assertEquals(10000.0, (float) $reportResp->viewData('totalPotonganDiskon'));
        $this->assertEquals(120000.0, (float) $reportResp->viewData('totalPemasukkanAsli'));
        $this->assertEquals(110000.0, (float) $reportResp->viewData('totalPemasukkanSetelahDiskon'));
        $this->assertEquals(99, (int) $reportResp->viewData('jumlahProdukKadaluarsa'));

        // 2. Excel XML export succeeds without TypeError and neutralizes leading '='
        $xlsResp = $this->actingAs($admin)->get(route('export.all-sales'));
        $xlsResp->assertStatus(200);
        $this->assertStringContainsString('&apos;=CMD|', $xlsResp->getContent());

        // 3. CSV orders export neutralizes leading '=' with single quote
        $csvResp = $this->actingAs($admin)->get(route('export.orders'));
        $csvResp->assertStatus(200);
        $this->assertStringContainsString("'=CMD|", $csvResp->streamedContent());
    }
}
