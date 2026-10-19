<?php

namespace Tests\Feature;

use App\Enums\CreditTransactionType;
use App\Enums\PurchaseStatus;
use App\Enums\Tier;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreditService $credits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credits = app(CreditService::class);
    }

    public function test_a_user_with_no_batches_is_general(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Tier::General, $user->tier());
        $this->assertFalse($user->isPremium());
        $this->assertSame(0, $user->availableCredits());
    }

    public function test_grant_from_purchase_makes_the_user_premium_with_the_right_credits(): void
    {
        $package = Package::factory()->create(['analysis_count' => 10, 'validity_days' => 365]);
        $purchase = Purchase::factory()->paid()->create(['package_id' => $package->id]);

        $this->credits->grantFromPurchase($purchase);

        $user = $purchase->user->fresh();

        $this->assertSame(Tier::Premium, $user->tier());
        $this->assertTrue($user->isPremium());
        $this->assertSame(10, $user->availableCredits());
    }

    public function test_consuming_credits_takes_from_the_soonest_expiring_batch_first(): void
    {
        $user = User::factory()->create();

        $soonBatch = CreditBatch::factory()->for($user)->create([
            'total' => 5,
            'remaining' => 5,
            'expires_at' => now()->addDays(10),
        ]);

        $laterBatch = CreditBatch::factory()->for($user)->create([
            'total' => 5,
            'remaining' => 5,
            'expires_at' => now()->addDays(60),
        ]);

        $transaction = $this->credits->consume($user);

        $this->assertSame($soonBatch->id, $transaction->credit_batch_id);
        $this->assertSame(4, $soonBatch->fresh()->remaining);
        $this->assertSame(5, $laterBatch->fresh()->remaining);
    }

    public function test_expired_batches_are_never_consumed_and_dont_count_toward_premium(): void
    {
        $user = User::factory()->create();

        CreditBatch::factory()->for($user)->expired()->create(['total' => 5, 'remaining' => 5]);

        $this->assertSame(Tier::General, $user->tier());
        $this->assertSame(0, $user->availableCredits());

        $this->expectException(InsufficientCreditsException::class);

        $this->credits->consume($user);
    }

    public function test_using_the_last_credit_flips_the_user_back_to_general(): void
    {
        $user = User::factory()->create();

        CreditBatch::factory()->for($user)->create(['total' => 1, 'remaining' => 1]);

        $this->assertTrue($user->isPremium());

        $this->credits->consume($user);

        $this->assertSame(Tier::General, $user->fresh()->tier());
        $this->assertSame(0, $user->availableCredits());
    }

    public function test_a_batch_passing_its_expiry_flips_the_user_to_general_even_before_the_command_runs(): void
    {
        $user = User::factory()->create();

        $batch = CreditBatch::factory()->for($user)->create([
            'total' => 3,
            'remaining' => 3,
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertTrue($user->isPremium());

        $this->travel(2)->minutes();

        $this->assertSame(Tier::General, $user->tier());

        $expiredCount = $this->credits->expireDue();

        $this->assertSame(1, $expiredCount);
        $this->assertSame(0, $batch->fresh()->remaining);

        $this->assertDatabaseHas('credit_transactions', [
            'credit_batch_id' => $batch->id,
            'type' => CreditTransactionType::Expiry->value,
            'amount' => -3,
        ]);

        $this->assertSame(0, $this->credits->expireDue());
        $this->assertSame(1, CreditTransaction::where('credit_batch_id', $batch->id)->count());
    }

    public function test_grant_from_purchase_is_idempotent_and_skips_non_paid_purchases(): void
    {
        $purchase = Purchase::factory()->paid()->create();

        $this->credits->grantFromPurchase($purchase);
        $this->credits->grantFromPurchase($purchase);

        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
        $this->assertSame(1, CreditTransaction::where('credit_batch_id', CreditBatch::where('purchase_id', $purchase->id)->value('id'))->count());

        $pending = Purchase::factory()->create();
        $result = $this->credits->grantFromPurchase($pending);

        $this->assertNull($result);
        $this->assertSame(0, CreditBatch::where('purchase_id', $pending->id)->count());
    }

    public function test_consume_throws_with_no_credits(): void
    {
        $user = User::factory()->create();

        $this->expectException(InsufficientCreditsException::class);

        $this->credits->consume($user);
    }

    public function test_the_ledger_sum_per_user_always_equals_the_sum_of_remaining(): void
    {
        $user = User::factory()->create();

        $this->credits->grantManual($user, 5, now()->addDays(5));
        $this->credits->grantManual($user, 3, now()->addDays(60));

        $this->credits->consume($user);
        $this->credits->consume($user);

        $remainingSum = CreditBatch::where('user_id', $user->id)->sum('remaining');
        $ledgerSum = CreditTransaction::where('user_id', $user->id)->sum('amount');

        $this->assertSame((int) $remainingSum, (int) $ledgerSum);
    }

    public function test_refund_zeroes_remaining_credits_and_writes_the_ledger_row(): void
    {
        $package = Package::factory()->create(['analysis_count' => 10]);
        $purchase = Purchase::factory()->paid()->create(['package_id' => $package->id]);

        $this->credits->grantFromPurchase($purchase);
        $this->credits->consume($purchase->user);

        $batch = CreditBatch::where('purchase_id', $purchase->id)->first();
        $this->assertSame(9, $batch->remaining);

        $this->credits->refund($purchase);

        $this->assertSame(PurchaseStatus::Refunded, $purchase->fresh()->status);
        $this->assertSame(0, $batch->fresh()->remaining);

        $this->assertDatabaseHas('credit_transactions', [
            'credit_batch_id' => $batch->id,
            'type' => CreditTransactionType::Refund->value,
            'amount' => -9,
        ]);
    }
}
