<?php

namespace App\Enums;

enum TopupStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
