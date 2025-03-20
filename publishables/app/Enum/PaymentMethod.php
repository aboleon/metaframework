<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum PaymentMethod: string implements BackedEnumInteface
{

    case CB_PAYBOX = 'cb_paybox';
    case CB_VAD = 'cb_vad';

    case CHECK = 'check';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case REFUND = 'refund';


    use BackedEnum;

    public static function default(): string
    {
        return self::CASH->value;
    }

}
