<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderCartType: string implements BackedEnumInteface
{

    case SERVICE = 'service';
    case ACCOMMODATION = 'accommodation';
    case TAXROOM = 'taxroom';

    use BackedEnum;

    public static function default(): string
    {
        return self::SERVICE->value;
    }

    public static function defaultCarts(): array
    {
        return [
            self::SERVICE->value,
            self::ACCOMMODATION->value,
        ];
    }

}
