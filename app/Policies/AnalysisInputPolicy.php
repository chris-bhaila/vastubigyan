<?php

namespace App\Policies;

use App\Models\AnalysisInput;
use App\Models\User;

class AnalysisInputPolicy
{
    /**
     * An analysis's inputs are immutable from the moment they're created,
     * for everyone — including super_admin. See AppServiceProvider's
     * Gate::before, which exempts these abilities from its usual
     * super_admin bypass so this denial actually takes effect.
     */
    public function update(User $user, AnalysisInput $input): bool
    {
        return false;
    }

    public function delete(User $user, AnalysisInput $input): bool
    {
        return false;
    }

    public function restore(User $user, AnalysisInput $input): bool
    {
        return false;
    }

    public function forceDelete(User $user, AnalysisInput $input): bool
    {
        return false;
    }
}
