<?php

namespace App\MailTemplates\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum MailTemplateMode: string implements BackedEnumInteface
{

    case PORTRAIT = 'portrait';
    case LANDSCAPE = 'landscape';

    use BackedEnum;

    public static function default(): string
    {
        return self::PORTRAIT->value;
    }

}
