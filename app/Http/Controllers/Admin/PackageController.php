<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Every package, including inactive ones. super_admin only.
     */
    public function index(): View
    {
        return view('admin.packages.index', [
            'packages' => Package::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.edit', [
            'package' => $package,
        ]);
    }

    /**
     * Update a package's catalog details. This never touches purchases or
     * credit batches already created from it; a price or count change only
     * takes effect for purchases made afterwards.
     */
    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'analysis_count' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('status', "Updated {$package->name}.");
    }
}
