<?php

namespace App\Enums;

enum CreditSource: string
{
    case Purchase = 'purchase';
    case AdminGrant = 'admin_grant';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::AdminGrant => 'Admin Grant',
        };
    }
}
