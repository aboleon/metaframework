<?php

namespace App\Accessors;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Chronos
{
    public static function createYearRangeFromNowToPast(int $years): array
    {
        $period = CarbonPeriod::create(now()->subYears($years), '1 year', now());
        $years = [];
        foreach ($period as $date) {
            $years[$date->year] = $date->year;
        }
        arsort($years);
        return $years;
    }

    public static function formatDate(?Carbon $date): string|null
    {
        if (null === $date) {
            return null;
        }
        return $date->format(config('app.date_display_format'));
    }

    public static function formatTime(?Carbon $time, bool $useSeconds = false): string|null
    {
        if (null === $time) {
            return null;
        }
        if ($useSeconds) {
            return $time->format('H\hi\ms\s');
        }
        return $time->format('H\hi');
    }
}
