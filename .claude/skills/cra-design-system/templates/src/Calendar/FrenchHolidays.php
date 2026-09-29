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
     * @return array<string, string> date ('Y-m-d') => French name of the holiday
     */
    public static function forYear(int $year, bool $alsaceMoselle = false): array
    {
        $easter = self::easterSunday($year);
        $relative = static fn (int $days): string => $easter->modify(sprintf('%+d days', $days))->format('Y-m-d');

        $holidays = [
            sprintf('%d-01-01', $year) => 'Jour de l’an',
            $relative(1) => 'Lundi de Pâques',
            sprintf('%d-05-01', $year) => 'Fête du Travail',
            sprintf('%d-05-08', $year) => 'Victoire 1945',
            $relative(39) => 'Ascension',
            $relative(50) => 'Lundi de Pentecôte',
            sprintf('%d-07-14', $year) => 'Fête nationale',
            sprintf('%d-08-15', $year) => 'Assomption',
            sprintf('%d-11-01', $year) => 'Toussaint',
            sprintf('%d-11-11', $year) => 'Armistice 1918',
            sprintf('%d-12-25', $year) => 'Noël',
        ];

        if ($alsaceMoselle) {
            $holidays[$relative(-2)] = 'Vendredi saint';
            $holidays[sprintf('%d-12-26', $year)] = 'Saint-Étienne';
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
