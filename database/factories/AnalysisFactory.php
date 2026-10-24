<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\AnalysisInput;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Analysis>
 */
class AnalysisFactory extends Factory
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
            'status' => AnalysisStatus::Started,
            'credit_transaction_id' => null,
            'request_key' => (string) Str::uuid(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Failed,
        ]);
    }

    /**
     * Attach an immutable inputs row to the analysis — real analyses
     * always have exactly one, created alongside it. Not the unconditional
     * default here because AnalysisInputFactory's own default analysis_id
     * already creates its parent Analysis; chaining this on top of that
     * same call would try to attach a second input to one analysis_id.
     */
    public function withInput(): static
    {
        return $this->has(AnalysisInput::factory(), 'input');
    }
}
