<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_settings_page(): void
    {
        $response = $this->get(route('settings'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_access_settings_page_with_redesigned_tabs(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'alamat' => 'Jl. Sudirman No. 10',
        ]);

        UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Alamat Utama',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'street_address' => 'Jl. Sudirman No. 10',
            'notes' => 'Pagar abu-abu dekat masjid',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertStatus(200);
        // Verify Segmented Tabs
        $response->assertSee('Profil Akun', false);
        $response->assertSee('Alamat Pengiriman', false);
        $response->assertSee('Keamanan & Sandi', false);

        // Verify Single Full Name input and IDs
        $response->assertSee('id="inputFullName"', false);
        $response->assertSee('value="Budi Santoso"', false);
        $response->assertSee('id="inputAccountEmail"', false);
        $response->assertSee('id="inputAccountPhone"', false);

        // Verify Address inputs
        $response->assertSee('id="inputBillingStreet"', false);
        $response->assertSee('Jl. Sudirman No. 10', false);
        $response->assertSee('id="inputBillingNotes"', false);
        $response->assertSee('Pagar abu-abu dekat masjid', false);

        // Verify Password inputs
        $response->assertSee('id="inputCurrentPassword"', false);
        $response->assertSee('id="inputNewPassword"', false);
        $response->assertSee('id="inputConfirmPassword"', false);

        // Verify typo correction
        $response->assertSee('Ganti Foto Profil', false);
        $response->assertDontSee('Chose Image', false);

        // Verify dummy fields removed
        $response->assertDontSee('inputBillingFirstName', false);
        $response->assertDontSee('inputBillingCompany', false);
        $response->assertDontSee('inputBillingDescribe', false);
    }

    public function test_customer_can_update_account_info(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Nama Lama',
            'email' => 'lama@example.com',
            'no_hp' => '081111111111',
        ]);

        $response = $this->actingAs($user)->postJson(route('settings.account'), [
            'nama' => 'Bambang Pamungkas',
            'email' => 'bambang@example.com',
            'no_hp' => '082222222222',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'nama' => 'Bambang Pamungkas',
            'email' => 'bambang@example.com',
            'no_hp' => '082222222222',
        ]);
    }

    public function test_customer_can_update_billing_address_and_notes(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Siti Nurhaliza',
            'alamat' => 'Alamat Lama',
        ]);

        $response = $this->actingAs($user)->postJson(route('settings.address'), [
            'alamat' => 'Jl. Melati No. 45, Pekanbaru',
            'notes' => 'Titip di pos security',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'alamat' => 'Jl. Melati No. 45, Pekanbaru',
        ]);

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'street_address' => 'Jl. Melati No. 45, Pekanbaru',
            'notes' => 'Titip di pos security',
            'is_default' => 1,
        ]);
    }

    public function test_customer_can_update_password(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->postJson(route('settings.password'), [
            'current_password' => 'oldpassword123',
            'new_password' => 'newsecretpass456',
            'confirm_password' => 'newsecretpass456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newsecretpass456', $user->password));
    }

    public function test_customer_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        $file = UploadedFile::fake()->image('profile.jpg', 200, 200);

        $response = $this->actingAs($user)->post(route('settings.avatar'), [
            'avatar' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertNotNull($user->avatar);
    }
}
