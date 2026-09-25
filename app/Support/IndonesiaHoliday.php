<?php

namespace App\Support;

use Carbon\Carbon;

class IndonesiaHoliday
{
    public static function datesForYear(int $year): array
    {
        $fixed = [
            $year.'-01-01',
            $year.'-05-01',
            $year.'-06-01',
            $year.'-08-17',
            $year.'-12-25',
        ];

        $byYear = [
            2025 => ['2025-01-27', '2025-01-28', '2025-01-29', '2025-03-28', '2025-03-29', '2025-03-31', '2025-04-01', '2025-04-18', '2025-05-12', '2025-05-29', '2025-06-06', '2025-06-07', '2025-06-27', '2025-09-05'],
            2026 => ['2026-01-16', '2026-01-17', '2026-02-17', '2026-03-19', '2026-03-20', '2026-03-21', '2026-04-03', '2026-05-01', '2026-05-14', '2026-05-27', '2026-05-28', '2026-06-16', '2026-08-17', '2026-08-25', '2026-12-25'],
            2027 => ['2027-01-06', '2027-01-07', '2027-02-06', '2027-03-09', '2027-03-10', '2027-03-21', '2027-04-27', '2027-05-01', '2027-05-06', '2027-05-17', '2027-05-18', '2027-06-06', '2027-08-17', '2027-08-14', '2027-12-25'],
        ];

        return array_values(array_unique(array_merge($fixed, $byYear[$year] ?? [])));
    }

    public static function isHoliday(Carbon|string $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return in_array($carbon->toDateString(), self::datesForYear((int) $carbon->year), true);
    }

    public static function isClosedDay(Carbon|string $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->isWeekend() || self::isHoliday($carbon);
    }
}
