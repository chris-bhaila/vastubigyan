<?php

namespace App\Services;

use App\Enums\CreditSource;
use App\Enums\CreditTransactionType;
use App\Enums\PurchaseStatus;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditBatch;
use App\Models\CreditTransaction;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Create the credit batch for a paid purchase. Idempotent: a unique
     * constraint on credit_batches.purchase_id backs this against duplicate
     * webhook retries, including concurrent ones.
     */
    public function grantFromPurchase(Purchase $purchase): ?CreditBatch
    {
        if ($purchase->status !== PurchaseStatus::Paid) {
            return null;
        }

        return DB::transaction(function () use ($purchase) {
            $existing = CreditBatch::query()->where('purchase_id', $purchase->id)->first();

            if ($existing) {
                return $existing;
            }

            $package = $purchase->package;

            try {
                $batch = CreditBatch::create([
                    'user_id' => $purchase->user_id,
                    'purchase_id' => $purchase->id,
                    'source' => CreditSource::Purchase,
                    'total' => $package->analysis_count,
                    'remaining' => $package->analysis_count,
                    'expires_at' => $purchase->paid_at->copy()->addDays($package->validity_days),
                ]);
            } catch (QueryException $e) {
                // Another process won the race and already created the batch.
                return CreditBatch::query()->where('purchase_id', $purchase->id)->first();
            }

            CreditTransaction::create([
                'user_id' => $purchase->user_id,
                'credit_batch_id' => $batch->id,
                'amount' => $package->analysis_count,
                'type' => CreditTransactionType::Purchase,
                'note' => "Purchase #{$purchase->id}",
            ]);

            return $batch;
        });
    }

    /**
     * Grant a batch of credits outside of a purchase, e.g. a goodwill gesture.
     */
    public function grantManual(User $user, int $count, ?Carbon $expiresAt = null, ?string $note = null): CreditBatch
    {
        return DB::transaction(function () use ($user, $count, $expiresAt, $note) {
            $batch = CreditBatch::create([
                'user_id' => $user->id,
                'purchase_id' => null,
                'source' => CreditSource::AdminGrant,
                'total' => $count,
                'remaining' => $count,
                'expires_at' => $expiresAt ?? now()->addDays(365),
            ]);

            CreditTransaction::create([
                'user_id' => $user->id,
                'credit_batch_id' => $batch->id,
                'amount' => $count,
                'type' => CreditTransactionType::AdminGrant,
                'note' => $note,
            ]);

            return $batch;
        });
    }

    /**
     * Spend one credit from the batch that expires soonest. Row-locked so
     * concurrent requests cannot spend the same credit twice.
     */
    public function consume(User $user, ?int $analysisId = null): CreditTransaction
    {
        return DB::transaction(function () use ($user, $analysisId) {
            $batch = CreditBatch::query()
                ->where('user_id', $user->id)
                ->where('remaining', '>', 0)
                ->where('expires_at', '>', now())
                ->orderBy('expires_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $batch) {
                throw new InsufficientCreditsException;
            }

            $batch->decrement('remaining');

            return CreditTransaction::create([
                'user_id' => $user->id,
                'credit_batch_id' => $batch->id,
                'amount' => -1,
                'type' => CreditTransactionType::Usage,
                'analysis_id' => $analysisId,
            ]);
        });
    }

    /**
     * Mark a purchase refunded and zero out its batch's remaining credits.
     * Credits already used are not clawed back.
     */
    public function refund(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->update(['status' => PurchaseStatus::Refunded]);

            $batch = CreditBatch::query()
                ->where('purchase_id', $purchase->id)
                ->lockForUpdate()
                ->first();

            if (! $batch || $batch->remaining <= 0) {
                return;
            }

            $removed = $batch->remaining;
            $batch->update(['remaining' => 0]);

            CreditTransaction::create([
                'user_id' => $purchase->user_id,
                'credit_batch_id' => $batch->id,
                'amount' => -$removed,
                'type' => CreditTransactionType::Refund,
                'note' => "Refund for purchase #{$purchase->id}",
            ]);
        });
    }

    /**
     * Expire every due batch that still has credits left. Idempotent: a
     * batch that has already been expired has remaining = 0 and is skipped.
     */
    public function expireDue(): int
    {
        return DB::transaction(function () {
            $batches = CreditBatch::query()
                ->where('remaining', '>', 0)
                ->where('expires_at', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                CreditTransaction::create([
                    'user_id' => $batch->user_id,
                    'credit_batch_id' => $batch->id,
                    'amount' => -$batch->remaining,
                    'type' => CreditTransactionType::Expiry,
                    'note' => 'Batch expired',
                ]);

                $batch->update(['remaining' => 0]);
            }

            return $batches->count();
        });
    }
}
