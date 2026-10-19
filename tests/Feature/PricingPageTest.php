<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_page_shows_each_plan(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();

        $visibleText = preg_replace('/\s+/', ' ', strip_tags($response->getContent()));

        foreach (config('pricing.plans') as $plan) {
            $this->assertStringContainsString($plan['name'], $visibleText);
            $this->assertStringContainsString('Nrs. '.number_format($plan['price']), $visibleText);
        }

        $this->assertStringContainsString('BEST OFFER', $visibleText);
    }

    public function test_guest_sees_the_pricing_page_without_the_sidebar(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();
        $response->assertDontSee('data-sidebar', escape: false);
    }

    public function test_authenticated_user_keeps_the_sidebar_on_the_pricing_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertOk();
        $response->assertSee('data-sidebar', escape: false);
        $response->assertSee(route('dashboard'), escape: false);
    }
}
