<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = env('SUPER_ADMIN_NAME');
        $email = env('SUPER_ADMIN_EMAIL');
        $mobile = env('SUPER_ADMIN_MOBILE');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $name || ! $email || ! $mobile || ! $password) {
            $this->command?->warn('Skipping SuperAdminSeeder: SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL, SUPER_ADMIN_MOBILE, and SUPER_ADMIN_PASSWORD must all be set in .env.');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'mobile' => $mobile, 'password' => Hash::make($password)],
        );

        $user->forceFill(['role' => Role::SuperAdmin])->save();

        $this->command?->info("Super admin ready: {$email} ({$mobile})");
    }
}
