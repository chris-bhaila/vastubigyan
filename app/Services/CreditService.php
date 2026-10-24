<?php

namespace App\Services;

use App\Enums\AnalysisStatus;
use App\Enums\CreditSource;
use App\Enums\CreditTransactionType;
use App\Enums\PurchaseStatus;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Analysis;
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
     * The acting admin, if any, is recorded on the ledger row for audit.
     */
    public function grantManual(User $user, int $count, ?Carbon $expiresAt = null, ?string $note = null, ?User $actor = null): CreditBatch
    {
        return DB::transaction(function () use ($user, $count, $expiresAt, $note, $actor) {
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
                'actor_id' => $actor?->id,
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
     * Give back the credit spent on a failed analysis. Idempotent: calling
     * this more than once for the same analysis restores only one credit.
     * Restores into the original batch if it hasn't expired yet; otherwise
     * grants a fresh replacement batch of 1 credit. Never restores for a
     * completed analysis.
     */
    public function restoreForAnalysis(Analysis $analysis): void
    {
        DB::transaction(function () use ($analysis) {
            $analysis = Analysis::query()->where('id', $analysis->id)->lockForUpdate()->first();

            if (! $analysis || $analysis->status === AnalysisStatus::Completed) {
                return;
            }

            $alreadyRestored = CreditTransaction::query()
                ->where('analysis_id', $analysis->id)
                ->where('type', CreditTransactionType::Refund)
                ->exists();

            if ($alreadyRestored) {
                return;
            }

            $usage = $analysis->credit_transaction_id
                ? CreditTransaction::query()->find($analysis->credit_transaction_id)
                : null;

            $originalBatch = $usage
                ? CreditBatch::query()->where('id', $usage->credit_batch_id)->lockForUpdate()->first()
                : null;

            if ($originalBatch && $originalBatch->expires_at->isFuture()) {
                $originalBatch->increment('remaining');
                $batch = $originalBatch;
            } else {
                $batch = CreditBatch::create([
                    'user_id' => $analysis->user_id,
                    'purchase_id' => null,
                    'source' => CreditSource::AdminGrant,
                    'total' => 1,
                    'remaining' => 1,
                    'expires_at' => now()->addDays(config('credits.restore_days', 30)),
                ]);
            }

            CreditTransaction::create([
                'user_id' => $analysis->user_id,
                'credit_batch_id' => $batch->id,
                'amount' => 1,
                'type' => CreditTransactionType::Refund,
                'analysis_id' => $analysis->id,
                'note' => "Refund for analysis #{$analysis->id}",
            ]);
        });
    }

    /**
     * Mark a purchase refunded and zero out its batch's remaining credits.
     * Credits already used are not clawed back. The acting admin, if any,
     * is recorded on the ledger row for audit. Idempotent: a purchase whose
     * batch is already at 0 (already refunded) writes no further row.
     */
    public function refund(Purchase $purchase, ?User $actor = null, ?string $note = null): void
    {
        DB::transaction(function () use ($purchase, $actor, $note) {
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
                'actor_id' => $actor?->id,
                'note' => $note ?? "Refund for purchase #{$purchase->id}",
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
