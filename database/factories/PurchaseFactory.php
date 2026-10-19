<?php

namespace Database\Factories;

use App\Enums\PurchaseStatus;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'package_id' => Package::factory(),
            'amount' => fake()->numberBetween(1000, 10000),
            'gateway' => null,
            'gateway_ref' => null,
            'status' => PurchaseStatus::Pending,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
