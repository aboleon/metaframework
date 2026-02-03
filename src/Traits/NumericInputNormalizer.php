<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

trait NumericInputNormalizer
{
    public function normalizeNumericValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(["\u{A0}", ' '], '', $value);

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalPos = max($lastComma, $lastDot);
            $integerPart = substr($value, 0, $decimalPos);
            $decimalPart = substr($value, $decimalPos + 1);
            $integerPart = preg_replace('/[^\d\-]/', '', $integerPart);
            $decimalPart = preg_replace('/[^\d]/', '', $decimalPart);

            return $integerPart . '.' . $decimalPart;
        }

        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d\.\-]/', '', $value);

        return $value;
    }

    public function normalizeNumericArrayValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeNumericValue($item), $value);
        }

        return $this->normalizeNumericValue($value);
    }
}
