<?php

declare(strict_types=1);


namespace MetaFramework\Accessors;

use Illuminate\Support\Facades\Cache;

class Locale
{
    public static function multilang(): bool
    {
        return Cache::rememberForever('mfw.multilang', fn() => config('mfw.translatable.multilang')) ?? false;
    }

    public static function locale(): string
    {
        return app()->getLocale();
    }

    public static function locales(): array
    {
        return config('mfw.translatable.locales');
    }

    public static function activeLocales(): array
    {
        return config('mfw.translatable.active_locales');
    }

    public static function defaultLocale(): string
    {
        return config('app.fallback_locale');
    }

    public static function localesAsSelectable(bool $useOriginal = false): array
    {
        $locales = config('mfw.translatable.locales', ['fr', 'en']);
        $allTranslations = trans('mfw-lang');
        $labelKey = $useOriginal ? 'label_original' : 'label';

        return collect($locales)
            ->mapWithKeys(function ($locale) use ($allTranslations, $labelKey) {
                $label = $allTranslations[$locale][$labelKey] ?? $locale;
                return [$locale => $label];
            })
            ->toArray();
    }

    public static function alternateIsoLocales(): array
    {
        return collect(config('mfw.translatable.active_locales'))->reject(function ($item) {
            return $item == app()->getLocale();
        })->values()->toArray();
    }

    public static function openGraphAlternateLocales(): string
    {
        $output = '';

        foreach (config('mfw.translatable.active_locales') as $locale) {
            $output.= '<link rel="alternate" hreflang="'.$locale.'" href="'.url($locale).'" />'."\n";
            if ($locale == app()->getLocale()) {
                $output.= '<link rel="alternate" hreflang="x-default" href="'.url($locale).'" />'."\n";
                $output.= '<meta property="og:locale" content="'.$locale.'" />'."\n";
            } else {
                $output.= '<meta property="og:locale:alternate" content="'.$locale.'" />'."\n";
            }

        }
        return trim($output);
    }

    public static function projectLocales(): array
    {
        return config('mfw.translatable.locales');
    }

}
