<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\CreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(private CreditService $credits) {}

    /**
     * Every purchase, newest first. Read-only for admin and super_admin.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $purchases = Purchase::query()
            ->with(['user', 'package'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('transaction_uuid', 'like', "%{$search}%")
                        ->orWhere('gateway_ref', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Refund a paid purchase: zero its batch's remaining credits and mark
     * it refunded. Only ever acts on a paid purchase, so replaying this
     * request after a successful refund does nothing the second time.
     */
    public function refund(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'confirm' => ['accepted'],
        ]);

        if ($purchase->status !== PurchaseStatus::Paid) {
            return back()->with('status', 'Only paid purchases can be refunded.');
        }

        $this->credits->refund($purchase, $request->user(), $validated['reason']);

        return back()->with('status', 'Purchase refunded.');
    }
}
