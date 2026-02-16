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
            $date = $this->parseDate('Y-m-d', (string)$value);

            return $date?->format('d/m/Y');
        }

        return null;
    }

    public function set($model, $key, $value, $attributes)
    {
        if (!$value) {
            return null;
        }

        $value = (string)$value;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $this->parseDate('Y-m-d', $value)?->format('Y-m-d');
        }

        $date = $this->parseDate('d/m/Y', $value);

        return $date?->format('Y-m-d');
    }

    private function parseDate(string $format, string $value): ?DateTime
    {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        if (
            $date === false
            || ($errors['warning_count'] ?? 0) > 0
            || ($errors['error_count'] ?? 0) > 0
        ) {
            return null;
        }

        return $date;
    }
}
