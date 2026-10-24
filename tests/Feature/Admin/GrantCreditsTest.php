<?php

namespace Tests\Feature\Admin;

use App\Enums\CreditTransactionType;
use App\Enums\Role;
use App\Enums\Tier;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrantCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_granting_credits_creates_a_batch_and_a_ledger_row_with_the_actor_and_reason(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $user), [
            'count' => 5,
            'expiry_days' => 90,
            'reason' => 'Goodwill gesture',
        ])->assertRedirect();

        $batch = CreditBatch::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(5, $batch->total);
        $this->assertSame(5, $batch->remaining);

        $transaction = CreditTransaction::where('credit_batch_id', $batch->id)->firstOrFail();
        $this->assertSame(CreditTransactionType::AdminGrant, $transaction->type);
        $this->assertSame($superAdmin->id, $transaction->actor_id);
        $this->assertSame('Goodwill gesture', $transaction->note);
        $this->assertSame(5, $transaction->amount);
    }

    public function test_granting_credits_updates_the_users_balance_and_tier(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();

        $this->assertSame(Tier::General, $user->tier());

        $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $user), [
            'count' => 3,
            'expiry_days' => 90,
            'reason' => 'Goodwill gesture',
        ]);

        $user = $user->fresh();
        $this->assertSame(3, $user->availableCredits());
        $this->assertSame(Tier::Premium, $user->tier());
    }

    public function test_granting_credits_is_rejected_without_a_reason(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $user), [
            'count' => 3,
            'expiry_days' => 90,
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(0, CreditBatch::where('user_id', $user->id)->count());
    }

    public function test_granting_credits_is_rejected_with_an_out_of_range_count(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $user), [
            'count' => config('credits.grant_count_max', 100) + 1,
            'expiry_days' => 90,
            'reason' => 'Too many',
        ]);

        $response->assertSessionHasErrors('count');
        $this->assertSame(0, CreditBatch::where('user_id', $user->id)->count());
    }

    public function test_granting_credits_is_rejected_with_an_out_of_range_expiry(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.credits.store', $user), [
            'count' => 3,
            'expiry_days' => config('credits.grant_expiry_days_max', 730) + 1,
            'reason' => 'Too long',
        ]);

        $response->assertSessionHasErrors('expiry_days');
        $this->assertSame(0, CreditBatch::where('user_id', $user->id)->count());
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => Role::SuperAdmin])->save();

        return $user;
    }
}
