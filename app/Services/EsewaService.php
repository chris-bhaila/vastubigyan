<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Exceptions\EsewaVerificationException;
use App\Models\Purchase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EsewaService
{
    public function __construct(private CreditService $credits) {}

    /**
     * Compute the ePay v2 signature for a set of fields, over the keys
     * named in $fields['signed_field_names'], in that order.
     */
    public function sign(array $fields): string
    {
        $message = $this->signedMessage($fields);

        return base64_encode(hash_hmac('sha256', $message, (string) config('esewa.secret'), true));
    }

    /**
     * The full set of form fields for the eSewa checkout form. The amount
     * always comes from the purchase row, never from the request.
     */
    public function checkoutPayload(Purchase $purchase): array
    {
        $fields = [
            'amount' => (string) $purchase->amount,
            'tax_amount' => '0',
            'total_amount' => (string) $purchase->amount,
            'transaction_uuid' => $purchase->transaction_uuid,
            'product_code' => config('esewa.product_code'),
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',
            'success_url' => route('payment.esewa.success'),
            'failure_url' => route('payment.esewa.failure', $purchase),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        $fields['signature'] = $this->sign($fields);

        return $fields;
    }

    /**
     * Decode and verify the base64 "data" query param eSewa redirects back
     * with on success. Throws when the structure or signature is invalid.
     */
    public function verifyCallback(string $base64Data): array
    {
        $decoded = base64_decode($base64Data, true);

        if ($decoded === false) {
            throw new EsewaVerificationException('eSewa callback payload is not valid base64.');
        }

        $payload = json_decode($decoded, true);

        if (! is_array($payload)) {
            throw new EsewaVerificationException('eSewa callback payload is not valid JSON.');
        }

        foreach (['transaction_code', 'status', 'total_amount', 'transaction_uuid', 'product_code', 'signed_field_names', 'signature'] as $key) {
            if (! array_key_exists($key, $payload)) {
                throw new EsewaVerificationException("eSewa callback payload is missing \"{$key}\".");
            }
        }

        $expected = $this->sign($payload);

        if (! hash_equals($expected, (string) $payload['signature'])) {
            throw new EsewaVerificationException('eSewa callback signature does not match.');
        }

        return $payload;
    }

    /**
     * Server-to-server status check. Network errors and non-200 responses
     * are reported as "UNKNOWN", never as a failure.
     */
    public function checkStatus(Purchase $purchase): array
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 500)
                ->get(config('esewa.status_url'), [
                    'product_code' => config('esewa.product_code'),
                    'total_amount' => $purchase->amount,
                    'transaction_uuid' => $purchase->transaction_uuid,
                ]);
        } catch (ConnectionException) {
            return ['status' => 'UNKNOWN'];
        }

        if (! $response->successful()) {
            return ['status' => 'UNKNOWN'];
        }

        return $response->json() ?? ['status' => 'UNKNOWN'];
    }

    /**
     * The single path by which a purchase becomes paid, shared by the
     * success callback and the reconcile command. Row-locked so a replayed
     * callback racing the reconcile command never grants credits twice.
     */
    public function settle(Purchase $purchase, array $payload): Purchase
    {
        return DB::transaction(function () use ($purchase, $payload) {
            $locked = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->first();

            if ($locked->status === PurchaseStatus::Paid) {
                return $locked;
            }

            $locked->update([
                'status' => PurchaseStatus::Paid,
                'paid_at' => now(),
                'gateway_ref' => $payload['transaction_code'] ?? $payload['ref_id'] ?? $locked->gateway_ref,
                'gateway_payload' => $payload,
            ]);

            $this->credits->grantFromPurchase($locked);

            return $locked;
        });
    }

    /**
     * Mark a purchase failed, unless it has already been settled paid.
     */
    public function markFailed(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $locked = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->first();

            if ($locked->status === PurchaseStatus::Paid) {
                return $locked;
            }

            $locked->update(['status' => PurchaseStatus::Failed]);

            return $locked;
        });
    }

    private function signedMessage(array $fields): string
    {
        $signedFieldNames = explode(',', (string) $fields['signed_field_names']);

        return implode(',', array_map(
            fn (string $field) => $field.'='.($fields[$field] ?? ''),
            $signedFieldNames,
        ));
    }
}
