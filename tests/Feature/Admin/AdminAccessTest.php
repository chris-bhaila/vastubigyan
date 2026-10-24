<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_from_every_admin_route(): void
    {
        $package = Package::factory()->create();
        $purchase = Purchase::factory()->paid()->create();
        $targetUser = User::factory()->create();

        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $this->get(route('admin.users.show', $targetUser))->assertRedirect(route('login'));
        $this->post(route('admin.users.credits.store', $targetUser))->assertRedirect(route('login'));
        $this->get(route('admin.purchases.index'))->assertRedirect(route('login'));
        $this->post(route('admin.purchases.refund', $purchase))->assertRedirect(route('login'));
        $this->get(route('admin.analyses.index'))->assertRedirect(route('login'));
        $this->get(route('admin.packages.index'))->assertRedirect(route('login'));
        $this->get(route('admin.packages.edit', $package))->assertRedirect(route('login'));
        $this->put(route('admin.packages.update', $package))->assertRedirect(route('login'));
    }

    public function test_a_plain_user_gets_403_on_every_admin_route(): void
    {
        $plain = $this->userWithRole(Role::User);
        $package = Package::factory()->create();
        $purchase = Purchase::factory()->paid()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($plain)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($plain)->get(route('admin.users.show', $targetUser))->assertForbidden();
        $this->actingAs($plain)->post(route('admin.users.credits.store', $targetUser))->assertForbidden();
        $this->actingAs($plain)->get(route('admin.purchases.index'))->assertForbidden();
        $this->actingAs($plain)->post(route('admin.purchases.refund', $purchase))->assertForbidden();
        $this->actingAs($plain)->get(route('admin.analyses.index'))->assertForbidden();
        $this->actingAs($plain)->get(route('admin.packages.index'))->assertForbidden();
        $this->actingAs($plain)->get(route('admin.packages.edit', $package))->assertForbidden();
        $this->actingAs($plain)->put(route('admin.packages.update', $package))->assertForbidden();
    }

    public function test_an_admin_can_read_but_cannot_mutate_or_see_packages(): void
    {
        $admin = $this->userWithRole(Role::Admin);
        $package = Package::factory()->create();
        $purchase = Purchase::factory()->paid()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.show', $targetUser))->assertOk();
        $this->actingAs($admin)->get(route('admin.purchases.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.analyses.index'))->assertOk();

        $this->actingAs($admin)->post(route('admin.users.credits.store', $targetUser), [
            'count' => 1,
            'expiry_days' => 30,
            'reason' => 'test grant',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'test refund',
            'confirm' => '1',
        ])->assertForbidden();

        $this->actingAs($admin)->get(route('admin.packages.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.packages.edit', $package))->assertForbidden();

        $this->actingAs($admin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'analysis_count' => $package->analysis_count,
            'price' => $package->price,
            'validity_days' => $package->validity_days,
        ])->assertForbidden();
    }

    public function test_a_super_admin_can_do_everything(): void
    {
        $superAdmin = $this->userWithRole(Role::SuperAdmin);
        $package = Package::factory()->create();
        $purchase = Purchase::factory()->paid()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.users.show', $targetUser))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.purchases.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.analyses.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.packages.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.packages.edit', $package))->assertOk();

        $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $targetUser), [
            'count' => 1,
            'expiry_days' => 30,
            'reason' => 'test grant',
        ])->assertRedirect();

        $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'test refund',
            'confirm' => '1',
        ])->assertRedirect();

        $this->actingAs($superAdmin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'analysis_count' => $package->analysis_count,
            'price' => $package->price,
            'validity_days' => $package->validity_days,
        ])->assertRedirect();
    }

    private function userWithRole(Role $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
