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
}
