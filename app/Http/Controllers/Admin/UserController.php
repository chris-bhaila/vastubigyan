<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private CreditService $credits) {}

    /**
     * Every user, searchable by name/email, filterable by role and tier.
     * Credits and tier are computed via aggregated subqueries (withSum /
     * withHas), so this never runs a per-row query no matter how many
     * users are listed.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $role = $request->query('role');
        $tier = $request->query('tier');

        $unexpiredCredits = fn ($query) => $query->where('remaining', '>', 0)->where('expires_at', '>', now());

        $users = User::query()
            ->withSum(['creditBatches as available_credits' => $unexpiredCredits], 'remaining')
            ->withMin(['creditBatches as next_expiry' => $unexpiredCredits], 'expires_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', Role::fromSlug($role)))
            ->when($tier === 'premium', fn ($query) => $query->whereHas('creditBatches', $unexpiredCredits))
            ->when($tier === 'general', fn ($query) => $query->whereDoesntHave('creditBatches', $unexpiredCredits))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'tier' => $tier,
        ]);
    }

    /**
     * A user's profile, balance, full ledger, purchases and analyses.
     */
    public function show(User $user): View
    {
        $batches = $user->creditBatches()
            ->withCredits()
            ->unexpired()
            ->orderBy('expires_at')
            ->get();

        $transactions = $user->creditTransactions()
            ->with('actor')
            ->latest()
            ->paginate(15, pageName: 'transactions');

        $purchases = $user->purchases()
            ->with('package')
            ->latest()
            ->paginate(10, pageName: 'purchases');

        $analyses = $user->analyses()
            ->latest()
            ->paginate(10, pageName: 'analyses');

        return view('admin.users.show', [
            'user' => $user,
            'availableCredits' => $user->availableCredits(),
            'tier' => $user->tier(),
            'nextExpiry' => $user->nextExpiry(),
            'batches' => $batches,
            'transactions' => $transactions,
            'purchases' => $purchases,
            'analyses' => $analyses,
        ]);
    }

    /**
     * Manually grant a credit batch. The `can:manage-credits` route
     * middleware is the real enforcement; the view only hides the form
     * for non-super-admins.
     */
    public function storeCredits(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'count' => [
                'required',
                'integer',
                'min:'.config('credits.grant_count_min', 1),
                'max:'.config('credits.grant_count_max', 100),
            ],
            'expiry_days' => [
                'required',
                'integer',
                'min:'.config('credits.grant_expiry_days_min', 1),
                'max:'.config('credits.grant_expiry_days_max', 730),
            ],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->credits->grantManual(
            $user,
            $validated['count'],
            now()->addDays($validated['expiry_days']),
            $validated['reason'],
            $request->user(),
        );

        return back()->with('status', "Granted {$validated['count']} credit(s) to {$user->name}.");
    }
}
