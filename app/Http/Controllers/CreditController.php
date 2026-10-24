<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditController extends Controller
{
    /**
     * The logged-in user's credit balance, active batches, and ledger
     * history, newest first.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $batches = $user->creditBatches()
            ->withCredits()
            ->unexpired()
            ->orderBy('expires_at')
            ->get();

        $transactions = $user->creditTransactions()
            ->latest()
            ->paginate(15);

        $availableCredits = $user->availableCredits();
        $nextExpiry = $user->nextExpiry();

        return view('credits.index', [
            'tier' => $user->tier(),
            'availableCredits' => $availableCredits,
            'nextExpiry' => $nextExpiry,
            'batches' => $batches,
            'transactions' => $transactions,
            'lowCredits' => $availableCredits <= config('credits.low_balance_threshold'),
            'expiringSoon' => $nextExpiry !== null
                && $nextExpiry->lessThanOrEqualTo(now()->addDays(config('credits.expiry_warning_days'))),
        ]);
    }
}
