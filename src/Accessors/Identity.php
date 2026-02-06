<?php

declare(strict_types=1);

namespace MetaFramework\Accessors;

use MetaFramework\Models\SiteOwner;

class Identity
{
    public static function model(): ?SiteOwner
    {
        return cache()->rememberForever('mfw_siteowner', function () {
            return SiteOwner::query()->first();
        });
    }

    public static function get(string $key): ?string
    {
        return $key == 'address'
            ? implode(', ', (array) self::model()?->{$key})
            : self::model()?->{$key};

    }

    public static function addressBlock(): string
    {
        return implode("\n", (array) self::model()?->address);
    }

    public static function addressLine(int $line = 0): ?string
    {
        $address = (array) self::model()?->address;

        return $address[$line] ?? null;
    }
}
