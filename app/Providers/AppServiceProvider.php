<?php

namespace App\Providers;

use App\Models\Analysis;
use App\Models\AnalysisInput;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Submitted analyses and their inputs are permanently immutable, even
        // for super_admin: update/delete/restore/forceDelete on these two
        // models must fall through to the policy's explicit denial instead
        // of being blanket-granted here.
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            if (! $user->isSuperAdmin()) {
                return null;
            }

            $target = $arguments[0] ?? null;

            if (
                in_array($ability, ['update', 'delete', 'restore', 'forceDelete'], true)
                && ($target instanceof Analysis || $target instanceof AnalysisInput)
            ) {
                return null;
            }

            return true;
        });

        Gate::define('manage-admins', fn (User $user): bool => $user->isSuperAdmin());

        Gate::define('manage-credits', fn (User $user): bool => $user->isSuperAdmin());

        Gate::define('access-admin-panel', fn (User $user): bool => $user->isAdmin());

        Gate::define('viewLogViewer', function (User $user): bool {
            $allowedEmails = array_filter(explode(',', (string) config('log-viewer.allowed_emails')));

            return in_array($user->email, $allowedEmails, strict: true);
        });

        // Computed once per request: the sidebar partial is only rendered
        // once per page load, so this doesn't add a query per component.
        View::composer('partials.sidebar', function ($view): void {
            $user = auth()->user();

            $view->with('sidebarCredits', $user ? $user->availableCredits() : null);
        });
    }
}
