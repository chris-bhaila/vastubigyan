<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\CreditBatch;
use App\Models\Purchase;
use App\Models\User;
use App\Services\EsewaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EsewaPaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    private EsewaService $esewa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->esewa = app(EsewaService::class);
    }

    public function test_a_valid_success_callback_with_complete_status_marks_the_purchase_paid_and_grants_one_batch(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('COMPLETE');

        $response = $this->get(route('payment.esewa.success', ['data' => $this->validData($purchase)]));

        $response->assertRedirect(route('purchases.show', $purchase));
        $this->assertSame(PurchaseStatus::Paid, $purchase->fresh()->status);
        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_a_tampered_signature_is_rejected_and_grants_nothing(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('COMPLETE');

        $fields = $this->callbackFields($purchase);
        $fields['signature'] = base64_encode('bogus');
        $data = base64_encode(json_encode($fields));

        $response = $this->get(route('payment.esewa.success', ['data' => $data]));

        $response->assertStatus(422);
        $this->assertSame(PurchaseStatus::Pending, $purchase->fresh()->status);
        $this->assertSame(0, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_an_unknown_transaction_uuid_is_rejected_and_grants_nothing(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('COMPLETE');

        $fields = $this->callbackFields($purchase);
        $fields['transaction_uuid'] = 'does-not-exist';
        $fields['signature'] = $this->esewa->sign($fields);
        $data = base64_encode(json_encode($fields));

        $response = $this->get(route('payment.esewa.success', ['data' => $data]));

        $response->assertStatus(422);
        $this->assertSame(0, CreditBatch::count());
    }

    public function test_an_amount_mismatch_is_rejected_and_grants_nothing(): void
    {
        $purchase = $this->pendingPurchase(1000);
        $this->fakeStatus('COMPLETE');

        $fields = $this->callbackFields($purchase);
        $fields['total_amount'] = '1'; // Does not match the purchase's amount of 1000.
        $fields['signature'] = $this->esewa->sign($fields);
        $data = base64_encode(json_encode($fields));

        $response = $this->get(route('payment.esewa.success', ['data' => $data]));

        $response->assertStatus(422);
        $this->assertSame(PurchaseStatus::Pending, $purchase->fresh()->status);
        $this->assertSame(0, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_a_success_callback_with_a_non_complete_status_grants_nothing(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('PENDING');

        $response = $this->get(route('payment.esewa.success', ['data' => $this->validData($purchase)]));

        $response->assertRedirect(route('purchases.show', $purchase));
        $this->assertSame(PurchaseStatus::Pending, $purchase->fresh()->status);
        $this->assertSame(0, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_replaying_the_same_success_callback_grants_nothing_a_second_time(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('COMPLETE');

        $data = $this->validData($purchase);

        $this->get(route('payment.esewa.success', ['data' => $data]))->assertRedirect();
        $this->get(route('payment.esewa.success', ['data' => $data]))->assertRedirect();

        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_the_failure_callback_marks_canceled_or_not_found_purchases_failed(): void
    {
        $canceled = $this->pendingPurchase();
        $this->fakeStatus('CANCELED');
        $this->get(route('payment.esewa.failure', $canceled))->assertRedirect(route('purchases.show', $canceled));
        $this->assertSame(PurchaseStatus::Failed, $canceled->fresh()->status);

        $notFound = $this->pendingPurchase();
        $this->fakeStatus('NOT_FOUND');
        $this->get(route('payment.esewa.failure', $notFound))->assertRedirect(route('purchases.show', $notFound));
        $this->assertSame(PurchaseStatus::Failed, $notFound->fresh()->status);
    }

    public function test_the_failure_callback_settles_a_purchase_that_actually_completed(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('COMPLETE');

        $this->get(route('payment.esewa.failure', $purchase))->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame(PurchaseStatus::Paid, $purchase->fresh()->status);
        $this->assertSame(1, CreditBatch::where('purchase_id', $purchase->id)->count());
    }

    public function test_the_failure_callback_leaves_pending_purchases_alone(): void
    {
        $purchase = $this->pendingPurchase();
        $this->fakeStatus('PENDING');

        $this->get(route('payment.esewa.failure', $purchase))->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame(PurchaseStatus::Pending, $purchase->fresh()->status);
    }

    private function pendingPurchase(int $amount = 1000): Purchase
    {
        return Purchase::factory()->for(User::factory())->create(['amount' => $amount]);
    }

    private function fakeStatus(string $status): void
    {
        Http::fake([
            config('esewa.status_url').'*' => Http::response(['status' => $status]),
        ]);
    }

    private function callbackFields(Purchase $purchase, string $status = 'COMPLETE'): array
    {
        return [
            'transaction_code' => 'TXN-123',
            'status' => $status,
            'total_amount' => (string) $purchase->amount,
            'transaction_uuid' => $purchase->transaction_uuid,
            'product_code' => config('esewa.product_code'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];
    }

    private function validData(Purchase $purchase, string $status = 'COMPLETE'): string
    {
        $fields = $this->callbackFields($purchase, $status);
        $fields['signature'] = $this->esewa->sign($fields);

        return base64_encode(json_encode($fields));
    }
}
