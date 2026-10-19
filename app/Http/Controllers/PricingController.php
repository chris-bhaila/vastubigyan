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
            $plan['package'] = $packagesBySlug->get($plan['slug']);

            return $plan;
        });

        return view('pricing', ['plans' => $plans]);
    }
}
