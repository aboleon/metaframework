<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum DictionnaryType: string implements BackedEnumInteface
{

    case SIMPLE = 'simple';
    case META = 'meta';
    case CUSTOM = 'custom';

    use BackedEnum;

    public static function default(): string
    {
        return self::SIMPLE->value;
    }

}
