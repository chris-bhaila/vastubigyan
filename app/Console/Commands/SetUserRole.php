<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:set-role {email} {role}')]
#[Description('Set a user\'s role by email')]
class SetUserRole extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = Role::tryFrom($this->argument('role'));

        if (! $role) {
            $allowed = implode(', ', array_map(fn (Role $case) => $case->value, Role::cases()));
            $this->error("Invalid role. Allowed roles: {$allowed}");

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        $user->forceFill(['role' => $role])->save();

        $this->info("{$user->email} is now {$role->value}.");

        return self::SUCCESS;
    }
}
