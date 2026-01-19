<?php

namespace MetaFramework\Accessors;

use Illuminate\Support\Str;
use MetaFramework\Models\Country;

class Countries
{

    /**
     * @return array<mixed>
     */
    public static function orderedCodeNameArray(): array
    {
        return cache()->rememberForever('countries_'.app()->getLocale(), function () {
            return Country::query()->select('name', 'code', 'name'.(Locale::multilang() ? '->'.app()->getLocale() : '').' as sortable')->get()
                ->sortBy(fn($item) => Str::slug($item->sortable))
                ->pluck('name', 'code')
                ->toArray();
        });
    }

    public static function getCountryNameByCode(?string $code = null): string
    {
        return self::getCountryNameByCodeAndLocale($code, app()->getLocale());
    }

    public static function getRawCountries()
    {
        return cache()->rememberForever('countries_raw', function () {
            return Country::all()->mapWithKeys(fn($country)
                => [
                $country->code => json_decode($country->getRawOriginal('name'), true),
            ]);
        });
    }

    public static function getCountryNameByCodeAndLocale(?string $code = null, ?string $locale = null): string
    {
        $locale = $locale ?: config('app.locale');

        return self::getRawCountries()[$code][$locale] ?? 'NC';
    }

}
