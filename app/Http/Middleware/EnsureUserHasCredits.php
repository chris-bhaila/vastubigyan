<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCredits
{
    /**
     * Handle an incoming request.
     *
     * A convenience pre-check only: it redirects users with no credits to
     * the pricing page before they reach the controller. The real
     * enforcement is CreditService::consume()'s transactional check, which
     * this must never be relied on to replace.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->availableCredits() <= 0) {
            return redirect()->route('pricing')
                ->with('status', 'You have no analyses left. Buy more to continue.');
        }

        return $next($request);
    }
}
