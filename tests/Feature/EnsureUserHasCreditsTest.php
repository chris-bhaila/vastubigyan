<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasCredits;
use App\Models\CreditBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EnsureUserHasCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_to_pricing_when_the_user_has_no_credits(): void
    {
        $user = User::factory()->create();

        $response = TestResponse::fromBaseResponse($this->runMiddleware($user));

        $response->assertRedirect(route('pricing'));
        $response->assertSessionHas('status', 'You have no analyses left. Buy more to continue.');
    }

    public function test_it_passes_through_when_the_user_has_credits(): void
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->create(['total' => 1, 'remaining' => 1]);

        $called = false;

        $this->runMiddleware($user, function () use (&$called) {
            $called = true;

            return response('ok');
        });

        $this->assertTrue($called);
    }

    private function runMiddleware(User $user, ?callable $next = null)
    {
        $request = Request::create('/analyses', 'POST');
        $request->setUserResolver(fn () => $user);

        return (new EnsureUserHasCredits)->handle($request, $next ?? fn ($request) => response('ok'));
    }
}
