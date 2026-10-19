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

    public function test_an_admin_sees_every_users_purchases(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $alice = User::factory()->create(['name' => 'Alice Example']);
        $bob = User::factory()->create(['name' => 'Bob Example']);

        $alicePurchase = Purchase::factory()->for($alice)->create();
        $bobPurchase = Purchase::factory()->for($bob)->create();

        $response = $this->actingAs($admin)->get(route('purchases.index'));

        $response->assertOk();
        $response->assertSee('Alice Example');
        $response->assertSee('Bob Example');

        $purchases = $response->viewData('purchases');
        $this->assertCount(2, $purchases);
        $this->assertTrue($purchases->contains('id', $alicePurchase->id));
        $this->assertTrue($purchases->contains('id', $bobPurchase->id));
    }

    public function test_a_super_admin_sees_every_users_purchases_too(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();

        Purchase::factory()->for(User::factory())->create();
        Purchase::factory()->for(User::factory())->create();

        $response = $this->actingAs($superAdmin)->get(route('purchases.index'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('purchases'));
    }

    public function test_a_plain_user_does_not_see_other_users_names_on_the_history_page(): void
    {
        $user = User::factory()->create();
        Purchase::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('purchases.index'));

        $this->assertFalse($response->viewData('showBuyer'));
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
