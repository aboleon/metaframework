<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use MetaFramework\Support\Traits\Responses;
use Throwable;

trait DateManipulator
{
    use Responses;

    private Carbon $dateManipulatorFormattedDate;

    /**
     * @throws \Exception
     */
    public function makeDate(string|Carbon $date, string $format = 'd/m/Y H:i'
    ): Carbon {
        try {
            if ($date instanceof Carbon) {
                return $date;
            }

            return Carbon::createFromFormat($format, $date);
        } catch (Throwable $e) {
            $this->responseWarning('La date ' . $date . ' est invalide.');
            $this->responseError($e->getMessage());
        }
    }

    public function ensureValidEndDate(Carbon $starts, Carbon $ends): bool
    {
        try {
            if ($ends->lessThan($starts)) {
                $this->responseWarning(
                    'La date/temps de fin est supérieur à la date/temps du début.'
                );
            }

            return true;
        } catch (Throwable $e) {
            $this->responseError($e->getMessage());

            return false;
        }
    }

    public function weekDaysBetween(Carbon $starts, Carbon $ends): array
    {
        $days   = [];
        $period = CarbonPeriod::create($starts, $ends);
        foreach ($period as $date) {
            $days[] = $date->dayOfWeek;
        }

        return $days;
    }

    public function toDateFormat(string $format, mixed $date, ?string $nullable = null): ?string
    {
        if (!$date) {
            return !$nullable ? null : $nullable;
        }

        try {
            if (str_contains($date, '/')) {
                return Carbon::createFromFormat('d/m/Y', $date)->format($format);
            }

            return Carbon::parse($date)->format($format);

        } catch (Throwable) {
            return !$nullable ? null : $nullable;
        }
    }

    private function parseDate(?string $value, string $format = 'Y-m-d', ?string $nullable = null): ?string
    {
        return $this->toDateFormat($format, $value, $nullable);
    }

    private function parseHour(string $value): ?string
    {
        $value = trim($value);

        if (strlen($value) == 5) {
            try {
                $value = Carbon::parse(str_replace(['h', 'H', '-'], ':', $value)
                )->format('H:i');
            } catch (Throwable $e) {
                $value = null;
                // throw new \Exception($e);
            }
        } elseif (strlen($value) == 4) {
            try {
                $value = Carbon::parse($value)->format('H:i');
            } catch (Throwable $e) {
                $value = null;
                // throw new \Exception($e);
            }
        } else {
            $value = null;
        }

        return $value;
    }

    public function parseTimestamp(?string $value): ?string
    {
        $value = trim($value);

        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromTimestamp($value)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    public function parseDateTime(?string $value): ?string
    {
        $value = trim($value);

        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    public function fullReadableDate(Carbon $date): string
    {
        $date->locale(app()->getLocale());

        return ucfirst($date->dayName) . ' ' . $date->day . ' ' . ucfirst(
            $date->monthName
        ) . ' ' . $date->year;
    }

    public static function createYearRangeFromNowToPast(int $years): array
    {
        $period = CarbonPeriod::create(
            now()->subYears($years), '1 year', now()
        );
        $years  = [];
        foreach ($period as $date) {
            $years[$date->year] = $date->year;
        }
        arsort($years);

        return $years;
    }
}
