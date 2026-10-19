<?php

namespace Database\Factories;

use App\Enums\CreditSource;
use App\Models\CreditBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditBatch>
 */
class CreditBatchFactory extends Factory
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
            'purchase_id' => null,
            'source' => CreditSource::AdminGrant,
            'total' => 10,
            'remaining' => 10,
            'expires_at' => now()->addYear(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function depleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'remaining' => 0,
        ]);
    }
}
