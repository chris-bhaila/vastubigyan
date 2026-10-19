<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_cannot_be_set_via_registration_request(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'role' => Role::SuperAdmin->value,
        ]);

        $response->assertCreated();

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(Role::User));
    }

    public function test_role_cannot_be_set_via_profile_update_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'role' => Role::SuperAdmin->value,
        ]);

        $response->assertOk();

        $this->assertTrue($user->fresh()->hasRole(Role::User));
    }
}
