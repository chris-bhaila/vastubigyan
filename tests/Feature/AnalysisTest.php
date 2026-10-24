<?php

namespace Tests\Feature;

use App\Enums\CreditTransactionType;
use App\Enums\Role;
use App\Models\Analysis;
use App\Models\AnalysisInput;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function step1Data(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ram',
            'last_name' => 'Sharma',
            'gender' => 'male',
            'phone' => '+977-9812345678',
            'contact_address' => 'Kathmandu, Nepal',
            'birth_year' => 1990,
            'birth_month' => 5,
            'birth_day' => 15,
            'time_of_birth' => '10:30',
            'nachhetra' => 'Ashwini',
        ], $overrides);
    }

    private function step2Data(array $overrides = []): array
    {
        return array_merge([
            'country' => 'Nepal',
            'state' => 'Bagmati',
            'district' => 'Kathmandu',
            'municipality' => 'KMC',
            'ward' => 5,
            'village' => 'Baneshwor',
            'tole' => 'Mid Baneshwor',
            'plot_area_sqft' => 1200.50,
            'length' => 40.25,
            'width' => 30,
            'plinth_area' => 900.75,
            'angula_length_inches' => 1.5,
            'visit_date' => '2024-01-15',
            'silanyas_month' => 3,
            'constructed_year' => 2020,
            'plot_with_road' => UploadedFile::fake()->image('plot.jpg'),
            'floor_plan' => UploadedFile::fake()->image('floor.jpg'),
            'confirm' => '1',
        ], $overrides);
    }

    /**
     * Drives the full wizard for a user and returns the final response.
     */
    private function runWizard(User $user, array $step1 = [], array $step2 = [])
    {
        $this->actingAs($user)->get(route('analyses.create'));
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data($step1));
        $this->actingAs($user)->get(route('analyses.step2'));

        return $this->actingAs($user)->post(route('analyses.store'), array_merge(
            $this->step2Data($step2),
            ['request_key' => session('analysis.wizard.request_key')],
        ));
    }

    private function userWithCredits(int $count = 5): User
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->create(['total' => $count, 'remaining' => $count]);

        return $user;
    }

    // --- Access -----------------------------------------------------

    public function test_a_guest_is_redirected_to_login_from_every_wizard_route(): void
    {
        $this->get(route('analyses.create'))->assertRedirect(route('login'));
        $this->post(route('analyses.step1'))->assertRedirect(route('login'));
        $this->get(route('analyses.step2'))->assertRedirect(route('login'));
        $this->post(route('analyses.store'))->assertRedirect(route('login'));
        $this->get(route('analyses.index'))->assertRedirect(route('login'));

        $analysis = Analysis::factory()->create();
        $this->get(route('analyses.show', $analysis))->assertRedirect(route('login'));
    }

    public function test_a_user_with_no_credits_is_redirected_to_pricing_at_both_steps(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('analyses.create'))->assertRedirect(route('pricing'));
        $this->actingAs($user)->get(route('analyses.step2'))->assertRedirect(route('pricing'));
    }

    public function test_a_user_with_only_an_expired_batch_is_redirected_to_pricing(): void
    {
        $user = User::factory()->create();
        CreditBatch::factory()->for($user)->expired()->create(['total' => 5, 'remaining' => 5]);

        $this->actingAs($user)->get(route('analyses.create'))->assertRedirect(route('pricing'));
    }

    // --- Step 1 -------------------------------------------------------

    public function test_step_1_requires_every_field(): void
    {
        $user = $this->userWithCredits();

        $response = $this->actingAs($user)->post(route('analyses.step1'), []);

        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'gender', 'phone', 'contact_address',
            'birth_year', 'birth_month', 'birth_day', 'time_of_birth', 'nachhetra',
        ]);
    }

    public function test_step_1_rejects_a_birth_year_outside_the_configured_range(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data(['birth_year' => 1899]))
            ->assertSessionHasErrors('birth_year');

        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data(['birth_year' => (int) now()->format('Y') + 1]))
            ->assertSessionHasErrors('birth_year');
    }

    public function test_step_1_rejects_an_impossible_month_day_combination(): void
    {
        $user = $this->userWithCredits();

        $response = $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data([
            'birth_month' => 2,
            'birth_day' => 30,
        ]));

        $response->assertSessionHasErrors('birth_day');
    }

    public function test_step_1_rejects_a_birth_date_in_the_future(): void
    {
        $user = $this->userWithCredits();
        $future = now()->addYear();

        $response = $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data([
            'birth_year' => $future->year,
            'birth_month' => $future->month,
            'birth_day' => $future->day,
        ]));

        $response->assertSessionHasErrors('birth_year');
    }

    public function test_step_1_rejects_a_malformed_phone_number(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data(['phone' => 'not-a-phone']))
            ->assertSessionHasErrors('phone');
    }

    public function test_step_1_rejects_a_gender_not_in_the_list(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data(['gender' => 'robot']))
            ->assertSessionHasErrors('gender');
    }

    public function test_step_1_rejects_a_nachhetra_not_in_the_list(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data(['nachhetra' => 'Not A Real One']))
            ->assertSessionHasErrors('nachhetra');
    }

    public function test_a_valid_step_1_stores_the_session_and_redirects_with_no_db_writes_or_spend(): void
    {
        $user = $this->userWithCredits();

        $response = $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());

        $response->assertRedirect(route('analyses.step2'));
        $this->assertSame('Ram', session('analysis.wizard.step1.first_name'));

        $this->assertSame(0, Analysis::count());
        $this->assertSame(0, CreditTransaction::count());
    }

    // --- Step 2 -------------------------------------------------------

    public function test_step_2_without_a_step_1_session_redirects_to_step_1(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->get(route('analyses.step2'))->assertRedirect(route('analyses.create'));
    }

    public function test_step_2_requires_every_field_including_files_and_confirmation(): void
    {
        $user = $this->userWithCredits();
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());

        $response = $this->actingAs($user)->post(route('analyses.store'), []);

        $response->assertSessionHasErrors([
            'country', 'state', 'district', 'municipality', 'ward', 'village', 'tole',
            'plot_area_sqft', 'length', 'width', 'plinth_area', 'angula_length_inches',
            'visit_date', 'silanyas_month', 'constructed_year',
            'plot_with_road', 'floor_plan', 'confirm',
        ]);

        $this->assertSame(0, Analysis::count());
    }

    public function test_step_2_enforces_file_type_and_size_rules(): void
    {
        $user = $this->userWithCredits();
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());

        $response = $this->actingAs($user)->post(route('analyses.store'), $this->step2Data([
            'plot_with_road' => UploadedFile::fake()->create('plot.pdf', 100, 'application/pdf'),
            'floor_plan' => UploadedFile::fake()->image('floor.jpg')->size(6000),
        ]));

        $response->assertSessionHasErrors(['plot_with_road', 'floor_plan']);
    }

    public function test_step_2_enforces_numeric_bounds(): void
    {
        $user = $this->userWithCredits();
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());

        $response = $this->actingAs($user)->post(route('analyses.store'), $this->step2Data([
            'plot_area_sqft' => 0,
            'angula_length_inches' => 20,
        ]));

        $response->assertSessionHasErrors(['plot_area_sqft', 'angula_length_inches']);
    }

    // --- Full submission ----------------------------------------------

    public function test_a_full_valid_submission_creates_one_analysis_and_inputs_row_spends_one_credit_and_clears_the_session(): void
    {
        $user = $this->userWithCredits();

        $response = $this->runWizard($user);

        $this->assertSame(1, Analysis::count());
        $this->assertSame(1, AnalysisInput::count());

        $analysis = Analysis::first();
        $response->assertRedirect(route('analyses.show', $analysis));

        $input = $analysis->input;
        $this->assertSame('Ram', $input->first_name);
        $this->assertSame('Nepal', $input->country);
        Storage::disk('local')->assertExists($input->plot_with_road);
        Storage::disk('local')->assertExists($input->floor_plan);

        $this->assertSame(4, $user->fresh()->availableCredits());

        $usage = CreditTransaction::where('analysis_id', $analysis->id)->first();
        $this->assertSame(CreditTransactionType::Usage, $usage->type);
        $this->assertSame($usage->id, $analysis->credit_transaction_id);

        $this->assertNull(session('analysis.wizard'));

        // Round-trips through real date/decimal/time DB columns and back
        // out through casts without the hash drifting.
        $this->assertTrue($input->fresh()->verifyIntegrity());
    }

    public function test_insufficient_credits_at_final_submit_leaves_nothing_behind(): void
    {
        $user = $this->userWithCredits(1);

        $this->actingAs($user)->get(route('analyses.create'));
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());
        $this->actingAs($user)->get(route('analyses.step2'));

        // Credits run out between loading step 2 and submitting it.
        CreditBatch::query()->update(['remaining' => 0]);

        $response = $this->actingAs($user)->post(route('analyses.store'), array_merge(
            $this->step2Data(),
            ['request_key' => session('analysis.wizard.request_key')],
        ));

        $response->assertRedirect(route('pricing'));
        $this->assertSame(0, Analysis::count());
        $this->assertSame(0, AnalysisInput::count());
        $this->assertEmpty(Storage::disk('local')->allFiles('analysis-files'));
    }

    public function test_a_mid_transaction_failure_leaves_nothing_behind_and_deletes_the_files(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->get(route('analyses.create'));
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());
        $this->actingAs($user)->get(route('analyses.step2'));

        $this->mock(CreditService::class, function ($mock) {
            $mock->shouldReceive('consume')->andThrow(new \RuntimeException('simulated failure'));
        });

        try {
            $this->actingAs($user)->post(route('analyses.store'), array_merge(
                $this->step2Data(),
                ['request_key' => session('analysis.wizard.request_key')],
            ));
        } catch (\RuntimeException) {
            // expected to bubble up in this test environment
        }

        $this->assertSame(0, Analysis::count());
        $this->assertSame(0, AnalysisInput::count());
        $this->assertEmpty(Storage::disk('local')->allFiles('analysis-files'));
    }

    public function test_submitting_the_same_request_key_twice_creates_one_analysis_and_spends_one_credit(): void
    {
        $user = $this->userWithCredits();

        $this->actingAs($user)->get(route('analyses.create'));
        $this->actingAs($user)->post(route('analyses.step1'), $this->step1Data());
        $this->actingAs($user)->get(route('analyses.step2'));
        $requestKey = session('analysis.wizard.request_key');

        $body = array_merge($this->step2Data(), ['request_key' => $requestKey]);

        $this->actingAs($user)->post(route('analyses.store'), $body);

        $filesAfterFirst = Storage::disk('local')->allFiles('analysis-files');

        // Simulate a second, identical submission (double-click / resubmit)
        // with fresh file uploads but the same request_key.
        $body = array_merge($this->step2Data(), ['request_key' => $requestKey]);
        $response = $this->actingAs($user)->post(route('analyses.store'), $body);

        $this->assertSame(1, Analysis::count());
        $this->assertSame(4, $user->fresh()->availableCredits());
        $this->assertSame($filesAfterFirst, Storage::disk('local')->allFiles('analysis-files'));

        $analysis = Analysis::first();
        $response->assertRedirect(route('analyses.show', $analysis));
    }

    // --- Index / show / policy -----------------------------------------

    public function test_a_user_cannot_view_another_users_analysis(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($stranger)->get(route('analyses.show', $analysis))->assertForbidden();
        $this->actingAs($owner)->get(route('analyses.show', $analysis))->assertOk();
    }

    public function test_the_show_page_displays_submitted_values_read_only_with_no_edit_controls(): void
    {
        $owner = $this->userWithCredits();
        $this->runWizard($owner);
        $analysis = Analysis::first();

        $response = $this->actingAs($owner)->get(route('analyses.show', $analysis));

        $response->assertOk();
        $response->assertSee('Ram');
        $response->assertSee('Sharma');
        $response->assertSee('Mid Baneshwor');
        $response->assertSee('Submitted details cannot be changed.');
        $response->assertDontSee('name="first_name"', escape: false);
    }

    public function test_an_admin_can_view_any_users_analysis(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();

        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($admin)->get(route('analyses.show', $analysis))->assertOk();
    }

    public function test_a_user_sees_only_their_own_analyses_newest_first(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        CreditBatch::factory()->for($user)->create(['total' => 10, 'remaining' => 10]);

        $older = Analysis::factory()->for($user)->create(['created_at' => now()->subDay()]);
        $newer = Analysis::factory()->for($user)->create();
        Analysis::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('analyses.index'));

        $analyses = $response->viewData('analyses');

        $this->assertCount(2, $analyses);
        $this->assertSame($newer->id, $analyses->first()->id);
    }
}
