<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('credits:grant {email} {count} {--days=365} {--note=}')]
#[Description('Grant a manual credit batch to a user (developer CLI use only)')]
class CreditsGrant extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CreditService $credits): int
    {
        $email = $this->argument('email');
        $count = (int) $this->argument('count');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        $batch = $credits->grantManual(
            $user,
            $count,
            now()->addDays((int) $this->option('days')),
            $this->option('note'),
        );

        $this->info("Granted {$count} credit(s) to {$user->email}, expiring {$batch->expires_at->toDateString()}.");

        return self::SUCCESS;
    }
}
