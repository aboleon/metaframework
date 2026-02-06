<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ForceInteger implements CastsAttributes
{
    /**
     * @throws \Exception
     */
    public function get($model, $key, $value, $attributes)
    {
        return $this->toInteger($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return $this->toInteger($value);
    }

    private function toInteger($value): int
    {
        return is_numeric($value) ? intval($value) : 0;
    }
}
