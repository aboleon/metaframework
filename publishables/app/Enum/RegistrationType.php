<?php

namespace App\Enum;


use MetaFramework\Interfaces\BackedEnumInteface;
use MetaFramework\Traits\BackedEnum;

enum RegistrationType: string implements BackedEnumInteface
{

    case CONGRESS = 'congress';
    case INDUSTRY = 'industry';
    case LOGIN = 'login';

    case ORATOR = 'orator';

    case GROUP = 'group';
    case GROUP_MEMBER = 'group_member';
    case PARTICIPANT = 'participant';
    case SPEAKER = 'speaker';
    case SPEAKER_DEPRECATED = 'speakerDeprecated';

    use BackedEnum;

    public static function default(): string
    {
        return self::PARTICIPANT->value;
    }

    public static function defaultGroups(): array
    {
        return [
            self::PARTICIPANT->value,
            self::INDUSTRY->value,
            self::SPEAKER->value,
        ];
    }

}
