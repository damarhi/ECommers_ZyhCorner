<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cassier;
use Illuminate\Support\Facades\Hash;

class CassierSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_cassier_automatically_creates_user_record_with_kasir_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('cassiers.store'), [
            'name' => 'Budi Kasir',
            'email' => 'budi.kasir@example.com',
            'password' => 'password123',
            'status' => 'Active'
        ]);

        $response->assertRedirect(route('cassiers.index'));

        // Assert Cassier is created
        $this->assertDatabaseHas('cassiers', [
            'name' => 'Budi Kasir',
            'email' => 'budi.kasir@example.com',
            'status' => 'Active'
        ]);

        // Assert corresponding User is created
        $this->assertDatabaseHas('users', [
            'nama' => 'Budi Kasir',
            'email' => 'budi.kasir@example.com',
            'role' => 'kasir',
            'no_hp' => null
        ]);

        // Verify password hashes check out
        $user = User::where('email', 'budi.kasir@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_adding_cassier_fails_if_email_already_taken_in_users(): void
    {
        // Create an existing user with the same email
        User::factory()->create([
            'email' => 'taken@example.com'
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('cassiers.store'), [
            'name' => 'Budi Kasir',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'status' => 'Active'
        ]);

        // Assert redirection back with validation errors
        $response->assertSessionHasErrors('email');
        
        // Assert no Cassier or secondary User is created
        $this->assertDatabaseMissing('cassiers', [
            'name' => 'Budi Kasir',
            'email' => 'taken@example.com'
        ]);
    }

    public function test_updating_cassier_automatically_updates_corresponding_user_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Pre-create Cassier and corresponding User
        $cassier = Cassier::create([
            'name' => 'Old Name',
            'email' => 'old.email@example.com',
            'password' => Hash::make('old_password'),
            'status' => 'Active'
        ]);

        $user = User::create([
            'nama' => 'Old Name',
            'email' => 'old.email@example.com',
            'password' => Hash::make('old_password'),
            'role' => 'kasir',
            'no_hp' => null
        ]);

        // Update the cashier
        $response = $this->put(route('cassiers.update', $cassier->id), [
            'name' => 'New Name',
            'email' => 'new.email@example.com',
            'password' => 'new_password123',
            'status' => 'Deactive'
        ]);

        $response->assertRedirect(route('cassiers.index'));

        // Assert Cassier is updated
        $cassier->refresh();
        $this->assertEquals('New Name', $cassier->name);
        $this->assertEquals('new.email@example.com', $cassier->email);
        $this->assertEquals('Deactive', $cassier->status);

        // Assert User is updated
        $user->refresh();
        $this->assertEquals('New Name', $user->nama);
        $this->assertEquals('new.email@example.com', $user->email);
        $this->assertTrue(Hash::check('new_password123', $user->password));
    }

    public function test_deleting_cassier_automatically_deletes_corresponding_user_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Pre-create Cassier and corresponding User
        $cassier = Cassier::create([
            'name' => 'Delete Me',
            'email' => 'delete.me@example.com',
            'password' => Hash::make('password'),
            'status' => 'Active'
        ]);

        $user = User::create([
            'nama' => 'Delete Me',
            'email' => 'delete.me@example.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'no_hp' => null
        ]);

        $response = $this->delete(route('cassiers.destroy', $cassier->id));

        $response->assertRedirect(route('cassiers.index'));

        // Assert both are deleted from DB
        $this->assertDatabaseMissing('cassiers', [
            'email' => 'delete.me@example.com'
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'delete.me@example.com'
        ]);
    }
}
