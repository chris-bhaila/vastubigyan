<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingBuyNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_pricing_pages_buy_now_buttons_point_at_a_real_checkout_form(): void
    {
        $this->seed(PackageSeeder::class);

        $premiumApp = Package::where('slug', 'premium-app')->firstOrFail();
        $webAppPremium = Package::where('slug', 'web-app-premium')->firstOrFail();

        $response = $this->get(route('pricing'));

        $response->assertOk();
        $response->assertSee('action="'.route('checkout.store', $premiumApp).'"', escape: false);
        $response->assertSee('action="'.route('checkout.store', $webAppPremium).'"', escape: false);
    }

    public function test_buying_the_premium_app_plan_creates_a_pending_purchase_for_its_real_price(): void
    {
        $this->seed(PackageSeeder::class);

        $package = Package::where('slug', 'premium-app')->firstOrFail();
        $this->assertSame(1100, $package->price);
        $this->assertSame(1, $package->analysis_count);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout.store', $package));

        $response->assertOk();
        $response->assertSee('esewa-checkout-form', escape: false);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 1100,
            'gateway' => 'esewa',
            'status' => PurchaseStatus::Pending->value,
        ]);
    }

    public function test_the_free_plan_has_no_buy_now_form(): void
    {
        $this->seed(PackageSeeder::class);

        $response = $this->get(route('pricing'));

        // Only the two paid plans (premium-app, web-app-premium) should
        // render a checkout form; the free plan keeps its plain link.
        $formCount = substr_count($response->getContent(), 'action="'.url('/checkout').'/');

        $this->assertSame(2, $formCount);
    }
}
