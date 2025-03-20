<?php

namespace App\MailTemplates\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum MailTemplateFormat: string implements BackedEnumInteface
{

    case A4 = 'a4';
    case A5 = 'a5';

    use BackedEnum;

    public static function default(): string
    {
        return self::A4->value;
    }

}
