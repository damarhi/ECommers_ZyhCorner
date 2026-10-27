<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Produk;

class CustomerAddressManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_retrieve_saved_addresses()
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'street_address' => 'Jl. Anggrek No. 10',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('customer.addresses.index'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'addresses');
        $response->assertJsonPath('addresses.0.label', 'Rumah');
    }

    public function test_customer_can_add_new_address_and_becomes_default_if_first()
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'alamat' => null,
        ]);

        $payload = [
            'label' => 'Kantor',
            'recipient_name' => 'Budi Kantor',
            'recipient_phone' => '081299998888',
            'destination_area_name' => 'Sukajadi, Pekanbaru',
            'destination_area_id' => 'ID_PKU_01',
            'postal_code' => '28121',
            'street_address' => 'Gedung Riau Lt. 3, Jl. Sudirman',
            'is_default' => 0, // Should be forced to true because it is the first address
        ];

        $response = $this->actingAs($user)->postJson(route('customer.addresses.store'), $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'label' => 'Kantor',
            'is_default' => 1,
            'postal_code' => '28121',
        ]);

        $user->refresh();
        $this->assertEquals('Gedung Riau Lt. 3, Jl. Sudirman', $user->alamat);
    }

    public function test_setting_new_address_as_default_resets_previous_default()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        $addr1 = UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi',
            'recipient_phone' => '081234567890',
            'street_address' => 'Alamat 1',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('customer.addresses.store'), [
            'label' => 'Kantor',
            'recipient_name' => 'Budi Kantor',
            'recipient_phone' => '081299998888',
            'street_address' => 'Alamat 2',
            'is_default' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertEquals(0, $addr1->fresh()->is_default);
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'label' => 'Kantor',
            'is_default' => 1,
        ]);
    }

    public function test_customer_cannot_modify_or_delete_other_customer_address()
    {
        $user1 = User::factory()->create(['role' => 'pelanggan']);
        $user2 = User::factory()->create(['role' => 'pelanggan']);

        $addr = UserAddress::create([
            'user_id' => $user1->user_id,
            'label' => 'Rumah User 1',
            'recipient_name' => 'User 1',
            'recipient_phone' => '081234567890',
            'street_address' => 'Alamat User 1',
            'is_default' => true,
        ]);

        // User 2 tries to update User 1's address
        $response = $this->actingAs($user2)->putJson(route('customer.addresses.update', $addr->id), [
            'label' => 'Hacked',
            'recipient_name' => 'Hacker',
            'recipient_phone' => '08000000',
            'street_address' => 'Hacked Street',
        ]);
        $response->assertStatus(404);

        // User 2 tries to delete User 1's address
        $delResponse = $this->actingAs($user2)->deleteJson(route('customer.addresses.destroy', $addr->id));
        $delResponse->assertStatus(404);

        $this->assertDatabaseHas('user_addresses', ['id' => $addr->id]);
    }

    public function test_deleting_default_address_promotes_next_address_to_default()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        $addr1 = UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Alamat 1',
            'recipient_name' => 'Budi',
            'recipient_phone' => '0812',
            'street_address' => 'Alamat 1',
            'is_default' => false,
        ]);

        $addr2 = UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Alamat 2',
            'recipient_name' => 'Budi',
            'recipient_phone' => '0812',
            'street_address' => 'Alamat 2',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('customer.addresses.destroy', $addr2->id));
        $response->assertStatus(200);

        $this->assertDatabaseMissing('user_addresses', ['id' => $addr2->id]);
        $this->assertEquals(1, $addr1->fresh()->is_default);
    }

    public function test_checkout_page_displays_saved_address_selector()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        UserAddress::create([
            'user_id' => $user->user_id,
            'label' => 'Rumah Mewah',
            'recipient_name' => 'Budi Sucipto',
            'recipient_phone' => '081234567890',
            'street_address' => 'Jl. Sudirman No. 88, Pekanbaru',
            'destination_area_name' => 'Bukit Raya, Pekanbaru',
            'postal_code' => '28288',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertSee('Pilih dari Alamat Tersimpan:');
        $response->assertSee('Rumah Mewah');
        $response->assertSee('Jl. Sudirman No. 88, Pekanbaru');
        $response->assertSee('28288');
        $response->assertSee('Tulis Alamat Baru');
    }

    public function test_checkout_saves_new_address_when_save_address_is_checked()
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'nama' => 'Rina',
            'no_hp' => '081122334455'
        ]);

        $product = Produk::create([
            'nama_produk' => 'Bolu Kemojo Pandan',
            'harga' => 45000,
            'stok' => 10,
            'status_produk' => '10',
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.post'), [
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'price' => 45000,
                    'qty' => 1,
                ]
            ],
            'recipient_name' => 'Ibu Rina',
            'recipient_phone' => '081234567890',
            'destination_area_name' => 'Tampan, Pekanbaru',
            'destination_area_id' => 'ID_PKU_02',
            'postal_code' => '28292',
            'address' => 'Jl. HR Soebrantas No. 45',
            'payment_method' => 'COD',
            'shipping_method' => 'Maxim',
            'shipping_cost' => 15000,
            'save_address' => true,
            'address_label' => 'Rumah Mertua',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'label' => 'Rumah Mertua',
            'recipient_name' => 'Ibu Rina',
            'postal_code' => '28292',
            'street_address' => 'Jl. HR Soebrantas No. 45',
        ]);
    }

    public function test_customer_can_create_and_update_address_with_complete_fields_including_notes_and_gps()
    {
        $user = User::factory()->create(['role' => 'pelanggan']);

        // 1. Create with notes and coordinates
        $createPayload = [
            'label' => 'Villa Mewah',
            'recipient_name' => 'Budi Hartono',
            'recipient_phone' => '081234567899',
            'destination_area_name' => 'Senapelan, Pekanbaru',
            'destination_area_id' => 'ID_PKU_08',
            'postal_code' => '28153',
            'street_address' => 'Jl. Jenderal Ahmad Yani No. 12',
            'notes' => 'Pagar hitam tinggi, samping minimarket',
            'latitude' => 0.5332,
            'longitude' => 101.4421,
            'is_default' => 1,
        ];

        $createRes = $this->actingAs($user)->postJson(route('customer.addresses.store'), $createPayload);
        $createRes->assertStatus(200);
        $createRes->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'label' => 'Villa Mewah',
            'notes' => 'Pagar hitam tinggi, samping minimarket',
            'latitude' => 0.5332,
            'longitude' => 101.4421,
            'is_default' => 1,
        ]);

        $address = UserAddress::where('user_id', $user->user_id)->first();

        // 2. Update all fields including notes and coordinates
        $updatePayload = [
            'label' => 'Villa Utama',
            'recipient_name' => 'Budi H. Updated',
            'recipient_phone' => '081299990000',
            'destination_area_name' => 'Bukit Raya, Pekanbaru',
            'destination_area_id' => 'ID_PKU_04',
            'postal_code' => '28288',
            'street_address' => 'Jl. Sudirman Kompleks Puri No. 5',
            'notes' => 'Masuk gerbang timur, belok kanan',
            'latitude' => 0.5090,
            'longitude' => 101.4550,
            'is_default' => 1,
        ];

        $updateRes = $this->actingAs($user)->putJson(route('customer.addresses.update', $address->id), $updatePayload);
        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'label' => 'Villa Utama',
            'recipient_name' => 'Budi H. Updated',
            'recipient_phone' => '081299990000',
            'destination_area_name' => 'Bukit Raya, Pekanbaru',
            'notes' => 'Masuk gerbang timur, belok kanan',
            'latitude' => 0.5090,
            'longitude' => 101.4550,
        ]);
    }
}
