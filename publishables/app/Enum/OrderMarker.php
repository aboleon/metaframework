<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderMarker: string implements BackedEnumInteface
{

    case NORMAL = 'normal';
    case GHOST = 'ghost';

    use BackedEnum;

    public static function default(): string
    {
        return self::NORMAL->value;
    }

}
