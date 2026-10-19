<?php

namespace App\Models;

use App\Enums\CreditSource;
use Database\Factories\CreditBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'purchase_id', 'source', 'total', 'remaining', 'expires_at'])]
class CreditBatch extends Model
{
    /** @use HasFactory<CreditBatchFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => CreditSource::class,
            'total' => 'integer',
            'remaining' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    #[Scope]
    protected function withCredits(Builder $query): Builder
    {
        return $query->where('remaining', '>', 0);
    }

    #[Scope]
    protected function unexpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    #[Scope]
    protected function expired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }
}
