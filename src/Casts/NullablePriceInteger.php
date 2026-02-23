<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class NullablePriceInteger implements CastsAttributes
{
    /**
     * @throws \Exception
     */
    public function get($model, $key, $value, $attributes): int|float|null
    {
        if (!is_numeric($value)) {
            return null;
        }
        return $value / 100;
    }

    public function set($model, $key, $value, $attributes): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value * 100;
    }
}
