<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PriceInteger implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return $value / 100;
    }

    public function set($model, $key, $value, $attributes)
    {
        if (!is_numeric($value)) {
            return 0;
        }
        return $value * 100;
    }
}
