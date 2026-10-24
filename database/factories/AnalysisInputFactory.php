<?php

namespace Database\Factories;

use App\Models\Analysis;
use App\Models\AnalysisInput;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<AnalysisInput>
 */
class AnalysisInputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Writes real (fake-content) files to the local disk so the integrity
     * hash — which includes each file's SHA-256 — can be computed for real,
     * the same way the controller computes it at submission time.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plotWithRoad = 'analysis-files/factory/'.Str::uuid().'.jpg';
        $floorPlan = 'analysis-files/factory/'.Str::uuid().'.jpg';

        Storage::disk('local')->put($plotWithRoad, 'fake-plot-photo-'.Str::random(32));
        Storage::disk('local')->put($floorPlan, 'fake-floor-plan-'.Str::random(32));

        $values = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(config('analysis.genders')),
            'phone' => '+977'.fake()->numerify('#########'),
            'contact_address' => fake()->address(),
            'birth_year' => fake()->numberBetween(1950, (int) now()->format('Y') - 18),
            'birth_month' => fake()->numberBetween(1, 12),
            'birth_day' => fake()->numberBetween(1, 28),
            'time_of_birth' => sprintf('%02d:%02d', fake()->numberBetween(0, 23), fake()->numberBetween(0, 59)),
            'nachhetra' => fake()->randomElement(config('analysis.nakshatras')),
            'country' => 'Nepal',
            'state' => fake()->state(),
            'district' => fake()->city(),
            'municipality' => fake()->city(),
            'ward' => fake()->numberBetween(1, 32),
            'village' => fake()->streetName(),
            'tole' => fake()->streetName(),
            'plot_area_sqft' => fake()->randomFloat(2, 500, 5000),
            'length' => fake()->randomFloat(2, 10, 200),
            'width' => fake()->randomFloat(2, 10, 200),
            'plinth_area' => fake()->randomFloat(2, 100, 4000),
            'angula_length_inches' => fake()->randomFloat(2, 0.5, 2),
            'visit_date' => fake()->date(),
            'silanyas_month' => fake()->numberBetween(1, 12),
            'constructed_year' => fake()->numberBetween(1980, (int) now()->format('Y')),
            'plot_with_road' => $plotWithRoad,
            'floor_plan' => $floorPlan,
        ];

        return $values + [
            'analysis_id' => Analysis::factory(),
            'integrity_hash' => AnalysisInput::computeIntegrityHash($values, $plotWithRoad, $floorPlan),
        ];
    }
}
