<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $packagesBySlug = Package::active()->get()->keyBy('slug');

        $plans = Collection::make(config('pricing.plans'))->map(function (array $plan) use ($packagesBySlug) {
            $package = $packagesBySlug->get($plan['slug']);
            $plan['package'] = $package;

            // The packages table is the single source of truth for price;
            // everything else (features, badge, copy) stays in config since
            // the table has no columns for it.
            if ($package) {
                $plan['price'] = $package->price;
            }

            return $plan;
        });

        return view('pricing', ['plans' => $plans]);
    }
}
