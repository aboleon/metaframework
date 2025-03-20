<?php

namespace App\Accessors;

use App\Helpers\PhoneCountryHelper;
use App\Models\Country;

class Countries
{

    public static function getCode2Name(): array
    {
        return cache()->rememberForever(__METHOD__, fn() => Country::all()->pluck('name', 'code')->toArray());
    }

    public static function countryToContinent(): array
    {
        return cache()->rememberForever('countryToContinent', fn() => Country::query()->pluck('continent_id', 'code')->mapWithKeys(fn($item, $key) => [$key => Continents::getContinents()[$item] ?? 'NC'])->toArray());
    }

    public static function getContinentCode(string $countryCode): ?string
    {
        return self::countryToContinent()[$countryCode] ?? null;
    }

    public static function getContinentName(string $countryCode): ?string
    {
        return Continents::getTranslatedName(self::countryToContinent()[$countryCode], app()->getLocale());
    }

    public static function getCountryName(string $countryCode): ?string
    {
        return self::getCode2Name()[$countryCode] ?? null;
    }

    public static function getCountryCodeToCountryCallingCode(): array
    {
        return cache()->rememberForever(__METHOD__, function () {
            return Country::all()->pluck('code', 'code')->map(function ($code) {
                return PhoneCountryHelper::getCountryCallingCodeByCountryCode($code);
            })->toArray();
        });
    }
}
