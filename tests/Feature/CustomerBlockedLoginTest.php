<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerBlockedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_customer_can_login_normally(): void
    {
        $user = User::factory()->create([
            'email' => 'pelanggan@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        Customer::create([
            'user_id' => $user->user_id,
            'name' => $user->nama,
            'contact' => $user->no_hp,
            'status' => 'Active',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'pelanggan@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
    }

    public function test_blocked_customer_via_user_id_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        Customer::create([
            'user_id' => $user->user_id,
            'name' => $user->nama,
            'contact' => $user->no_hp,
            'status' => 'Blocked',
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'blocked@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_blocked_customer_via_phone_fallback_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'blockedphone@example.com',
            'no_hp' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        // Customer created without user_id (e.g. legacy/offline sync), but matching contact
        Customer::create([
            'user_id' => null,
            'name' => 'Legacy Customer',
            'contact' => '081234567890',
            'status' => 'Blocked',
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'blockedphone@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_and_kasir_are_not_affected_by_blocked_check(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_unblocked_customer_can_login_again(): void
    {
        $user = User::factory()->create([
            'email' => 'restored@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $customer = Customer::create([
            'user_id' => $user->user_id,
            'name' => $user->nama,
            'contact' => $user->no_hp,
            'status' => 'Blocked',
        ]);

        // First attempt fails
        $firstAttempt = $this->post(route('login.post'), [
            'email' => 'restored@example.com',
            'password' => 'password123',
        ]);
        $firstAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin changes status back to Active
        $customer->update(['status' => 'Active']);

        // Second attempt succeeds
        $secondAttempt = $this->post(route('login.post'), [
            'email' => 'restored@example.com',
            'password' => 'password123',
        ]);
        $secondAttempt->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_active_session_is_terminated_by_middleware_when_customer_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'activeuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $customer = Customer::create([
            'user_id' => $user->user_id,
            'name' => $user->nama,
            'status' => 'Active',
        ]);

        // Customer accesses while Active
        $response = $this->actingAs($user)->get('/');
        $response->assertOk();

        // Admin blocks customer in database
        $customer->update(['status' => 'Blocked']);

        // Next request triggers middleware force-logout
        $nextResponse = $this->actingAs($user)->get('/');
        $nextResponse->assertRedirect(route('login'));
        $nextResponse->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_blocked_customer_cannot_login_via_google_callback(): void
    {
        $user = User::factory()->create([
            'email' => 'googleblocked@example.com',
            'google_id' => 'google-blocked-123',
            'role' => 'pelanggan',
        ]);

        Customer::create([
            'user_id' => $user->user_id,
            'name' => $user->nama,
            'status' => 'Blocked',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://oauth2.googleapis.com/token' => \Illuminate\Support\Facades\Http::response([
                'access_token' => 'mock-access-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => \Illuminate\Support\Facades\Http::response([
                'sub' => 'google-blocked-123',
                'email' => 'googleblocked@example.com',
                'name' => 'Google Blocked',
            ], 200),
        ]);

        $response = $this->get('/login?code=mock-auth-code');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
