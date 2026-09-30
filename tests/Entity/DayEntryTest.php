<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Calendar\CalendarDay;
use App\Entity\Client;
use App\Entity\DayEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DayEntryTest extends TestCase
{
    #[DataProvider('provideStates')]
    public function testQuantity(string $state, float $expected): void
    {
        $entry = new DayEntry(new Client(), new \DateTimeImmutable('2026-11-18'), $state);

        self::assertSame($expected, $entry->quantity());
    }

    /** @return iterable<string, array{string, float}> */
    public static function provideStates(): iterable
    {
        yield 'full' => [CalendarDay::STATE_FULL, 1.0];
        yield 'half' => [CalendarDay::STATE_HALF, 0.5];
        yield 'off' => [CalendarDay::STATE_OFF, 0.0];
        yield 'empty' => [CalendarDay::STATE_EMPTY, 0.0];
    }

    public function testIsBlankOnlyWhenEmptyWithoutNote(): void
    {
        $date = new \DateTimeImmutable('2026-11-18');

        self::assertTrue((new DayEntry(new Client(), $date))->isBlank());
        self::assertFalse((new DayEntry(new Client(), $date, CalendarDay::STATE_OFF))->isBlank());
        self::assertFalse((new DayEntry(new Client(), $date, CalendarDay::STATE_EMPTY, 'Note'))->isBlank());
    }
}
