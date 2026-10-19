<?php

namespace App\Enums;

enum Tier
{
    case General;
    case Premium;

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Premium => 'Premium',
        };
    }
}
