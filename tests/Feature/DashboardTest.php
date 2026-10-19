<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_plain_user_sees_only_their_profile_on_the_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Registered users');
        $response->assertDontSee('By role');
    }

    public function test_an_admin_sees_the_user_count_but_not_the_role_breakdown(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        User::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Registered users');
        $response->assertDontSee('By role');
        $response->assertSee((string) User::count());
    }

    public function test_a_super_admin_sees_the_user_count_and_role_breakdown(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        User::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Registered users');
        $response->assertSee('By role');
        $response->assertSee((string) User::count());
    }

    public function test_every_roles_default_landing_page_is_the_dashboard(): void
    {
        foreach ([Role::User, Role::Admin, Role::SuperAdmin] as $role) {
            $user = User::factory()->create();
            $user->forceFill(['role' => $role])->save();

            $this->assertSame('dashboard', $user->dashboardRouteName());

            $response = $this->actingAs($user)->get('/');

            $response->assertRedirect(route('dashboard'));
        }
    }

    public function test_the_sidebar_has_no_super_admin_panel_link(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertDontSee('Super admin panel');
    }

    public function test_the_sidebar_has_no_admin_panel_link_for_anyone(): void
    {
        foreach ([Role::User, Role::Admin, Role::SuperAdmin] as $role) {
            $user = User::factory()->create();
            $user->forceFill(['role' => $role])->save();

            $response = $this->actingAs($user)->get(route('dashboard'));

            $response->assertDontSee('Admin panel');
        }
    }
}
