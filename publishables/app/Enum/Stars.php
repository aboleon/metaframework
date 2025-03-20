<?php

namespace App\Enum;

enum Stars: int
{
    case One = 1;
    case Two = 2;
    case Three = 3;
    case Four = 4;
    case Five = 5;

    public static function toArray(): array
    {
        $values = array_column(self::cases(), 'value');
        return array_combine($values, $values);
    }
}
