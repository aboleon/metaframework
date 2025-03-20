<?php

namespace App\Enum;

enum Civility: string
{
    case M = 'M';
    case F = 'F';

    public static function toArray(): array
    {
        $values = [];
        foreach(self::cases() as $case) {
            $values[$case->name] = $case->value;
        }

        return $values;
    }

    public static function getValue($key): ?string
    {
        return self::toArray()[$key] ?? null;
    }
}
