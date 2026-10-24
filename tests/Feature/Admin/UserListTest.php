<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\CreditBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_name_or_email(): void
    {
        $superAdmin = $this->superAdmin();
        $alice = User::factory()->create(['name' => 'Alice Example', 'email' => 'alice@example.com']);
        $bob = User::factory()->create(['name' => 'Bob Example', 'email' => 'bob@example.com']);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index', ['search' => 'alice']));

        $response->assertOk();
        $response->assertSee('Alice Example');
        $response->assertDontSee('Bob Example');
    }

    public function test_filter_by_role(): void
    {
        $superAdmin = $this->superAdmin();
        $plainUser = User::factory()->create(['name' => 'Plain Person']);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index', ['role' => 'super_admin']));

        $response->assertOk();
        $response->assertSee($superAdmin->name);
        $response->assertDontSee('Plain Person');
    }

    public function test_filter_by_tier_premium_excludes_general_users(): void
    {
        $superAdmin = $this->superAdmin();
        $premiumUser = User::factory()->create(['name' => 'Premium Person']);
        CreditBatch::factory()->for($premiumUser)->create(['total' => 5, 'remaining' => 5]);

        $generalUser = User::factory()->create(['name' => 'General Person']);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index', ['tier' => 'premium']));

        $response->assertOk();
        $response->assertSee('Premium Person');
        $response->assertDontSee('General Person');
    }

    public function test_a_user_whose_only_batch_expired_shows_as_general(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create(['name' => 'Expired Batch Person']);
        CreditBatch::factory()->for($user)->expired()->create(['total' => 5, 'remaining' => 5]);

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Expired Batch Person');
        $response->assertSee('General');

        $listedUsers = $response->viewData('users');
        $found = $listedUsers->firstWhere('id', $user->id);
        $this->assertSame(0, (int) $found->available_credits);
    }

    public function test_the_user_list_does_not_run_a_per_row_query(): void
    {
        $superAdmin = $this->superAdmin();

        User::factory()->count(3)->create()->each(function (User $user) {
            CreditBatch::factory()->for($user)->create(['total' => 2, 'remaining' => 2]);
        });

        DB::enableQueryLog();
        $this->actingAs($superAdmin)->get(route('admin.users.index'));
        $queryCountForThree = count(DB::getQueryLog());
        DB::flushQueryLog();

        User::factory()->count(10)->create()->each(function (User $user) {
            CreditBatch::factory()->for($user)->create(['total' => 2, 'remaining' => 2]);
        });

        DB::flushQueryLog();
        $this->actingAs($superAdmin)->get(route('admin.users.index'));
        $queryCountForThirteen = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($queryCountForThree, $queryCountForThirteen);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['name' => 'Super Admin Person']);
        $user->forceFill(['role' => Role::SuperAdmin])->save();

        return $user;
    }
}
