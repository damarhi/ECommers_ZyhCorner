<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_settings_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Admin', false);
    }

    public function test_admin_can_update_profile_info(): void
    {
        $admin = User::factory()->create([
            'nama' => 'Admin Lama',
            'email' => 'adminlama@gmail.com',
            'no_hp' => '081111111111',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.profile.update'), [
            'nama' => 'Admin Baru',
            'email' => 'adminbaru@gmail.com',
            'no_hp' => '082222222222',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('users', [
            'user_id' => $admin->user_id,
            'nama' => 'Admin Baru',
            'email' => 'adminbaru@gmail.com',
        ]);
    }

    public function test_admin_can_update_password(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('oldpassword'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.password.update'), [
            'current_password' => 'oldpassword',
            'new_password' => 'newsecretpassword',
            'new_password_confirmation' => 'newsecretpassword',
        ]);

        $response->assertRedirect(route('settings.index'));

        $admin->refresh();
        $this->assertTrue(Hash::check('newsecretpassword', $admin->password));
    }

    public function test_admin_can_update_store_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('settings.store.update'), [
            'store_name' => 'Zyh Corner Official Store',
            'store_phone' => '081299998888',
            'store_email' => 'help@zyhcorner.com',
            'store_address' => 'Jl. Merdeka No. 45, Subang',
            'store_hours' => 'Senin - Sabtu (08:00 - 20:00)',
        ]);

        $response->assertRedirect(route('settings.index'));
        $this->assertEquals('Zyh Corner Official Store', session('store_name'));
    }
}
