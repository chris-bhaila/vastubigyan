<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * The logged-in user's own purchase history, newest first. Admins have
     * their own view of every purchase at /admin/purchases instead.
     */
    public function index(): View
    {
        $purchases = Purchase::query()
            ->where('user_id', auth()->id())
            ->with('package')
            ->latest()
            ->paginate(15);

        return view('purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    public function show(Purchase $purchase): View
    {
        return view('purchases.show', [
            'purchase' => $purchase->load('package'),
        ]);
    }

    /**
     * A printable invoice. Only paid purchases have one.
     */
    public function invoice(Purchase $purchase): View
    {
        abort_unless($purchase->status === PurchaseStatus::Paid, 404);

        return view('purchases.invoice', [
            'purchase' => $purchase->load(['package', 'user']),
        ]);
    }
}
