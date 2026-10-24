<?php

namespace Tests\Feature;

use App\Enums\CreditTransactionType;
use App\Enums\Tier;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreditBalancePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('credits.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_it_shows_the_users_credits_tier_next_expiry_and_batches(): void
    {
        $user = User::factory()->create();

        $soonBatch = CreditBatch::factory()->for($user)->create([
            'total' => 5,
            'remaining' => 3,
            'expires_at' => now()->addDays(10),
        ]);

        $laterBatch = CreditBatch::factory()->for($user)->create([
            'total' => 5,
            'remaining' => 5,
            'expires_at' => now()->addDays(60),
        ]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $response->assertOk();
        $response->assertViewHas('availableCredits', 8);
        $response->assertViewHas('tier', Tier::Premium);
        $response->assertViewHas('nextExpiry', fn ($nextExpiry) => $nextExpiry->isSameDay($soonBatch->expires_at));

        $batches = $response->viewData('batches');

        $this->assertCount(2, $batches);
        $this->assertSame($soonBatch->id, $batches->first()->id);
        $this->assertSame($laterBatch->id, $batches->last()->id);
    }

    public function test_expired_and_empty_batches_are_excluded(): void
    {
        $user = User::factory()->create();

        CreditBatch::factory()->for($user)->create(['total' => 4, 'remaining' => 4]);
        CreditBatch::factory()->for($user)->expired()->create(['total' => 4, 'remaining' => 4]);
        CreditBatch::factory()->for($user)->depleted()->create(['total' => 4, 'remaining' => 0]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $batches = $response->viewData('batches');

        $this->assertCount(1, $batches);
    }

    public function test_another_users_data_never_appears(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        CreditBatch::factory()->for($otherUser)->create(['total' => 10, 'remaining' => 10]);
        CreditTransaction::create([
            'user_id' => $otherUser->id,
            'credit_batch_id' => CreditBatch::factory()->for($otherUser)->create()->id,
            'amount' => -1,
            'type' => CreditTransactionType::Usage,
            'note' => 'Other user transaction',
        ]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $response->assertViewHas('availableCredits', 0);
        $this->assertCount(0, $response->viewData('batches'));
        $this->assertCount(0, $response->viewData('transactions'));
        $response->assertDontSee('Other user transaction');
    }

    public function test_history_is_paginated_newest_first(): void
    {
        $user = User::factory()->create();
        $batch = CreditBatch::factory()->for($user)->create();

        $older = CreditTransaction::create([
            'user_id' => $user->id,
            'credit_batch_id' => $batch->id,
            'amount' => 1,
            'type' => CreditTransactionType::AdminGrant,
        ]);

        // The ledger is append-only and blocks ->update(), so backdate via
        // the query builder to bypass that guard for this test only.
        DB::table('credit_transactions')->where('id', $older->id)->update(['created_at' => now()->subDay()]);

        $newer = CreditTransaction::create([
            'user_id' => $user->id,
            'credit_batch_id' => $batch->id,
            'amount' => -1,
            'type' => CreditTransactionType::Usage,
        ]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $transactions = $response->viewData('transactions');

        $this->assertSame($newer->id, $transactions->first()->id);
        $this->assertSame($older->id, $transactions->last()->id);
    }

    public function test_a_low_balance_shows_a_warning_banner(): void
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->create(['total' => 2, 'remaining' => 1]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $response->assertViewHas('lowCredits', true);
        $response->assertSee('1 credit left');
    }

    public function test_an_expiring_batch_shows_a_warning_banner(): void
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->create([
            'total' => 10,
            'remaining' => 10,
            'expires_at' => now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $response->assertViewHas('expiringSoon', true);
    }

    public function test_a_healthy_balance_shows_no_warning_banner(): void
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->create([
            'total' => 10,
            'remaining' => 10,
            'expires_at' => now()->addDays(90),
        ]);

        $response = $this->actingAs($user)->get(route('credits.index'));

        $response->assertViewHas('lowCredits', false);
        $response->assertViewHas('expiringSoon', false);
    }
}
