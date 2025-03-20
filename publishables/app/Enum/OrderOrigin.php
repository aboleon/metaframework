<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderOrigin: string implements BackedEnumInteface
{

    case FRONT = 'front';
    case BACK = 'back';

    use BackedEnum;

    public static function default(): string
    {
        return self::BACK->value;
    }

}
