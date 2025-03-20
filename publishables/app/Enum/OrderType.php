<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderType: string implements BackedEnumInteface
{

    case ORDER = 'order';
    case GRANTDEPOSIT = 'grantdeposit';
    case DEPOSIT = 'deposit';

    use BackedEnum;

    public static function default(): string
    {
        return self::ORDER->value;
    }

}
