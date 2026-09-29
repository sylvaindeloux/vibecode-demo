<?php

declare(strict_types=1);

namespace App\Calendar;

/**
 * French public holidays (jours fériés) for a given year, keyed by 'Y-m-d'.
 * Metropolitan France by default; Alsace-Moselle adds Good Friday and St Stephen's Day.
 * Requires no extension (Easter computed with the anonymous Gregorian algorithm).
 */
final class FrenchHolidays
{
    /**
     * @return array<string, string> date ('Y-m-d') => translation key of the holiday name
     */
    public static function forYear(int $year, bool $alsaceMoselle = false): array
    {
        $easter = self::easterSunday($year);
        $relative = static fn (int $days): string => $easter->modify(sprintf('%+d days', $days))->format('Y-m-d');

        $holidays = [
            sprintf('%d-01-01', $year) => 'holiday.new_year',
            $relative(1) => 'holiday.easter_monday',
            sprintf('%d-05-01', $year) => 'holiday.labour_day',
            sprintf('%d-05-08', $year) => 'holiday.victory_1945',
            $relative(39) => 'holiday.ascension',
            $relative(50) => 'holiday.whit_monday',
            sprintf('%d-07-14', $year) => 'holiday.bastille_day',
            sprintf('%d-08-15', $year) => 'holiday.assumption',
            sprintf('%d-11-01', $year) => 'holiday.all_saints',
            sprintf('%d-11-11', $year) => 'holiday.armistice',
            sprintf('%d-12-25', $year) => 'holiday.christmas',
        ];

        if ($alsaceMoselle) {
            $holidays[$relative(-2)] = 'holiday.good_friday';
            $holidays[sprintf('%d-12-26', $year)] = 'holiday.st_stephen';
        }

        ksort($holidays);

        return $holidays;
    }

    private static function easterSunday(int $year): \DateTimeImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new \DateTimeImmutable(sprintf('%d-%02d-%02d', $year, $month, $day));
    }
}
