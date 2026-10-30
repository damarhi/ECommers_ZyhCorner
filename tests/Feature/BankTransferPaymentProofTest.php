<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankTransferPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_update_bank_account_settings_with_sanitization(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'store_name' => 'Zyh Corner Pastry',
            'store_tagline' => 'Fresh Artisan Pastries',
            'store_description' => 'Toko pastry terpercaya di Pekanbaru',
            'store_address' => 'Jl. Merdeka No. 45, Pekanbaru',
            'store_phone' => '081234567890',
            'store_email' => 'store@zyhcorner.com',
            'instagram_url' => 'https://instagram.com/zyhcorner',
            'bank_name' => '<script>alert("xss")</script>Bank Central Asia (BCA)',
            'bank_account_number' => '8295-0192-38',
            'bank_account_holder' => '<b>ZYH CORNER PASTRY</b>',
        ];

        $response = $this->actingAs($admin)->post(route('settings.store.update'), $payload);

        $response->assertRedirect(route('settings.index') . '#store-tab');
        $response->assertSessionHas('success');

        $this->assertEquals('Bank Central Asia (BCA)', StoreSetting::get('bank_name'));
        $this->assertEquals('8295-0192-38', StoreSetting::get('bank_account_number'));
        $this->assertEquals('ZYH CORNER PASTRY', StoreSetting::get('bank_account_holder'));
    }

    public function test_non_admin_cannot_update_store_bank_settings(): void
    {
        $pelanggan = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($pelanggan)->post(route('settings.store.update'), [
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Hacker Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_upload_payment_proof_for_own_order(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer.transfer@example.com',
            'role' => 'pelanggan',
            'nama' => 'Budi Santoso',
        ]);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-101',
            'customer_name' => 'Budi Santoso',
            'email_pembeli' => $customer->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 150000,
            'payment_method' => 'Transfer Bank (Manual)',
            'shipping_method' => 'Kurir Toko',
            'items' => [
                ['id' => 1, 'name' => 'Bolu Kemojo', 'price' => 50000, 'qty' => 3],
            ],
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800)->size(500); // 500 KB

        $response = $this->actingAs($customer)->postJson(route('order.upload.proof', $order->id), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $order->refresh();
        $this->assertTrue($order->has_payment_proof);
        $this->assertNotNull($order->bukti_pembayaran);
        $this->assertNotNull($order->bukti_pembayaran_uploaded_at);
        $this->assertStringStartsWith('bukti_pembayaran/', $order->bukti_pembayaran);

        Storage::disk('public')->assertExists($order->bukti_pembayaran);
    }

    public function test_upload_proof_rejects_invalid_file_types_and_oversized_files(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer.invalid@example.com',
            'role' => 'pelanggan',
        ]);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-102',
            'customer_name' => 'Customer Test',
            'email_pembeli' => $customer->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 100000,
            'payment_method' => 'Transfer Bank',
            'items' => [],
        ]);

        // Rejects non-image (e.g. PDF or PHP script)
        $txtFile = UploadedFile::fake()->create('malicious.php', 100, 'text/plain');
        $response1 = $this->actingAs($customer)->postJson(route('order.upload.proof', $order->id), [
            'bukti_pembayaran' => $txtFile,
        ]);
        $response1->assertStatus(422);

        // Rejects file > 2MB (2048 KB)
        $largeFile = UploadedFile::fake()->image('large_receipt.png')->size(2500);
        $response2 = $this->actingAs($customer)->postJson(route('order.upload.proof', $order->id), [
            'bukti_pembayaran' => $largeFile,
        ]);
        $response2->assertStatus(422);
    }

    public function test_customer_cannot_upload_proof_to_another_customers_order(): void
    {
        $customerA = User::factory()->create([
            'email' => 'victim@example.com',
            'role' => 'pelanggan',
        ]);

        $customerB = User::factory()->create([
            'email' => 'attacker@example.com',
            'role' => 'pelanggan',
        ]);

        $orderA = Pesanan::create([
            'order_number' => 'ORD-VICTIM-01',
            'customer_name' => 'Victim',
            'email_pembeli' => $customerA->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 200000,
            'payment_method' => 'Transfer Bank',
            'items' => [],
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($customerB)->post(route('order.upload.proof', $orderA->id), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertStatus(404);
    }

    public function test_admin_and_kasir_can_confirm_payment_proof(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $path = UploadedFile::fake()->image('test_proof.jpg')->store('bukti_pembayaran', 'public');

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-103',
            'customer_name' => 'Pelanggan Setia',
            'email_pembeli' => 'setia@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 125000,
            'payment_method' => 'Transfer Bank (Manual)',
            'bukti_pembayaran' => $path,
            'bukti_pembayaran_uploaded_at' => now(),
            'items' => [],
        ]);

        $response = $this->actingAs($kasir)->post(route('orders.confirm-proof', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Paid', $order->payment_status);
        $this->assertEquals('dikemas', $order->order_status);
        $this->assertNotNull($order->catatan_verifikasi_pembayaran);
    }

    public function test_admin_and_kasir_can_reject_payment_proof_with_reason(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $file = UploadedFile::fake()->image('fake_proof.jpg');
        $path = $file->store('bukti_pembayaran', 'public');

        Storage::disk('public')->assertExists($path);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-104',
            'customer_name' => 'Pelanggan Ditolak',
            'email_pembeli' => 'ditolak@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 125000,
            'payment_method' => 'Transfer Bank',
            'bukti_pembayaran' => $path,
            'bukti_pembayaran_uploaded_at' => now(),
            'items' => [],
        ]);

        $response = $this->actingAs($kasir)->post(route('orders.reject-proof', $order->id), [
            'alasan_penolakan' => 'Nominal transfer tidak sesuai dengan total tagihan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertNull($order->bukti_pembayaran);
        $this->assertNull($order->bukti_pembayaran_uploaded_at);
        $this->assertEquals('Pending', $order->payment_status);
        $this->assertEquals('menunggu pembayaran', $order->order_status);
        $this->assertStringContainsString('Nominal transfer tidak sesuai', $order->catatan_verifikasi_pembayaran);

        // Previous invalid proof file is deleted
        Storage::disk('public')->assertMissing($path);
    }

    public function test_rejection_requires_reason(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-105',
            'customer_name' => 'Pelanggan Test',
            'email_pembeli' => 'test@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 75000,
            'payment_method' => 'Transfer Bank',
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'items' => [],
        ]);

        $response = $this->actingAs($kasir)->post(route('orders.reject-proof', $order->id), [
            'alasan_penolakan' => '',
        ]);

        $response->assertSessionHasErrors('alasan_penolakan');
    }

    public function test_customer_views_render_bank_info_and_upload_modal(): void
    {
        $customer = User::factory()->create([
            'email' => 'view.test@example.com',
            'role' => 'pelanggan',
            'nama' => 'Dewi Lestari',
        ]);

        StoreSetting::set('bank_name', 'Bank Central Asia (BCA)');
        StoreSetting::set('bank_account_number', '8295-0192-38');
        StoreSetting::set('bank_account_holder', 'ZYH CORNER PASTRY');

        $order = Pesanan::create([
            'order_number' => 'ORD-VIEW-99',
            'customer_name' => 'Dewi Lestari',
            'email_pembeli' => $customer->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 95000,
            'payment_method' => 'Transfer Bank (BCA)',
            'shipping_method' => 'Kurir Toko',
            'items' => [
                ['id' => 1, 'name' => 'Brownies Shiny Crust', 'price' => 95000, 'qty' => 1],
            ],
        ]);

        // Order Success View
        $responseSuccess = $this->actingAs($customer)->get(route('order.success', ['order_id' => $order->id]));
        $responseSuccess->assertStatus(200);
        $responseSuccess->assertSee('8295-0192-38');
        $responseSuccess->assertSee('ZYH CORNER PASTRY');
        $responseSuccess->assertSee('Upload Bukti Pembayaran:');
        $responseSuccess->assertSee('Kirim Bukti Pembayaran');

        // Order Detail View
        $responseDetail = $this->actingAs($customer)->get(route('order.detail', $order->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Upload Bukti Bayar');
        $responseDetail->assertSee('id="uploadProofModal"', false);
        $responseDetail->assertSee('8295-0192-38');

        // Order History View
        $responseHistory = $this->actingAs($customer)->get(route('order.history'));
        $responseHistory->assertStatus(200);
        $responseHistory->assertSee('Upload Bukti');
        $responseHistory->assertSee('id="historyProofModal"', false);
    }

    public function test_customer_cannot_upload_proof_after_order_is_confirmed_or_processing(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create([
            'email' => 'customer.locked@example.com',
            'role' => 'pelanggan',
        ]);

        // Order already confirmed and dikemas
        $order = Pesanan::create([
            'order_number' => 'ORD-LOCKED-01',
            'customer_name' => 'Customer Locked',
            'email_pembeli' => $customer->email,
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'total' => 125000,
            'payment_method' => 'Transfer Bank (BCA)',
            'items' => [],
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg')->size(500);

        $response = $this->actingAs($customer)->postJson(route('order.upload.proof', $order->id), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_quick_confirm_is_blocked_for_bank_transfer_orders(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-NOQUICK',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Randi',
            'email_pembeli' => 'randi@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 150000,
            'payment_method' => 'Transfer Bank (BCA)',
            'items' => [],
        ]);

        // Backend quick-confirm endpoint returns 422 for bank transfer
        $response = $this->actingAs($kasir)->postJson("/admin/orders/{$order->id}/quick-confirm");
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Table does not render quick confirm button for bank transfer
        $responseTable = $this->actingAs($kasir)->get('/admin/orders');
        $responseTable->assertStatus(200);
        $responseTable->assertDontSee('btn-quick-confirm-' . $order->id);
    }

    public function test_customer_views_display_rejection_reason_and_upload_ulang_when_proof_is_rejected(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer.rejected@example.com',
            'role' => 'pelanggan',
            'nama' => 'Agus Priyono',
        ]);

        $kasir = User::factory()->create(['role' => 'kasir']);

        $order = Pesanan::create([
            'order_number' => 'ORD-TF-REJECTED',
            'customer_name' => 'Agus Priyono',
            'email_pembeli' => $customer->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu verifikasi',
            'total' => 200000,
            'payment_method' => 'Transfer Bank (BCA)',
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'bukti_pembayaran_uploaded_at' => now(),
            'shipping_address' => 'Jl. Nangka No. 12',
            'shipping_method' => 'Kurir Toko',
            'items' => [
                ['id' => 1, 'name' => 'Bolu Gulung', 'price' => 50000, 'qty' => 4],
            ],
        ]);

        Storage::disk('public')->put('bukti_pembayaran/dummy.jpg', 'fake-image');

        // Kasir menolak bukti transfer dengan alasan nominal kurang
        $reason = 'Nominal transfer tidak sesuai dengan total tagihan.';
        $rejectResponse = $this->actingAs($kasir)->post(route('orders.reject-proof', $order->id), [
            'alasan_penolakan' => $reason,
        ]);
        $rejectResponse->assertRedirect();
        $rejectResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertTrue($order->is_payment_proof_rejected);
        $this->assertEquals($reason, $order->payment_proof_rejection_reason);
        $this->assertEmpty($order->bukti_pembayaran);

        // 1. Cek tampilan order_success pelanggan
        $successView = $this->actingAs($customer)->get(route('order.success', ['order_id' => $order->id]));
        $successView->assertStatus(200);
        $successView->assertSee('Bukti Pembayaran Ditolak Kasir');
        $successView->assertSee($reason);
        $successView->assertSee('Upload Ulang Bukti Pembayaran');

        // 2. Cek tampilan order_detail pelanggan
        $detailView = $this->actingAs($customer)->get(route('order.detail', $order->id));
        $detailView->assertStatus(200);
        $detailView->assertSee('Bukti Pembayaran Ditolak oleh Kasir');
        $detailView->assertSee($reason);
        $detailView->assertSee('Upload Ulang Bukti');

        // 3. Cek tampilan order_history pelanggan
        $historyView = $this->actingAs($customer)->get(route('order.history'));
        $historyView->assertStatus(200);
        $historyView->assertSee('Upload Ulang Bukti');

        // 4. Pelanggan mengunggah ulang bukti baru
        $newFile = UploadedFile::fake()->image('bukti_baru.jpg', 600, 800)->size(300);
        $uploadResponse = $this->actingAs($customer)->postJson(route('order.upload.proof', $order->id), [
            'bukti_pembayaran' => $newFile,
        ]);
        $uploadResponse->assertStatus(200);
        $uploadResponse->assertJson(['success' => true]);

        $order->refresh();
        $this->assertFalse($order->is_payment_proof_rejected);
        $this->assertTrue($order->has_payment_proof);
        $this->assertNotNull($order->bukti_pembayaran);
    }
}
