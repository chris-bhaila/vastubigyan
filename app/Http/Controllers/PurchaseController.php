<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Purchase history, newest first. Admins and super admins see every
     * user's purchases; everyone else sees only their own.
     */
    public function index(): View
    {
        $isAdmin = auth()->user()->isAdmin();

        $purchases = Purchase::query()
            ->when(! $isAdmin, fn ($query) => $query->where('user_id', auth()->id()))
            ->with(['package', 'user'])
            ->latest()
            ->paginate(15);

        return view('purchases.index', [
            'purchases' => $purchases,
            'showBuyer' => $isAdmin,
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
