<?php

namespace App\Enums;

enum SocialAuthDriverEnum: string
{
    case GOOGLE = 'google';
    case GITHUB = 'github';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}
