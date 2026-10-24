<?php

namespace App\Policies;

use App\Models\Analysis;
use App\Models\User;

class AnalysisPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Analysis $analysis): bool
    {
        return $user->id === $analysis->user_id || $user->isAdmin();
    }

    /**
     * A submitted analysis can never be edited or removed, by anyone —
     * including super_admin. See AppServiceProvider's Gate::before, which
     * exempts these abilities from its usual super_admin bypass so this
     * denial actually takes effect.
     */
    public function update(User $user, Analysis $analysis): bool
    {
        return false;
    }

    public function delete(User $user, Analysis $analysis): bool
    {
        return false;
    }

    public function restore(User $user, Analysis $analysis): bool
    {
        return false;
    }

    public function forceDelete(User $user, Analysis $analysis): bool
    {
        return false;
    }
}
