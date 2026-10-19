<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        return view('pricing', [
            'plans' => config('pricing.plans'),
        ]);
    }
}
