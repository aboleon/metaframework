<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum CancellationStatus: string implements BackedEnumInteface
{

    case FULL = 'full';
    case PARTIAL = 'partial';

    use BackedEnum;

    public static function default(): string
    {
        return self::PARTIAL->value;
    }

}
