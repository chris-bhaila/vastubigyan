<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_super_admin_with_a_mobile_number_when_all_env_vars_are_set(): void
    {
        $this->withEnv([
            'SUPER_ADMIN_NAME' => 'Root Admin',
            'SUPER_ADMIN_EMAIL' => 'root-admin@example.com',
            'SUPER_ADMIN_MOBILE' => '9800000000',
            'SUPER_ADMIN_PASSWORD' => 'password123',
        ], fn () => $this->seed(SuperAdminSeeder::class));

        $user = User::where('email', 'root-admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('9800000000', $user->mobile);
        $this->assertTrue($user->hasRole(Role::SuperAdmin));
    }

    public function test_it_skips_without_erroring_when_mobile_is_missing(): void
    {
        $this->withEnv([
            'SUPER_ADMIN_NAME' => 'Root Admin',
            'SUPER_ADMIN_EMAIL' => 'root-admin@example.com',
            'SUPER_ADMIN_MOBILE' => '',
            'SUPER_ADMIN_PASSWORD' => 'password123',
        ], fn () => $this->seed(SuperAdminSeeder::class));

        $this->assertDatabaseMissing('users', ['email' => 'root-admin@example.com']);
    }

    private function withEnv(array $vars, \Closure $callback): void
    {
        $previous = [];

        foreach ($vars as $key => $value) {
            $previous[$key] = getenv($key);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        try {
            $callback();
        } finally {
            foreach ($previous as $key => $value) {
                if ($value === false) {
                    putenv($key);
                    unset($_ENV[$key]);
                } else {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}
