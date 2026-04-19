<?php

namespace App\Enums;

enum ToneEnum: string
{
    case FORMAL = 'formal';
    case CASUAL = 'casual';
    case PERSUASIVE = 'persuasive';
    case FRIENDLY = 'friendly';
    case PROFESSIONAL = 'professional';
    case INSPIRATIONAL = 'inspirational';
    case HUMOROUS = 'humorous';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
