<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_sees_only_their_own_purchases_newest_first(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $older = Purchase::factory()->for($user)->create(['created_at' => now()->subDays(2)]);
        $newer = Purchase::factory()->for($user)->create(['created_at' => now()->subDay()]);
        Purchase::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('purchases.index'));

        $response->assertOk();
        $purchases = $response->viewData('purchases');

        $this->assertCount(2, $purchases);
        $this->assertSame($newer->id, $purchases->first()->id);
    }

    public function test_an_admin_cannot_use_the_personal_purchase_history_page(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        // Admins view every user's purchases at /admin/purchases instead;
        // this page is a plain-user concern since they never buy anything.
        $this->actingAs($admin)->get(route('purchases.index'))->assertForbidden();
    }

    public function test_a_super_admin_cannot_use_the_personal_purchase_history_page_either(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        $this->actingAs($superAdmin)->get(route('purchases.index'))->assertForbidden();
    }

    public function test_an_empty_history_shows_a_friendly_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchases.index'));

        $response->assertOk();
        $response->assertSee("You haven't bought any credits yet.");
    }

    public function test_a_paid_purchase_has_a_viewable_invoice(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create(['name' => 'Web + App Premium', 'price' => 29400]);
        $purchase = Purchase::factory()->for($user)->paid()->create([
            'package_id' => $package->id,
            'amount' => 29400,
            'gateway_ref' => 'TXN-999',
        ]);

        $response = $this->actingAs($user)->get(route('purchases.invoice', $purchase));

        $response->assertOk();
        $response->assertSee('Web + App Premium');
        $response->assertSee('Nrs. 29,400');
        $response->assertSee('TXN-999');
    }

    public function test_a_pending_purchase_has_no_invoice(): void
    {
        $user = User::factory()->create();
        $purchase = Purchase::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('purchases.invoice', $purchase));

        $response->assertNotFound();
    }

    public function test_only_the_owner_or_an_admin_can_view_the_invoice(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $purchase = Purchase::factory()->for($owner)->paid()->create();

        $this->actingAs($owner)->get(route('purchases.invoice', $purchase))->assertOk();
        $this->actingAs($admin)->get(route('purchases.invoice', $purchase))->assertOk();
        $this->actingAs($stranger)->get(route('purchases.invoice', $purchase))->assertForbidden();
    }
}
