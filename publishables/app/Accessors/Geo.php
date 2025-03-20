<?php

namespace App\Accessors;

class Geo
{
    public static function getContinentFromRequest(): array
    {
        $code = (string)request('country_code');
        $continent = (string)request('continent');

        return match ((string)request('address_type')) {
            'continent' => [
                'name' => $continent,
                'code' => Continents::getContinentKey($continent),
            ],
            default => [
                'name' => Countries::getContinentName($code),
                'code' => Countries::getContinentCode($code),
            ]
        };
    }
}
