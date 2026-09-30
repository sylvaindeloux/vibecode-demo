<?php

declare(strict_types=1);

namespace App\Tests\Calendar;

use App\Calendar\FrenchHolidays;
use PHPUnit\Framework\TestCase;

final class FrenchHolidaysTest extends TestCase
{
    public function testMetropolitanHolidaysOf2026(): void
    {
        $holidays = FrenchHolidays::forYear(2026);

        self::assertSame([
            '2026-01-01' => 'Jour de l’an',
            '2026-04-06' => 'Lundi de Pâques',
            '2026-05-01' => 'Fête du Travail',
            '2026-05-08' => 'Victoire 1945',
            '2026-05-14' => 'Ascension',
            '2026-05-25' => 'Lundi de Pentecôte',
            '2026-07-14' => 'Fête nationale',
            '2026-08-15' => 'Assomption',
            '2026-11-01' => 'Toussaint',
            '2026-11-11' => 'Armistice 1918',
            '2026-12-25' => 'Noël',
        ], $holidays);
    }
}
