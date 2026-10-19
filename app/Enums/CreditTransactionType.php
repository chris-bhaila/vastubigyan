<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Expiry = 'expiry';
    case Refund = 'refund';
    case AdminGrant = 'admin_grant';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Usage => 'Usage',
            self::Expiry => 'Expiry',
            self::Refund => 'Refund',
            self::AdminGrant => 'Admin Grant',
        };
    }
}
