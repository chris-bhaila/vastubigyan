<?php

namespace App\Console\Commands;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Services\EsewaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('esewa:reconcile')]
#[Description('Reconcile pending eSewa purchases against the status check API')]
class EsewaReconcile extends Command
{
    /**
     * Covers a user who paid and closed the browser before the success
     * redirect completed. Safe to run concurrently with the callbacks:
     * settling and failing both lock the purchase row.
     */
    public function handle(EsewaService $esewa): int
    {
        $purchases = Purchase::query()
            ->where('gateway', 'esewa')
            ->where('status', PurchaseStatus::Pending)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $settled = 0;
        $failed = 0;

        foreach ($purchases as $purchase) {
            $response = $esewa->checkStatus($purchase);
            $status = $response['status'] ?? null;

            if ($status === 'COMPLETE') {
                $esewa->settle($purchase, $response);
                $settled++;
            } elseif (in_array($status, ['CANCELED', 'NOT_FOUND'], true)) {
                $esewa->markFailed($purchase);
                $failed++;
            }
        }

        $this->info("Checked {$purchases->count()} pending eSewa purchase(s): settled {$settled}, failed {$failed}.");

        return self::SUCCESS;
    }
}
