<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class NullablePriceInteger implements CastsAttributes
{
    /**
     * @throws \Exception
     */
    public function get($model, $key, $value, $attributes): ?int
    {
        return isset($value) ? $value/100 : null;
    }

    public function set($model, $key, $value, $attributes): ?int
    {
        return isset($value) ? $value*100 : null;
    }
}
