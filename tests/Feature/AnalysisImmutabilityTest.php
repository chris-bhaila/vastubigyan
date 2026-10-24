<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Exceptions\AnalysisInputIsImmutableException;
use App\Models\Analysis;
use App\Models\AnalysisInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AnalysisImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_an_analysis_input_throws(): void
    {
        $input = AnalysisInput::factory()->create();

        $this->expectException(AnalysisInputIsImmutableException::class);

        $input->update(['first_name' => 'Changed']);
    }

    public function test_saving_a_dirty_existing_analysis_input_throws(): void
    {
        $input = AnalysisInput::factory()->create();
        $input->first_name = 'Changed';

        $this->expectException(AnalysisInputIsImmutableException::class);

        $input->save();
    }

    public function test_deleting_an_analysis_input_throws(): void
    {
        $input = AnalysisInput::factory()->create();

        $this->expectException(AnalysisInputIsImmutableException::class);

        $input->delete();
    }

    public function test_there_are_no_edit_or_delete_routes_for_analyses(): void
    {
        $methods = collect(Route::getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'analyses'))
            ->flatMap(fn ($route) => $route->methods());

        $this->assertFalse($methods->contains('PUT'));
        $this->assertFalse($methods->contains('PATCH'));
        $this->assertFalse($methods->contains('DELETE'));
    }

    public function test_an_admin_is_denied_update_and_delete_on_an_analysis(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => Role::Admin])->save();
        $analysis = Analysis::factory()->create();

        $this->assertFalse($admin->can('update', $analysis));
        $this->assertFalse($admin->can('delete', $analysis));
    }

    public function test_a_super_admin_is_denied_update_and_delete_on_an_analysis_despite_the_global_bypass(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();
        $analysis = Analysis::factory()->create();

        $this->assertFalse($superAdmin->can('update', $analysis));
        $this->assertFalse($superAdmin->can('delete', $analysis));
        $this->assertFalse($superAdmin->can('restore', $analysis));
        $this->assertFalse($superAdmin->can('forceDelete', $analysis));
    }

    public function test_a_super_admin_is_denied_update_and_delete_on_an_analysis_input_too(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => Role::SuperAdmin])->save();
        $input = AnalysisInput::factory()->create();

        $this->assertFalse($superAdmin->can('update', $input));
        $this->assertFalse($superAdmin->can('delete', $input));
    }

    public function test_verify_integrity_passes_for_an_untouched_row(): void
    {
        $input = AnalysisInput::factory()->create();

        $this->assertTrue($input->verifyIntegrity());
    }

    public function test_verify_integrity_fails_after_the_row_is_changed_directly_in_the_database(): void
    {
        $input = AnalysisInput::factory()->create();

        DB::table('analysis_inputs')->where('id', $input->id)->update(['first_name' => 'Tampered']);

        $this->assertFalse($input->fresh()->verifyIntegrity());
    }
}
