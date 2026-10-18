<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $roleCounts = collect(Role::cases())
            ->mapWithKeys(fn (Role $role) => [
                $role->label() => User::query()->where('role', $role)->count(),
            ]);

        return view('super-admin.dashboard', [
            'userCount' => User::query()->count(),
            'roleCounts' => $roleCounts,
        ]);
    }
}
