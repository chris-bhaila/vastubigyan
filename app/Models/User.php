<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Enums\Tier;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The model's default attribute values.
     *
     * Mirrors the `role` column's database default so a freshly built,
     * unsaved/unrefreshed instance still resolves a role instead of null.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::Admin, Role::SuperAdmin);
    }

    public function hasRole(Role|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof Role ? $role->value : $role;

            if ($this->role->value === $value) {
                return true;
            }
        }

        return false;
    }

    public function dashboardRouteName(): string
    {
        return 'dashboard';
    }

    public function creditBatches(): HasMany
    {
        return $this->hasMany(CreditBatch::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Sum of remaining credits across unexpired batches with credits left.
     */
    public function availableCredits(): int
    {
        return (int) $this->creditBatches()
            ->withCredits()
            ->unexpired()
            ->sum('remaining');
    }

    public function isPremium(): bool
    {
        return $this->availableCredits() > 0;
    }

    public function tier(): Tier
    {
        return $this->isPremium() ? Tier::Premium : Tier::General;
    }

    /**
     * The earliest expiry among batches that still have credits.
     */
    public function nextExpiry(): ?Carbon
    {
        $expiresAt = $this->creditBatches()
            ->withCredits()
            ->unexpired()
            ->min('expires_at');

        return $expiresAt ? Carbon::parse($expiresAt) : null;
    }
}
