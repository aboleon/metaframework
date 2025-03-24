<?php

namespace MetaFramework\Accessors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFramework\Models\Vat;

class VatAccessor
{
    public static function fetchVatRate(int $vat_id): int
    {
        return self::rate($vat_id);
    }

    public static function vatForPrice(null|float|int $price = 0, int $vat_id): float|int
    {
        if (!$price) {
            return 0;
        }

        $vat_rate = VatAccessor::fetchVatRate($vat_id);
        return round($price / (100 + $vat_rate) * $vat_rate, 2);
    }

    public static function netPriceFromVatPrice(null|float|int $price = 0, int $vat_id): float|int
    {
        if (!$price) {
            return 0;
        }

        return round($price - VatAccessor::vatForPrice($price, $vat_id), 2);
    }


    public static function vats(): Collection
    {
        return Cache::rememberForever('vats', fn() => Vat::query()->pluck('rate', 'id'));

    }

    public static function rate(?int $id): int|float
    {
        return self::vats()[$id] ?? self::defaultRate()->rate;
    }


    public static function defaultRate(): ?Vat
    {
        return Cache::rememberForever('default_vat_rate', fn() => Vat::query()->where('default', 1)->first());
    }

    public static function defaultId(): int
    {
        return self::defaultRate()->id ?? 0;
    }


    public static function readableArrayList(): array
    {
        return VatAccessor::vats()->sortBy('default')->map(fn($item) => $item .'%')->toArray();
    }

    public static function selectables(): array
    {
        return VatAccessor::readableArrayList();
    }

    public static function selectableOptionHtmlList($affected = null): string
    {
        $options = '';
        foreach (VatAccessor::vats()->sortBy('default') as $key => $value) {
            $options.='<option data-rate="'.$value.'" value="'.$key.'"'.($affected && $affected == $key ? ' selected' : '').'>'.$value.'%</option>'."\r\n";
        }
        return $options;
    }
}
