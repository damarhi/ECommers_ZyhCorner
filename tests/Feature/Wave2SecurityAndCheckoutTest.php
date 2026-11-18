<?php

namespace Tests\Feature;

use App\Mail\PesananBaruKasirMail;
use App\Models\Cassier;
use App\Models\Diskon;
use App\Models\Kategori;
use App\Models\Otp;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Wave2SecurityAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_kasir_cannot_login_or_receive_notifications(): void
    {
        Mail::fake();

        Cassier::create([
            'name' => 'Kasir Nonaktif',
            'email' => 'deactive.kasir@zyhcorner.com',
            'password' => Hash::make('secret123'),
            'status' => 'Deactive',
        ]);

        User::factory()->create([
            'nama' => 'Kasir Nonaktif',
            'email' => 'deactive.kasir@zyhcorner.com',
            'password' => Hash::make('secret123'),
            'role' => 'kasir',
        ]);

        Cassier::create([
            'name' => 'Kasir Aktif',
            'email' => 'active.kasir@zyhcorner.com',
            'password' => Hash::make('secret123'),
            'status' => 'Active',
        ]);

        User::factory()->create([
            'nama' => 'Kasir Aktif',
            'email' => 'active.kasir@zyhcorner.com',
            'password' => Hash::make('secret123'),
            'role' => 'kasir',
        ]);

        $loginResponse = $this->post(route('login.post'), [
            'email' => 'deactive.kasir@zyhcorner.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());

        $pesanan = Pesanan::create([
            'order_number' => '#TESTWV2',
            'jenis_penjualan' => 'Online',
            'payment_method' => 'COD',
            'customer_name' => 'Pelanggan Uji',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 50000,
            'items' => [],
        ]);

        NotificationService::sendToKasir(new PesananBaruKasirMail($pesanan));

        Mail::assertSent(PesananBaruKasirMail::class, function ($mail) {
            return $mail->hasTo('active.kasir@zyhcorner.com')
                && !$mail->hasTo('deactive.kasir@zyhcorner.com');
        });
    }

    public function test_kasir_cannot_overwrite_store_settings_or_delete_orders(): void
    {
        StoreSetting::set('store_phone', '081111111111', 'Telepon Toko', 'contact');

        $kasir = User::factory()->create([
            'nama' => 'Kasir Satu',
            'email' => 'kasir1@zyhcorner.com',
            'no_hp' => '082222222222',
            'role' => 'kasir',
        ]);

        // Updating kasir personal profile must NOT overwrite global store_phone
        $this->actingAs($kasir)->post(route('settings.profile.update'), [
            'nama' => 'Kasir Satu Updated',
            'email' => 'kasir1@zyhcorner.com',
            'no_hp' => '089999999999',
        ])->assertRedirect(route('settings.index'));

        $this->assertEquals('081111111111', StoreSetting::get('store_phone'));

        // Kasir cannot POST to store settings
        $this->actingAs($kasir)->postJson(route('settings.store.update'), [
            'store_name' => 'Hacked Store',
            'store_phone' => '080000000',
            'store_email' => 'hack@example.com',
            'store_address' => 'Nowhere',
        ])->assertStatus(403);

        // Kasir cannot DELETE orders
        $order = Pesanan::create([
            'order_number' => '#NODEL1',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 25000,
            'items' => [],
        ]);

        $this->actingAs($kasir)->delete(route('orders.destroy', $order->id))
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('pesanans', ['id' => $order->id]);
    }

    public function test_mass_assignment_cannot_escalate_user_role(): void
    {
        $user = User::create([
            'nama' => 'Attacker',
            'email' => 'attacker@example.com',
            'no_hp' => '081234560001',
            'password' => 'secret123',
            'role' => 'admin',
            'google_id' => 'injected-google-id',
        ]);

        $user->refresh();
        $this->assertEquals('pelanggan', $user->role);
        $this->assertNull($user->google_id);
    }

    public function test_otp_locks_out_after_five_failed_attempts_and_session_hashes_password(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'ok'], 200),
        ]);
        config(['services.brevo.api_key' => 'mock-key']);

        $this->post(route('register.post'), [
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'no_hp' => '081277778888',
            'password' => 'mySuperSecretPass',
            'password_confirmation' => 'mySuperSecretPass',
        ])->assertRedirect(route('register.verify'));

        $pending = session('register_pending');
        $this->assertArrayNotHasKey('password', $pending);
        $this->assertArrayHasKey('password_hash', $pending);
        $this->assertTrue(Hash::check('mySuperSecretPass', $pending['password_hash']));

        $otp = Otp::where('email', 'siti@example.com')->firstOrFail();
        $wrongCode = $otp->otp_code == '111111' ? '222222' : '111111';

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('register.verify.post'), ['otp' => $wrongCode])
                ->assertSessionHasErrors('otp');
        }

        // After 5 wrong attempts, OTP record must be invalidated/deleted
        $this->assertDatabaseMissing('otp_verifications', ['email' => 'siti@example.com']);
    }

    public function test_google_oauth_rejects_mismatched_state_and_blocks_admin_auto_linking(): void
    {
        $admin = User::factory()->create([
            'email' => 'chief.admin@zyhcorner.com',
            'role' => 'admin',
            'google_id' => null,
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'tok'], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'attacker-google-sub',
                'email' => 'chief.admin@zyhcorner.com',
                'email_verified' => true,
                'name' => 'Attacker',
            ], 200),
        ]);

        // 1. Mismatched state must be rejected
        session(['google_oauth_state' => 'expected-state-token']);
        $this->get('/auth/google/callback?code=abc&state=wrong-state-token')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());

        // 2. Valid state with unlinked admin email must NOT auto-link or log in
        session(['google_oauth_state' => 'valid-state-token']);
        $this->get('/auth/google/callback?code=abc&state=valid-state-token')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());

        $admin->refresh();
        $this->assertNull($admin->google_id);
    }

    public function test_checkout_applies_active_discount_enforces_shipping_and_blocks_tampering(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $kategori = Kategori::create([
            'nama_kategori' => 'Signature Cake',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Cheese Cake Lumer',
            'harga' => 100000,
            'status_produk' => '4',
        ]);

        Diskon::create([
            'id_produk' => $produk->id_produk,
            'nama_diskon' => 'Diskon 25%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 25,
            'status' => 'Aktif',
        ]);

        // 1. Reject negative shipping_cost
        $this->actingAs($customer)->postJson(route('checkout.post'), [
            'items' => [['id' => $produk->id_produk, 'qty' => 1]],
            'shipping_cost' => -50000,
            'payment_method' => 'COD',
            'address' => 'Jl. Sudirman',
        ])->assertStatus(422);

        // 2. Reject zero or negative qty
        $this->actingAs($customer)->postJson(route('checkout.post'), [
            'items' => [['id' => $produk->id_produk, 'qty' => 0]],
            'payment_method' => 'COD',
            'address' => 'Jl. Sudirman',
        ])->assertStatus(422);

        // 3. Reject duplicate product entries exceeding total stock (3 + 3 > 4)
        $this->actingAs($customer)->postJson(route('checkout.post'), [
            'items' => [
                ['id' => $produk->id_produk, 'qty' => 3],
                ['id' => $produk->id_produk, 'qty' => 3],
            ],
            'payment_method' => 'COD',
            'address' => 'Jl. Sudirman',
        ])->assertStatus(400);

        // 4. Valid checkout applies 25% discount (75,000 * 2 = 150,000) + authoritative Grab Pekanbaru rate (45,000) = 195,000
        // even if client spoofs price: 1 and shipping_cost: 0
        $res = $this->actingAs($customer)->postJson(route('checkout.post'), [
            'items' => [
                ['id' => $produk->id_produk, 'price' => 1, 'qty' => 2],
            ],
            'payment_method' => 'COD',
            'shipping_method' => 'Grab',
            'destination_area_id' => 'ID_PKU_01',
            'shipping_cost' => 0,
            'address' => 'Jl. Sukajadi Pekanbaru',
        ]);

        $res->assertStatus(200)->assertJsonPath('success', true);

        $order = Pesanan::latest('id')->firstOrFail();
        $this->assertEquals(195000, (float) $order->total);
        $this->assertEquals(75000, (float) $order->items[0]['price']);
        $this->assertEquals(100000, (float) $order->items[0]['original_price']);
    }

    public function test_admin_cannot_create_percentage_discount_above_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $produk = Produk::create([
            'nama_produk' => 'Roti Sobek',
            'harga' => 40000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        $this->actingAs($admin)->post(route('discounts.store'), [
            'id_produk' => [$produk->id_produk],
            'nama_diskon' => 'Diskon Salah 150%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 150,
            'status' => 'Aktif',
        ])->assertSessionHasErrors('nilai_diskon');
    }

    public function test_guest_checkout_then_admin_or_kasir_login_redirects_to_admin_dashboard_and_blocks_checkout(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-checkout@zyhcorner.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $kasir = User::factory()->create([
            'email' => 'kasir-checkout@zyhcorner.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Brownies Panggang',
            'harga' => 45000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
        ]);

        // 1. Guest clicks Checkout -> redirected to /login with session('url.intended') = .../checkout
        $this->get(route('checkout'))->assertRedirect(route('login'));

        // 2. Guest logs in as Admin -> MUST go to /admin/dashboard, NOT /checkout
        $loginAdmin = $this->post(route('login.post'), [
            'email' => 'admin-checkout@zyhcorner.com',
            'password' => 'password123',
        ]);
        $loginAdmin->assertRedirect('/admin/dashboard');

        // 3. Admin cannot open /checkout, submit POST /checkout, or open customer dashboard
        $this->actingAs($admin)->get(route('checkout'))->assertStatus(403);
        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(403);
        $this->actingAs($admin)->postJson(route('checkout.post'), [
            'items' => [['id' => $produk->id_produk, 'qty' => 1]],
            'payment_method' => 'COD',
            'address' => 'Jl. Admin Test',
        ])->assertStatus(403);

        // 4. Logout, repeat guest -> /checkout -> login as Kasir -> MUST go to /admin/dashboard and block checkout
        $this->post(route('logout'));
        $this->get(route('checkout'))->assertRedirect(route('login'));

        $loginKasir = $this->post(route('login.post'), [
            'email' => 'kasir-checkout@zyhcorner.com',
            'password' => 'password123',
        ]);
        $loginKasir->assertRedirect('/admin/dashboard');

        $this->actingAs($kasir)->get(route('checkout'))->assertStatus(403);
        $this->actingAs($kasir)->postJson(route('checkout.post'), [
            'items' => [['id' => $produk->id_produk, 'qty' => 1]],
            'payment_method' => 'COD',
            'address' => 'Jl. Kasir Test',
        ])->assertStatus(403);

        $this->assertDatabaseCount('pesanans', 0);
    }

    public function test_guest_checkout_then_pelanggan_login_still_redirects_to_checkout(): void
    {
        User::factory()->create([
            'email' => 'pembeli-checkout@zyhcorner.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $this->get(route('checkout'))->assertRedirect(route('login'));

        $loginCustomer = $this->post(route('login.post'), [
            'email' => 'pembeli-checkout@zyhcorner.com',
            'password' => 'password123',
        ]);

        $loginCustomer->assertRedirect(route('checkout'));
    }
}
