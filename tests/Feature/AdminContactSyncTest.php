<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_wa_number_normalizes_various_formats(): void
    {
        $this->assertEquals('628123456789', StoreSetting::getWaNumber('08123456789'));
        $this->assertEquals('628123456789', StoreSetting::getWaNumber('+628123456789'));
        $this->assertEquals('628123456789', StoreSetting::getWaNumber('628123456789'));
        $this->assertEquals('628123456789', StoreSetting::getWaNumber('8123456789'));
        $this->assertEquals('628123456789', StoreSetting::getWaNumber('0812-3456-789'));
    }

    public function test_wa_number_falls_back_to_registered_admin_phone(): void
    {
        // Pastikan tidak ada store_phone di store_settings
        StoreSetting::where('key', 'store_phone')->delete();
        StoreSetting::clearCache();

        $admin = User::factory()->create([
            'role' => 'admin',
            'no_hp' => '08991234567',
        ]);

        $this->assertEquals('628991234567', StoreSetting::getWaNumber());
    }

    public function test_instagram_url_normalizes_correctly(): void
    {
        $this->assertEquals('https://instagram.com/zyhcorner', StoreSetting::getInstagramUrl('@zyhcorner'));
        $this->assertEquals('https://instagram.com/zyhcorner', StoreSetting::getInstagramUrl('zyhcorner'));
        $this->assertEquals('https://instagram.com/zyhcorner', StoreSetting::getInstagramUrl('https://instagram.com/zyhcorner'));
        $this->assertEquals('https://instagram.com/zyhcorner', StoreSetting::getInstagramUrl('http://instagram.com/zyhcorner/'));
    }

    public function test_updating_admin_profile_syncs_phone_to_store_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_hp' => '081111111111',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.profile.update'), [
            'nama' => 'Admin Utama',
            'email' => $admin->email,
            'no_hp' => '087788990011',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('users', [
            'user_id' => $admin->user_id,
            'no_hp' => '087788990011',
        ]);

        $this->assertEquals('087788990011', StoreSetting::get('store_phone'));
        $this->assertEquals('6287788990011', StoreSetting::getWaNumber());
    }

    public function test_updating_store_info_syncs_phone_to_admin_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_hp' => '081111111111',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.store.update'), [
            'store_name' => 'Zyh Corner Official',
            'store_phone' => '085566778899',
            'store_email' => 'support@zyhcorner.com',
            'store_address' => 'Jl. Sudirman No. 45, Pekanbaru',
            'social_instagram' => 'https://instagram.com/zyhcorner.official',
        ]);

        $response->assertRedirect(route('settings.index') . '#store-tab');

        $admin->refresh();
        $this->assertEquals('085566778899', $admin->no_hp);
        $this->assertEquals('085566778899', StoreSetting::get('store_phone'));
        $this->assertEquals('6285566778899', StoreSetting::getWaNumber());
        $this->assertEquals('https://instagram.com/zyhcorner.official', StoreSetting::get('social_instagram'));
    }

    public function test_customer_views_render_admin_phone_and_instagram(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_hp' => '081299887766',
        ]);

        StoreSetting::set('store_phone', '081299887766', 'No WA', 'contact');
        StoreSetting::set('social_instagram', 'https://instagram.com/zyhcorner', 'IG', 'social');
        StoreSetting::clearCache();

        // 1. Halaman Kontak
        $contactResponse = $this->get(route('contact'));
        $contactResponse->assertStatus(200);
        $contactResponse->assertSee('tel:+6281299887766', false);
        $contactResponse->assertSee('https://instagram.com/zyhcorner', false);

        // 2. Halaman FAQ
        $faqResponse = $this->get(route('faq'));
        $faqResponse->assertStatus(200);
        $faqResponse->assertSee('https://wa.me/6281299887766', false);

        // 3. Layout Pelanggan (window.storePhone, navbar, drawer & footer WhatsApp)
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertDontSee('window.storePhone = "6281298765432"', false);
        $homeResponse->assertDontSee('(+62) 8123456789', false);
        $homeResponse->assertSee('window.storePhone = "6281299887766"', false);
        $homeResponse->assertSee('class="nav-phone"', false);
        $homeResponse->assertSee('href="https://wa.me/6281299887766"', false);
    }
}
