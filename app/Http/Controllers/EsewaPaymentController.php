<?php

namespace App\Http\Controllers;

use App\Exceptions\EsewaVerificationException;
use App\Models\Purchase;
use App\Services\EsewaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EsewaPaymentController extends Controller
{
    public function __construct(private EsewaService $esewa) {}

    /**
     * eSewa redirects here with ?data=<base64 JSON> on success. The
     * redirect is never trusted on its own: the signature is verified, the
     * purchase and amount are matched, and the status API must confirm
     * COMPLETE before anything is settled.
     */
    public function success(Request $request): RedirectResponse
    {
        try {
            $payload = $this->esewa->verifyCallback((string) $request->query('data'));
        } catch (EsewaVerificationException $e) {
            abort(422, $e->getMessage());
        }

        $purchase = Purchase::where('transaction_uuid', $payload['transaction_uuid'])->first();

        abort_if(! $purchase, 422, 'Unknown eSewa transaction.');
        abort_if((int) $payload['total_amount'] !== $purchase->amount, 422, 'eSewa amount does not match the purchase.');

        $status = $this->esewa->checkStatus($purchase);

        if (($status['status'] ?? null) === 'COMPLETE') {
            $this->esewa->settle($purchase, $payload);
        }

        return redirect()->route('purchases.show', $purchase);
    }

    /**
     * eSewa redirects here when the user cancels or the payment fails. We
     * never mark the purchase failed on the redirect alone: the status API
     * has the final say, since the user may have actually completed
     * payment and simply lost the success redirect.
     */
    public function failure(Purchase $purchase): RedirectResponse
    {
        $status = $this->esewa->checkStatus($purchase);

        match ($status['status'] ?? null) {
            'COMPLETE' => $this->esewa->settle($purchase, $status),
            'CANCELED', 'NOT_FOUND' => $this->esewa->markFailed($purchase),
            default => null,
        };

        return redirect()->route('purchases.show', $purchase);
    }
}
