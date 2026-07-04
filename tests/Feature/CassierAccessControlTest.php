<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CassierAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_access_allowed_pages(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir);

        // Access orders
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);

        // Access products
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);

        // Access returs
        $response = $this->get(route('returs.index'));
        $response->assertStatus(200);
    }

    public function test_kasir_is_redirected_from_restricted_pages(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir);

        // Try to access dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman dashboard.');

        // Try to access categories
        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');

        // Try to access customers
        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');

        // Try to access cassiers
        $response = $this->get(route('cassiers.index'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke halaman ini.');
    }

    public function test_admin_can_access_all_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Access dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        // Access categories
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);

        // Access customers
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);

        // Access cassiers
        $response = $this->get(route('cassiers.index'));
        $response->assertStatus(200);

        // Access orders
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);
    }
}
