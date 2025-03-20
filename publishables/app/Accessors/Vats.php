<?php

namespace App\Accessors;


use App\Models\Vat;

class Vats
{
    public static function selectable(): array
    {
        return Vat::pluck('rate', 'id')
            ->mapWithKeys(function ($rate, $id) {
                return [$id => round($rate / 100, 2)  . " %"];
            })
            ->toArray();
    }
}
