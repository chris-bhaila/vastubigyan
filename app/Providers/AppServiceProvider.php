<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);

        Gate::define('manage-admins', fn (User $user): bool => $user->isSuperAdmin());

        Gate::define('access-admin-panel', fn (User $user): bool => $user->isAdmin());

        Gate::define('viewLogViewer', function (User $user): bool {
            $allowedEmails = array_filter(explode(',', (string) config('log-viewer.allowed_emails')));

            return in_array($user->email, $allowedEmails, strict: true);
        });
    }
}
