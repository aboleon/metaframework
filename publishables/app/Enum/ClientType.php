<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum ClientType: string implements BackedEnumInteface
{

    case COMPANY = 'company';
    case MEDICAL = 'medical';
    case OTHER = 'other';

    use BackedEnum;

    public static function default(): string
    {
        return self::OTHER->value;
    }

}
