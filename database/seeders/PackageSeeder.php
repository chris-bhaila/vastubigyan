<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Prices are placeholders pending client confirmation.
     */
    public function run(): void
    {
        Package::query()->updateOrCreate(
            ['name' => '1 Analysis'],
            ['analysis_count' => 1, 'price' => 1000, 'validity_days' => 365, 'is_active' => true],
        );

        Package::query()->updateOrCreate(
            ['name' => '10 Analyses'],
            ['analysis_count' => 10, 'price' => 10000, 'validity_days' => 365, 'is_active' => true],
        );
    }
}
