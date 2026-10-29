<?php

namespace Tests\Feature;

use App\Models\Retur;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReturStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_retur@example.com',
            'nama' => 'Admin Retur',
        ]);
    }

    public function test_admin_can_view_returs_index_with_status_tabs_and_counts(): void
    {
        Retur::create([
            'customer_name' => 'Budi Santoso',
            'order_id' => '#ORD-NEW-1',
            'product_name' => 'Kue Red Velvet',
            'reason' => 'Kue penyok saat pengiriman',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        Retur::create([
            'customer_name' => 'Ani Wijaya',
            'order_id' => '#ORD-DONE-1',
            'product_name' => 'Bolu Gulung Keju',
            'reason' => 'Topping rusak',
            'return_date' => now()->toDateString(),
            'status' => 'Done',
        ]);

        $response = $this->actingAs($this->admin)->get(route('returs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('statusCounts');
        $response->assertSee('Semua Retur');
        $response->assertSee('Retur Baru');
        $response->assertSee('Kue Red Velvet');
        $response->assertSee('Bolu Gulung Keju');
    }

    public function test_admin_can_filter_returs_by_status(): void
    {
        Retur::create([
            'customer_name' => 'Budi Santoso',
            'order_id' => '#ORD-NEW-1',
            'product_name' => 'Kue Red Velvet',
            'reason' => 'Kue penyok saat pengiriman',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        Retur::create([
            'customer_name' => 'Ani Wijaya',
            'order_id' => '#ORD-DONE-1',
            'product_name' => 'Bolu Gulung Keju',
            'reason' => 'Topping rusak',
            'return_date' => now()->toDateString(),
            'status' => 'Done',
        ]);

        $responseNew = $this->actingAs($this->admin)->get(route('returs.index', ['filter' => 'New']));
        $responseNew->assertStatus(200);
        $responseNew->assertSee('Kue Red Velvet');
        $responseNew->assertDontSee('Bolu Gulung Keju');

        $responseDone = $this->actingAs($this->admin)->get(route('returs.index', ['filter' => 'Done']));
        $responseDone->assertStatus(200);
        $responseDone->assertSee('Bolu Gulung Keju');
        $responseDone->assertDontSee('Kue Red Velvet');
    }

    public function test_admin_can_quick_update_retur_status(): void
    {
        $retur = Retur::create([
            'customer_name' => 'Citra Lestari',
            'order_id' => '#ORD-CITRA-1',
            'product_name' => 'Brownies Fudgy',
            'reason' => 'Salah varian rasa',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        // Quick update to Accepted
        $response = $this->actingAs($this->admin)->patch(route('returs.update-status', $retur->id), [
            'status' => 'Accepted',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Accepted', $retur->fresh()->status);

        // Quick update to Done
        $response2 = $this->actingAs($this->admin)->patch(route('returs.update-status', $retur->id), [
            'status' => 'Done',
        ]);

        $response2->assertRedirect();
        $this->assertEquals('Done', $retur->fresh()->status);
    }

    public function test_retur_status_mutator_normalizes_case_and_aliases(): void
    {
        $retur1 = Retur::create([
            'customer_name' => 'User 1',
            'order_id' => '#ORD-1',
            'product_name' => 'Kue Tart',
            'reason' => 'Rusak',
            'return_date' => now()->toDateString(),
            'status' => 'rejected',
        ]);
        $this->assertEquals('Rejected', $retur1->status);

        $retur2 = Retur::create([
            'customer_name' => 'User 2',
            'order_id' => '#ORD-2',
            'product_name' => 'Kue Sus',
            'reason' => 'Basi',
            'return_date' => now()->toDateString(),
            'status' => 'completed',
        ]);
        $this->assertEquals('Done', $retur2->status);

        $retur3 = Retur::create([
            'customer_name' => 'User 3',
            'order_id' => '#ORD-3',
            'product_name' => 'Kue Lapis',
            'reason' => 'Pecah',
            'return_date' => now()->toDateString(),
            'status' => 'processing',
        ]);
        $this->assertEquals('Processed', $retur3->status);
    }

    public function test_admin_can_update_status_and_catatan_admin(): void
    {
        $retur = Retur::create([
            'customer_name' => 'Dewi Sartika',
            'order_id' => '#ORD-DEWI-01',
            'product_name' => 'Lapis Legit',
            'reason' => 'Topping basah',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        $response = $this->actingAs($this->admin)->put(route('returs.update', $retur->id), [
            'status' => 'Rejected',
            'catatan_admin' => 'Kemasan dibuka melebihi 24 jam, tidak memenuhi syarat retur.',
        ]);

        $response->assertRedirect(route('returs.index'));
        $retur->refresh();

        $this->assertEquals('Rejected', $retur->status);
        $this->assertEquals('Kemasan dibuka melebihi 24 jam, tidak memenuhi syarat retur.', $retur->catatan_admin);
        // Pastikan data historis pengajuan awal tetap terjaga
        $this->assertEquals('Dewi Sartika', $retur->customer_name);
        $this->assertEquals('Lapis Legit', $retur->product_name);
    }

    public function test_admin_can_search_returs_by_customer_name_order_id_and_reason(): void
    {
        Retur::create([
            'customer_name' => 'Budi Santoso',
            'order_id' => '#ORD-BUDI-99',
            'product_name' => 'Kue Red Velvet',
            'reason' => 'Kue gosong di bagian bawah',
            'return_date' => now()->toDateString(),
            'status' => 'New',
        ]);

        Retur::create([
            'customer_name' => 'Siti Aminah',
            'order_id' => '#ORD-SITI-88',
            'product_name' => 'Bolu Gulung Keju',
            'reason' => 'Ukuran tidak sesuai pesanan',
            'return_date' => now()->toDateString(),
            'status' => 'Done',
        ]);

        // 1. Search by Customer Name
        $responseName = $this->actingAs($this->admin)->get(route('returs.index', ['search' => 'Budi']));
        $responseName->assertStatus(200);
        $responseName->assertSee('Budi Santoso');
        $responseName->assertDontSee('Siti Aminah');

        // 2. Search by Order ID
        $responseOrder = $this->actingAs($this->admin)->get(route('returs.index', ['search' => 'ORD-SITI']));
        $responseOrder->assertStatus(200);
        $responseOrder->assertSee('Siti Aminah');
        $responseOrder->assertDontSee('Budi Santoso');

        // 3. Search by Reason
        $responseReason = $this->actingAs($this->admin)->get(route('returs.index', ['search' => 'gosong']));
        $responseReason->assertStatus(200);
        $responseReason->assertSee('Budi Santoso');
        $responseReason->assertDontSee('Siti Aminah');
    }

    public function test_retur_table_renders_valid_data_search_attributes_and_accessors(): void
    {
        $retur = Retur::create([
            'customer_name' => 'Hendra Kusuma',
            'order_id' => '#ORD-HK-77',
            'product_name' => 'Lapis Surabaya',
            'reason' => 'Krim meleleh',
            'return_date' => now()->toDateString(),
            'status' => 'Accepted',
        ]);

        // Verify defensive alias accessors
        $this->assertEquals('Hendra Kusuma', $retur->nama_pelanggan);
        $this->assertEquals('#ORD-HK-77', $retur->order_number);
        $this->assertEquals('Krim meleleh', $retur->alasan);
        $this->assertEquals('Accepted', $retur->status_retur);

        // Verify HTML markup data-search
        $response = $this->actingAs($this->admin)->get(route('returs.index'));
        $response->assertStatus(200);
        $response->assertSee('data-search=', false);
        $response->assertSee('hendra kusuma', false);
        $response->assertSee('#ord-hk-77', false);
        $response->assertSee('krim meleleh', false);
    }
}
