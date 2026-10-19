<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'analysis_count' => fake()->numberBetween(1, 10),
            'price' => fake()->numberBetween(1000, 10000),
            'validity_days' => 365,
            'is_active' => true,
        ];
    }
}
