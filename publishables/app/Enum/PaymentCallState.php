<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum PaymentCallState: string implements BackedEnumInteface
{

    case SUCCESS = 'success';
    case DECLINED = 'declined';
    case CANCEL = 'cancel';
    case OPEN = 'open';

    use BackedEnum;

    public static function default(): string
    {
        return self::OPEN->value;
    }

}
