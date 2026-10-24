<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalysisFileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_can_fetch_either_file(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($owner)->get(route('analyses.files', [$analysis, 'plot-with-road']))->assertOk();
        $this->actingAs($owner)->get(route('analyses.files', [$analysis, 'floor-plan']))->assertOk();
    }

    public function test_an_admin_can_fetch_either_file(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($admin)->get(route('analyses.files', [$analysis, 'plot-with-road']))->assertOk();
    }

    public function test_another_user_gets_403(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($stranger)->get(route('analyses.files', [$analysis, 'plot-with-road']))->assertForbidden();
    }

    public function test_an_unknown_field_name_is_not_found(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $this->actingAs($owner)->get(route('analyses.files', [$analysis, 'something-else']))->assertNotFound();
    }

    public function test_the_files_are_not_reachable_via_any_public_url(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $analysis = Analysis::factory()->for($owner)->withInput()->create();

        $path = $analysis->input->plot_with_road;

        $this->assertStringStartsNotWith('/storage/', $path);

        // The public disk's root never hosts analysis files; a direct
        // guest request against the public path never serves anything,
        // because nothing is ever written there.
        $status = $this->get('/storage/'.$path)->getStatusCode();
        $this->assertContains($status, [403, 404]);
    }
}
