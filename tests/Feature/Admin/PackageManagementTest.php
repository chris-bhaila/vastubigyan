<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\CreditBatch;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_super_admin_can_update_a_package(): void
    {
        $superAdmin = $this->superAdmin();
        $package = Package::factory()->create();

        $response = $this->actingAs($superAdmin)->put(route('admin.packages.update', $package), [
            'name' => 'Renamed Package',
            'analysis_count' => 7,
            'price' => 5000,
            'validity_days' => 180,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $package = $package->fresh();
        $this->assertSame('Renamed Package', $package->name);
        $this->assertSame(7, $package->analysis_count);
        $this->assertSame(5000, $package->price);
        $this->assertSame(180, $package->validity_days);
        $this->assertTrue($package->is_active);
    }

    public function test_updating_a_package_validates_its_inputs(): void
    {
        $superAdmin = $this->superAdmin();
        $package = Package::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($superAdmin)->put(route('admin.packages.update', $package), [
            'name' => '',
            'analysis_count' => 0,
            'price' => -100,
            'validity_days' => 0,
        ]);

        $response->assertSessionHasErrors(['name', 'analysis_count', 'price', 'validity_days']);
        $this->assertSame('Original Name', $package->fresh()->name);
    }

    public function test_updating_a_package_never_alters_existing_purchases_or_batches(): void
    {
        $superAdmin = $this->superAdmin();
        $package = Package::factory()->create(['price' => 1000, 'analysis_count' => 1]);

        $purchase = Purchase::factory()->paid()->create(['package_id' => $package->id, 'amount' => 1000]);
        $batch = CreditBatch::factory()->create(['purchase_id' => $purchase->id, 'total' => 1, 'remaining' => 1]);

        $this->actingAs($superAdmin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'analysis_count' => 20,
            'price' => 99999,
            'validity_days' => 999,
        ]);

        $this->assertSame(1000, $purchase->fresh()->amount);
        $this->assertSame(1, $batch->fresh()->total);
        $this->assertSame(1, $batch->fresh()->remaining);
    }

    public function test_a_deactivated_package_cannot_be_bought(): void
    {
        $superAdmin = $this->superAdmin();
        $user = User::factory()->create();
        $package = Package::factory()->create(['is_active' => true]);

        $this->actingAs($superAdmin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'analysis_count' => $package->analysis_count,
            'price' => $package->price,
            'validity_days' => $package->validity_days,
            'is_active' => '0',
        ]);

        $this->assertFalse($package->fresh()->is_active);

        $this->actingAs($user)->post(route('checkout.store', $package))->assertNotFound();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => Role::SuperAdmin])->save();

        return $user;
    }
}
