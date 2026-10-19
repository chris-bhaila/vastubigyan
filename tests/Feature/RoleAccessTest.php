<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_panel_route_no_longer_exists(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertNotFound();
    }

    public function test_the_super_admin_panel_route_no_longer_exists(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertNotFound();
    }
}
