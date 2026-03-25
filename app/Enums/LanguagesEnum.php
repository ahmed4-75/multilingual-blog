<?php

namespace App\Enums;

enum LanguagesEnum: string
{
    case Ar = 'ar';
    case En = 'en';
    case Sp = 'sp';
    case Ur = 'ur';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}
