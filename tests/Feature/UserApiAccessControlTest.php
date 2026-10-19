<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_users(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_guests_cannot_view_a_user(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/users/{$user->id}")->assertUnauthorized();
    }

    public function test_guests_cannot_update_a_user(): void
    {
        $user = User::factory()->create();

        $this->putJson("/api/users/{$user->id}", ['name' => 'Hacked'])->assertUnauthorized();
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_guests_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/users/{$user->id}")->assertUnauthorized();
        $this->assertModelExists($user);
    }

    public function test_registration_remains_public(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();
    }

    public function test_a_plain_user_cannot_list_all_users(): void
    {
        $user = User::factory()->create();
        User::factory()->count(3)->create();

        $this->actingAs($user)->getJson('/api/users')->assertForbidden();
    }

    public function test_a_plain_user_can_view_their_own_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson("/api/users/{$user->id}")->assertOk();
    }

    public function test_a_plain_user_cannot_view_another_users_record(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->getJson("/api/users/{$other->id}")->assertForbidden();
    }

    public function test_a_plain_user_cannot_update_another_users_record(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['name' => 'Original']);

        $this->actingAs($user)->putJson("/api/users/{$other->id}", ['name' => 'Hacked'])->assertForbidden();
        $this->assertSame('Original', $other->fresh()->name);
    }

    public function test_a_plain_user_cannot_delete_any_account_including_their_own(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson("/api/users/{$user->id}")->assertForbidden();
        $this->assertModelExists($user);
    }

    public function test_an_admin_can_list_and_view_and_update_and_delete_any_user(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();
        $target = User::factory()->create();

        $this->actingAs($admin)->getJson('/api/users')->assertOk();
        $this->actingAs($admin)->getJson("/api/users/{$target->id}")->assertOk();
        $this->actingAs($admin)->putJson("/api/users/{$target->id}", ['name' => 'Renamed'])->assertOk();
        $this->assertSame('Renamed', $target->fresh()->name);
        $this->actingAs($admin)->deleteJson("/api/users/{$target->id}")->assertNoContent();
        $this->assertModelMissing($target);
    }
}
