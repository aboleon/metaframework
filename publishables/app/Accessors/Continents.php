<?php

namespace App\Accessors;

use App\Models\Continent;

class Continents
{

    private static array $continentMap = [
        'AF' => ['en' => 'Africa', 'fr' => 'Afrique'],
        'AS' => ['en' => 'Asia', 'fr' => 'Asie'],
        'EU' => ['en' => 'Europe', 'fr' => 'Europe'],
        'NA' => ['en' => 'North America', 'fr' => 'Amérique du Nord'],
        'OC' => ['en' => 'Oceania', 'fr' => 'Océanie'],
        'SA' => ['en' => 'South America', 'fr' => 'Amérique du Sud'],
        'AN' => ['en' => 'Antarctica', 'fr' => 'Antarctique'],
    ];

    public static function getContinents(): array
    {
        return cache()->rememberForever('continents', fn() => Continent::query()->pluck('name', 'id')->toArray());
    }

    public static function getContinentKey($name): ?string
    {
        $name = strtolower($name); // Convert input to lowercase for case-insensitive matching
        foreach (self::$continentMap as $key => $names) {
            foreach ($names as $continentName) {
                if (str_contains(strtolower($continentName), $name)) {
                    return $key;
                }
            }
        }
        return null;
    }

    public static function getTranslatedName($key, $language = 'fr'): string
    {
        return self::$continentMap[$key][$language] ?? 'NC';
    }

}
