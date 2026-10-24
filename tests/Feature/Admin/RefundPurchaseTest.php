<?php

namespace Tests\Feature\Admin;

use App\Enums\CreditTransactionType;
use App\Enums\PurchaseStatus;
use App\Enums\Role;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_refunding_a_paid_purchase_zeroes_the_batch_and_records_the_actor(): void
    {
        $superAdmin = $this->superAdmin();
        $package = Package::factory()->create(['analysis_count' => 10]);
        $purchase = Purchase::factory()->paid()->create(['package_id' => $package->id]);

        app(CreditService::class)->grantFromPurchase($purchase);
        $batch = CreditBatch::where('purchase_id', $purchase->id)->firstOrFail();
        $this->assertSame(10, $batch->remaining);

        $response = $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'Customer requested',
            'confirm' => '1',
        ]);

        $response->assertRedirect();

        $this->assertSame(PurchaseStatus::Refunded, $purchase->fresh()->status);
        $this->assertSame(0, $batch->fresh()->remaining);

        $transaction = CreditTransaction::where('credit_batch_id', $batch->id)
            ->where('type', CreditTransactionType::Refund)
            ->firstOrFail();

        $this->assertSame($superAdmin->id, $transaction->actor_id);
        $this->assertSame('Customer requested', $transaction->note);
        $this->assertSame(-10, $transaction->amount);
    }

    public function test_refunding_is_refused_for_a_non_paid_purchase(): void
    {
        $superAdmin = $this->superAdmin();
        $purchase = Purchase::factory()->create(['status' => PurchaseStatus::Pending]);

        $response = $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'Customer requested',
            'confirm' => '1',
        ]);

        $response->assertRedirect();
        $this->assertSame(PurchaseStatus::Pending, $purchase->fresh()->status);
        $this->assertSame(0, CreditTransaction::where('type', CreditTransactionType::Refund)->count());
    }

    public function test_refunding_requires_a_reason_and_confirmation(): void
    {
        $superAdmin = $this->superAdmin();
        $purchase = Purchase::factory()->paid()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => '',
            'confirm' => '0',
        ]);

        $response->assertSessionHasErrors(['reason', 'confirm']);
        $this->assertSame(PurchaseStatus::Paid, $purchase->fresh()->status);
    }

    public function test_refunding_twice_does_nothing_the_second_time(): void
    {
        $superAdmin = $this->superAdmin();
        $package = Package::factory()->create(['analysis_count' => 5]);
        $purchase = Purchase::factory()->paid()->create(['package_id' => $package->id]);

        app(CreditService::class)->grantFromPurchase($purchase);
        $batch = CreditBatch::where('purchase_id', $purchase->id)->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'First refund',
            'confirm' => '1',
        ]);

        $this->assertSame(0, $batch->fresh()->remaining);
        $this->assertSame(1, CreditTransaction::where('type', CreditTransactionType::Refund)->count());

        // The purchase is no longer "paid", so a second attempt is refused
        // at the controller before the service is even called.
        $response = $this->actingAs($superAdmin)->post(route('admin.purchases.refund', $purchase), [
            'reason' => 'Second attempt',
            'confirm' => '1',
        ]);

        $response->assertRedirect();
        $this->assertSame(0, $batch->fresh()->remaining);
        $this->assertSame(1, CreditTransaction::where('type', CreditTransactionType::Refund)->count());
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => Role::SuperAdmin])->save();

        return $user;
    }
}
