<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_super_admin_panel(): void
    {
        $response = $this->get('/super-admin');

        $response->assertRedirect(route('login'));
    }

    public function test_user_gets_forbidden_on_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_user_gets_forbidden_on_super_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/super-admin');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_admin_gets_forbidden_on_super_admin_panel(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $response = $this->actingAs($admin)->get('/super-admin');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_admin_panel(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
    }

    public function test_super_admin_can_access_super_admin_panel(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertOk();
    }
}
