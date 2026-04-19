<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'ADMIN';
    case MARIFATUN_USER = 'MARIFATUN_USER';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
