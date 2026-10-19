<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Matches the paid plans shown on the pricing page (config/pricing.php),
     * keyed by slug so "Buy now" can look up the right package.
     */
    public function run(): void
    {
        Package::query()->updateOrCreate(
            ['slug' => 'premium-app'],
            ['name' => 'Premium App', 'analysis_count' => 1, 'price' => 1100, 'validity_days' => 365, 'is_active' => true],
        );

        Package::query()->updateOrCreate(
            ['slug' => 'web-app-premium'],
            ['name' => 'Web + App Premium', 'analysis_count' => 25, 'price' => 29400, 'validity_days' => 365, 'is_active' => true],
        );
    }
}
