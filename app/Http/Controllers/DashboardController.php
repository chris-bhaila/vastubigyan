<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (! auth()->user()->isAdmin()) {
            return view('dashboard');
        }

        $data = [
            'userCount' => User::query()->count(),
        ];

        if (auth()->user()->isSuperAdmin()) {
            $data['roleCounts'] = collect(Role::cases())
                ->mapWithKeys(fn (Role $role) => [
                    $role->label() => User::query()->where('role', $role)->count(),
                ]);
        }

        return view('dashboard', $data);
    }
}
