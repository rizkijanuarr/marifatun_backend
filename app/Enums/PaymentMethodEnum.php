<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case QRIS = 'qris';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
