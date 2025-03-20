<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum OrderClientType: string implements BackedEnumInteface
{

    case CONTACT = 'contact';
    case GROUP = 'group';
    case ORATOR = 'orator';
    case CONGRESS = 'congress';

    use BackedEnum;

    public static function default(): string
    {
        return self::CONTACT->value;
    }

    public static function baseGroups(): array
    {
        return [
            self::CONTACT->value,
            self::GROUP->value,
        ];
    }

}
