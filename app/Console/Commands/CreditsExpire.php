<?php

namespace App\Console\Commands;

use App\Services\CreditService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('credits:expire')]
#[Description('Expire all due credit batches')]
class CreditsExpire extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CreditService $credits): int
    {
        $count = $credits->expireDue();

        $this->info("Expired {$count} credit batch(es).");

        return self::SUCCESS;
    }
}
