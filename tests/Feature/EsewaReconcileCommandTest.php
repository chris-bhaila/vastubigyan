<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\CreditBatch;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EsewaReconcileCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_settles_an_old_pending_complete_purchase(): void
    {
        $purchase = Purchase::factory()->for(User::factory())->create([
            'amount' => 1000,
            'created_at' => now()->subMinutes(30),
        ]);

        Http::fake([
            config('esewa.status_url').'*' => Http::response(['status' => 'COMPLETE', 'ref_id' => 'REF-1']),
        ]);

        $this->artisan('esewa:reconcile')->assertSuccessful();

        $this->assertSame(PurchaseStatus::Paid, $purchase->fresh()->status);
        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_it_ignores_recent_and_very_old_pending_purchases(): void
    {
        $recent = Purchase::factory()->for(User::factory())->create([
            'created_at' => now()->subMinutes(2),
        ]);

        $veryOld = Purchase::factory()->for(User::factory())->create([
            'created_at' => now()->subHours(48),
        ]);

        Http::fake([
            config('esewa.status_url').'*' => Http::response(['status' => 'COMPLETE']),
        ]);

        $this->artisan('esewa:reconcile')->assertSuccessful();

        $this->assertSame(PurchaseStatus::Pending, $recent->fresh()->status);
        $this->assertSame(PurchaseStatus::Pending, $veryOld->fresh()->status);
    }

    public function test_it_marks_canceled_purchases_failed(): void
    {
        $purchase = Purchase::factory()->for(User::factory())->create([
            'created_at' => now()->subMinutes(30),
        ]);

        Http::fake([
            config('esewa.status_url').'*' => Http::response(['status' => 'CANCELED']),
        ]);

        $this->artisan('esewa:reconcile')->assertSuccessful();

        $this->assertSame(PurchaseStatus::Failed, $purchase->fresh()->status);
    }

    public function test_it_is_idempotent(): void
    {
        $purchase = Purchase::factory()->for(User::factory())->create([
            'amount' => 1000,
            'created_at' => now()->subMinutes(30),
        ]);

        Http::fake([
            config('esewa.status_url').'*' => Http::response(['status' => 'COMPLETE']),
        ]);

        $this->artisan('esewa:reconcile')->assertSuccessful();
        $this->artisan('esewa:reconcile')->assertSuccessful();

        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
    }
}
