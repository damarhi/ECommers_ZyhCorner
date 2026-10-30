<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_resolves_address_fallback_from_customers_table(): void
    {
        // Create user
        $user = User::factory()->create([
            'nama' => 'Budi Santoso',
            'no_hp' => 82345678901,
            'alamat' => null,
        ]);

        // Create customer record with location
        Customer::create([
            'name' => 'Budi Santoso',
            'location' => 'Bandung',
            'contact' => '082345678901',
            'status' => 'Active',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Bandung');
        
        // Assert user's address was saved to the users table
        $user->refresh();
        $this->assertEquals('Bandung', $user->alamat);

        // Assert customer's user_id was set
        $customer = Customer::where('name', 'Budi Santoso')->first();
        $this->assertEquals($user->user_id, $customer->user_id);
    }

    public function test_customer_can_update_address(): void
    {
        $user = User::factory()->create([
            'alamat' => 'Old Address',
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('settings.address'), [
            'alamat' => 'New Address'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $user->refresh();
        $this->assertEquals('New Address', $user->alamat);
    }

    public function test_customer_can_upload_avatar(): void
    {
        $user = User::factory()->create([
            'avatar' => null,
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('settings.avatar'), [
            'avatar' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertFileExists(public_path($user->avatar));

        // Clean up the uploaded file
        if (file_exists(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }
    }

    public function test_dashboard_renders_saved_addresses_modal_and_hides_extra_addresses_from_main_card(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Rina Nose',
            'alamat' => 'Jl. Sudirman No. 1, Pekanbaru',
            'no_hp' => '081234567890',
        ]);

        \App\Models\UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Rumah Utama',
            'recipient_name' => 'Rina Nose',
            'recipient_phone' => '081234567890',
            'street_address' => 'Jl. Sudirman No. 1, Pekanbaru',
            'notes' => 'Pagar putih',
            'is_default' => true,
        ]);

        \App\Models\UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Kantor Cabang',
            'recipient_name' => 'Rina Kantor',
            'recipient_phone' => '081299990000',
            'street_address' => 'Jl. Tuanku Tambusai No. 88',
            'notes' => 'Lantai 2',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Detail button & modal detail should be removed
        $response->assertDontSee('btn-detail-address');
        $response->assertDontSee('id="modalDetailAddress"', false);
        $response->assertDontSee('Detail & Buku Alamat Pengiriman', false);
        // Saved addresses modal and view other addresses button should exist
        $response->assertSee('Lihat Alamat Lainnya (1)');
        $response->assertSee('id="modalSavedAddresses"', false);
        $response->assertSee('Alamat Tersimpan Lainnya');
        $response->assertSee('Kantor Cabang');
        $response->assertSee('Jl. Tuanku Tambusai No. 88');
    }

    public function test_dashboard_renders_empty_state_when_customer_has_no_address(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'User Baru Tanpa Alamat',
            'email' => 'userbaru@example.com',
            'no_hp' => '081298765432',
            'alamat' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Alamat Pengiriman');
        $response->assertSee('Tambah Alamat Sekarang');
        $response->assertDontSee('Edit Alamat Utama');
    }

    public function test_dashboard_renders_populated_state_with_full_details_when_customer_has_address(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Pelanggan Setia',
            'email' => 'setia@example.com',
            'no_hp' => '081233445566',
            'alamat' => 'Jl. Merdeka No. 45',
        ]);

        \App\Models\UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Rumah Utama',
            'recipient_name' => 'Budi Merdeka',
            'recipient_phone' => '081233445566',
            'street_address' => 'Jl. Merdeka No. 45, Kecamatan Tampan',
            'destination_area_name' => 'Tampan, Pekanbaru',
            'postal_code' => '28292',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rumah Utama');
        $response->assertSee('Default');
        $response->assertSee('Budi Merdeka');
        $response->assertSee('Jl. Merdeka No. 45, Kecamatan Tampan');
        $response->assertSee('Tampan, Pekanbaru');
        $response->assertSee('28292');
        $response->assertSee('Edit Alamat Utama');
        $response->assertDontSee('Belum Ada Alamat Pengiriman');
    }
}
