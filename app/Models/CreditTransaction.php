<?php

namespace App\Models;

use App\Enums\CreditTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['user_id', 'actor_id', 'credit_batch_id', 'amount', 'type', 'analysis_id', 'note'])]
class CreditTransaction extends Model
{
    /**
     * The ledger is append-only: rows are never updated after creation.
     */
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'type' => CreditTransactionType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function creditBatch(): BelongsTo
    {
        return $this->belongsTo(CreditBatch::class);
    }

    /**
     * Guard the append-only ledger against updates and deletes.
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Credit transactions are append-only and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new LogicException('Credit transactions are append-only and cannot be deleted.');
        });
    }
}
