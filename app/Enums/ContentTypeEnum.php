<?php

namespace App\Enums;

enum ContentTypeEnum: string
{
    case LINKEDIN = 'linkedin';
    case X = 'x';
    case THREAD = 'thread';
    case FACEBOOK = 'facebook';
    case EMAIL_MARKETING = 'email_marketing';

    case VIDEO_SCRIPT = 'video_script';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::LINKEDIN => 'LinkedIn Post',
            self::X => 'X (Twitter) Post',
            self::THREAD => 'Threads Post',
            self::FACEBOOK => 'Facebook Post',
            self::EMAIL_MARKETING => 'Email Marketing',
            self::VIDEO_SCRIPT => 'Skrip video (short-form)',
        };
    }
}
