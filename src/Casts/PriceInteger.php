<?php

declare(strict_types=1);

namespace MetaFramework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PriceInteger implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return 0;
        }

        if (!is_numeric($value)) {
            return $value;
        }

        $stringValue = trim((string) $value);
        $isNegative = str_starts_with($stringValue, '-');
        $digits = preg_replace('/\D/', '', $stringValue);

        if ($digits === '') {
            return 0;
        }

        $digits = ltrim($digits, '0');
        if ($digits === '') {
            $digits = '0';
        }

        $decimal = str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
        $integer = strlen($digits) > 2 ? substr($digits, 0, -2) : '0';
        $formatted = $integer . '.' . $decimal;

        return $isNegative ? '-' . $formatted : $formatted;
    }

    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return 0;
        }

        if (!is_numeric($value)) {
            return $value;
        }

        $stringValue = trim((string) $value);
        $isNegative = str_starts_with($stringValue, '-');
        $normalized = str_replace(',', '.', $stringValue);
        $parts = explode('.', $normalized, 2);

        $whole = preg_replace('/\D/', '', $parts[0]);
        $fraction = preg_replace('/\D/', '', $parts[1] ?? '');
        $fraction = substr($fraction . '00', 0, 2);

        $digits = ltrim($whole . $fraction, '0');
        if ($digits === '') {
            $digits = '0';
        }

        $cents = (int) $digits;

        return $isNegative ? -$cents : $cents;
    }
}
