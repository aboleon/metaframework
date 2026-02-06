<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Support\Str;
use ReflectionClass;

trait BackedEnum
{
    public static function translationPrefix(): string
    {
        return '';
    }

    public static function varname(): string
    {
        return str_replace('_enum', '', Str::snake((new ReflectionClass(static::class))->getShortName()));
    }

    public static function keys(): array
    {
        return static::values();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function translated(string $key): string
    {
        return self::translations()[$key] ?? self::translations()[self::default()];
    }

    public static function translations(): array
    {
        return cache()->rememberForever('enum_.' . static::varname(), function () {
            $keys = self::keys();

            return array_combine(
                $keys,
                collect($keys)->map(fn ($item) => trans(self::translationPrefix() . 'enum.' . static::varname() . '.' . $item))->toArray()
            );
        });
    }

    public static function getValueFromTranslation(string $keyword): bool|string
    {
        return collect(self::translations())->search(function ($item, $key) use ($keyword) {
            return strtolower($item) == strtolower($keyword);
        });
    }
}
