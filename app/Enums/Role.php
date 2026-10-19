<?php

namespace App\Enums;

enum Role: int
{
    case User = 1;
    case Admin = 2;
    case SuperAdmin = 3;

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::User => 'User',
        };
    }

    /**
     * The stable string identifier for this role, e.g. for CLI input and
     * matching the `roles.name` lookup table row.
     */
    public function slug(): string
    {
        return match ($this) {
            self::SuperAdmin => 'super_admin',
            self::Admin => 'admin',
            self::User => 'user',
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        return match ($slug) {
            'super_admin' => self::SuperAdmin,
            'admin' => self::Admin,
            'user' => self::User,
            default => null,
        };
    }
}
