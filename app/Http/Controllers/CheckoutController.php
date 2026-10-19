<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Models\Package;
use App\Models\Purchase;
use App\Services\EsewaService;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private EsewaService $esewa) {}

    /**
     * Create a pending purchase and hand back an auto-submitting form that
     * posts the signed checkout fields to eSewa.
     */
    public function store(Package $package): View
    {
        abort_unless($package->is_active, 404);

        $purchase = Purchase::create([
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'amount' => $package->price,
            'gateway' => 'esewa',
            'transaction_uuid' => (string) Str::uuid(),
            'status' => PurchaseStatus::Pending,
        ]);

        return view('checkout.redirect', [
            'formUrl' => config('esewa.form_url'),
            'fields' => $this->esewa->checkoutPayload($purchase),
        ]);
    }
}
