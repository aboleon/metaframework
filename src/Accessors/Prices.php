<?php

declare(strict_types=1);

namespace MetaFramework\Accessors;

class Prices
{
    public static function fromInteger(int $price): int|float
    {
        return $price / 100;
    }

    public static function toInteger(int|float|string $price): int
    {
        if (is_string($price)) {
            $price = floatval(str_replace(',', '.', $price));
        }
        $price = $price * 100;

        return (int) round($price);
    }

    public static function readableFormat(
        null|int|float $price = 0,
        string $currency = '€',
        string $decimal_separator = ',',
        string $thousand_separator = ' ',
        bool $showDecimals = true,
        bool $stripZeros = false,
        bool $round = false,
    ): string {
        if ($round && $price !== null) {
            $absolute_price = abs($price);
            $decimal_part = $absolute_price - floor($absolute_price);
            $rounded_price = $decimal_part >= 0.5 ? ceil($absolute_price) : floor($absolute_price);

            $price = $price < 0 ? -$rounded_price : $rounded_price;
        }

        $formatted_price = number_format($price, 2, $decimal_separator, $thousand_separator);

        [$integer_part, $decimal_part] = explode($decimal_separator, $formatted_price);

        if ($showDecimals) {
            if ($stripZeros && $decimal_part === '00') {
                return rtrim($integer_part . ' ' . $currency);
            }

            return rtrim($formatted_price . ' ' . $currency);
        }

        return rtrim($integer_part . ' ' . $currency);
    }
}
