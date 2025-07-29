<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

trait Locale
{

    public function locale(): string
    {
        /*
        if (request()->filled('lg') && in_array(request()->lg, config('mfw.translatable.locales'))) {
            return request()->lg;
        }
        */
        return app()->getLocale();
    }

    public function projectLocales()
    {
        return config('mfw.translatable.locales');
    }

    public function activeLocales()
    {
        return config('mfw.translatable.active_locales');
    }

    public function defaultLocale(): string
    {
        return config('app.fallback_locale');
    }

    public function localesAsSelectable(): array
    {
        return collect(trans('mfw-lang'))->sortBy('code')->pluck('label', 'code')->toArray();
    }

    public function alternateIsoLocales(): array
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

}
