<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetUserRoleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_a_users_role(): void
    {
        $user = User::factory()->create(['email' => 'promote-me@example.com']);

        $this->artisan('user:set-role', ['email' => $user->email, 'role' => 'admin'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->hasRole(Role::Admin));
    }

    public function test_it_rejects_an_invalid_role(): void
    {
        $user = User::factory()->create();

        $this->artisan('user:set-role', ['email' => $user->email, 'role' => 'superuser'])
            ->assertFailed();

        $this->assertTrue($user->fresh()->hasRole(Role::User));
    }

    public function test_it_fails_for_an_unknown_email(): void
    {
        $this->artisan('user:set-role', ['email' => 'nobody@example.com', 'role' => 'admin'])
            ->assertFailed();
    }
}
