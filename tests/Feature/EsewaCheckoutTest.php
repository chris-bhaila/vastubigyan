<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Enums\Role;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EsewaCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_a_pending_purchase_and_returns_the_redirect_form(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create(['price' => 1000, 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('checkout.store', $package));

        $response->assertOk();
        $response->assertSee('esewa-checkout-form', escape: false);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 1000,
            'gateway' => 'esewa',
            'status' => PurchaseStatus::Pending->value,
        ]);
    }

    public function test_inactive_packages_cannot_be_bought(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->post(route('checkout.store', $package));

        $response->assertNotFound();
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_only_the_owner_or_an_admin_can_view_a_purchase(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();
        $purchase = Purchase::factory()->for($owner)->create();

        $this->actingAs($owner)->get(route('purchases.show', $purchase))->assertOk();
        $this->actingAs($admin)->get(route('purchases.show', $purchase))->assertOk();
        $this->actingAs($stranger)->get(route('purchases.show', $purchase))->assertForbidden();
    }
}
