<?php

namespace App\Accessors;

use App\Helpers\Front\FrontLocaleHelper;

class Dates
{



    public static function getFrontHourMinuteFormat(?string $type = null)
    {
        $locale = FrontLocaleHelper::getLocale();
        switch ($type) {
            case "x-mask":
                return match ($locale) {
                    'fr' => '99:99',
                    'en' => '99:99',
                    default => '99:99',
                };
            case "placeholder":
                return match ($locale) {
                    'fr' => 'hh:mm',
                    'en' => 'hh:mm',
                    default => 'hh:mm',
                };
            default:
                return match ($locale) {
                    'fr' => config('app.time_display_formats')['fr'],
                    'en' => config('app.time_display_formats')['en'],
                    default => 'H\hi',
                };
        }
    }

    public static function getFrontDateFormat(?string $type = null)
    {
        $locale = FrontLocaleHelper::getLocale();
        switch ($type) {
            case "mask":
                return match ($locale) {
                    'fr' => 'D/M/Y',
                    'en' => 'M/D/Y',
                    default => 'D/M/Y',
                };
            case "x-mask":
                return match ($locale) {
                    'fr' => '99/99/9999',
                    'en' => '99/99/9999',
                    default => '99/99/9999',
                };
            case "placeholder":
                return match ($locale) {
                    'fr' => 'jj/mm/aaaa',
                    'en' => 'mm/dd/yyyy',
                    default => 'jj/mm/aaaa',
                };
            default:
                return match ($locale) {
                    'fr' => config('app.date_display_formats')['fr'],
                    'en' => config('app.date_display_formats')['en'],
                    default => 'd/m/Y',
                };
        }
    }


    public static function getFrontDateTimeFormat()
    {
        $locale = FrontLocaleHelper::getLocale();
        return match ($locale) {
            'fr' => config('app.date_display_formats')['fr'] . ' H:i:s',
            'en' => config('app.date_display_formats')['en'] . ' H:i:s',
            default => 'd/m/Y H:i:s',
        };
    }


}