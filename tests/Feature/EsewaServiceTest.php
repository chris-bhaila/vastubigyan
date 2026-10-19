<?php

namespace Tests\Feature;

use App\Exceptions\EsewaVerificationException;
use App\Models\Package;
use App\Models\Purchase;
use App\Services\EsewaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EsewaServiceTest extends TestCase
{
    use RefreshDatabase;

    private EsewaService $esewa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->esewa = app(EsewaService::class);
    }

    public function test_sign_matches_an_independently_computed_hmac(): void
    {
        $fields = [
            'total_amount' => '100',
            'transaction_uuid' => '11-201-13',
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        $message = 'total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST';
        $expected = base64_encode(hash_hmac('sha256', $message, config('esewa.secret'), true));

        $this->assertSame($expected, $this->esewa->sign($fields));
    }

    public function test_checkout_payload_uses_the_db_price_and_ignores_any_request_amount(): void
    {
        $package = Package::factory()->create(['price' => 4999]);
        $purchase = Purchase::factory()->create(['package_id' => $package->id, 'amount' => 4999]);

        // checkoutPayload() only ever takes a Purchase, never a Request, so
        // there is no "amount" input it could read even if one were sent.
        $fields = $this->esewa->checkoutPayload($purchase);

        $this->assertSame('4999', $fields['amount']);
        $this->assertSame('4999', $fields['total_amount']);
        $this->assertSame($purchase->transaction_uuid, $fields['transaction_uuid']);
        $this->assertSame('total_amount,transaction_uuid,product_code', $fields['signed_field_names']);
        $this->assertArrayHasKey('signature', $fields);
    }

    public function test_verify_callback_accepts_a_correctly_signed_payload(): void
    {
        $purchase = Purchase::factory()->create(['amount' => 1000]);

        $data = $this->validCallbackData($purchase);

        $payload = $this->esewa->verifyCallback($data);

        $this->assertSame($purchase->transaction_uuid, $payload['transaction_uuid']);
    }

    public function test_verify_callback_rejects_a_tampered_signature(): void
    {
        $purchase = Purchase::factory()->create(['amount' => 1000]);

        $fields = $this->callbackFields($purchase);
        $fields['signature'] = base64_encode('not-the-real-signature');
        $data = base64_encode(json_encode($fields));

        $this->expectException(EsewaVerificationException::class);

        $this->esewa->verifyCallback($data);
    }

    public function test_verify_callback_rejects_malformed_base64(): void
    {
        $this->expectException(EsewaVerificationException::class);

        $this->esewa->verifyCallback('not-base64-json');
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

    private function validCallbackData(Purchase $purchase, string $status = 'COMPLETE'): string
    {
        $fields = $this->callbackFields($purchase, $status);
        $fields['signature'] = $this->esewa->sign($fields);

        return base64_encode(json_encode($fields));
    }
}
