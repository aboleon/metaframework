<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderStatus: string implements BackedEnumInteface
{

    case UNPAID = 'unpaid';
    case PAID = 'paid';

    use BackedEnum;

    public static function default(): string
    {
        return self::UNPAID->value;
    }

}
