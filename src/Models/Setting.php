<?php

declare(strict_types=1);

namespace MetaFramework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    public $fillable = ['name', 'value'];

    public static function getValidationRules(): array
    {
        return self::getDefinedSettingFields()->pluck('rules', 'name')
            ->reject(function ($val) {
                return is_null($val);
            })->toArray();
    }

    public static function getConfigElementsKeys(): array
    {
        return collect(config('mfw-settings'))->pluck('elements.*.name')->flatten()->toArray();
    }

    public static function getConfigElements(): array
    {
        return collect(config('mfw-settings'))->reduce(function ($carry, $item) {
            return array_merge($carry, $item['elements'] ?? []);
        }, []);
    }

    private static function getDefinedSettingFields(): Collection
    {
        return collect(config('mfw-settings'))->pluck('elements')->flatten(1);
    }

    public static function add($key, $val): ?Setting
    {
        $setting = self::get($key);
        if ($setting) {
            $setting->value = $val;
            $setting->save();
        } else {
            if (Setting::defaultSettingValue($key) != $val) {
                $setting = Setting::create(['name' => $key, 'value' => $val]);
            }
        }

        return $setting;
    }

    public static function get(string $key): ?Setting
    {
        return self::getAllSettings()->filter(fn ($item) => $item->name == $key)->first();
    }

    public static function value(string $key): ?string
    {
        return self::getAllSettings()->filter(fn ($item) => $item->name == $key)->first()?->value ?? self::defaultSettingValue($key);
    }

    public static function defaultSettingValue(string $key): ?string
    {
        return collect(Setting::getConfigElements())->filter(fn ($item) => $item['name'] == $key)->first()['default'] ?? null;
    }

    public static function getAllSettings(): Collection
    {
        return cache()->rememberForever('mfw-settings', function () {
            return Setting::all();
        });
    }

    public static function getDefaultValueForField($key): string
    {
        if ($setting = self::getDefinedSettingFields()->where('name', $key)->first()) {
            return $setting['default'] ?? '';
        }

        return '';
    }
}
