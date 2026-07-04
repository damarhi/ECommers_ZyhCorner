<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CassierRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_login_redirects_to_admin_dashboard(): void
    {
        $kasir = User::factory()->create([
            'email' => 'kasir@example.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir'
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'kasir@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success', 'Selamat datang kembali Kasir! Anda berhasil masuk.');
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success', 'Selamat datang kembali Admin! Anda berhasil masuk.');
    }

    public function test_pelanggan_login_redirects_to_home(): void
    {
        $pelanggan = User::factory()->create([
            'email' => 'pelanggan@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan'
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'pelanggan@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Selamat datang kembali! Anda berhasil masuk.');
    }
}
