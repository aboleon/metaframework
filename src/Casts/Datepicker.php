<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use DateTime;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Datepicker implements CastsAttributes
{
    /**
     * @throws \Exception
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value) {
            $date = DateTime::createFromFormat('Y-m-d', $value);

            return $date->format('d/m/Y');
        }

        return null;
    }

    public function set($model, $key, $value, $attributes)
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        $date = DateTime::createFromFormat('d/m/Y', $value);

        return $date->format('Y-m-d');
    }
}
