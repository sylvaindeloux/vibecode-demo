<?php

declare(strict_types=1);

namespace App\Tests\Calendar;

use App\Calendar\CalendarDay;
use App\Calendar\CalendarMonth;
use App\Calendar\CalendarMonthFactory;
use PHPUnit\Framework\TestCase;

final class CalendarMonthTest extends TestCase
{
    public function testNovember2026HasTwentyWorkdays(): void
    {
        // 21 weekdays, minus Armistice Day (Wednesday 11 November). All Saints' Day falls on a Sunday.
        self::assertSame(20, $this->november2026([])->workdayCount());
    }

    public function testCountsAndTotal(): void
    {
        $month = $this->november2026([
            '2026-11-02' => ['state' => CalendarDay::STATE_FULL],
            '2026-11-03' => ['state' => CalendarDay::STATE_FULL, 'note' => 'Atelier'],
            '2026-11-04' => ['state' => CalendarDay::STATE_HALF],
            '2026-11-05' => ['state' => CalendarDay::STATE_OFF],
            '2026-11-06' => ['state' => CalendarDay::STATE_EMPTY, 'note' => 'Note only'],
            '2026-11-14' => ['state' => CalendarDay::STATE_FULL],
        ]);

        self::assertSame(['full' => 3, 'half' => 1, 'off' => 1, 'total' => 3.5], $month->counts());
    }

    public function testGridAndDayKinds(): void
    {
        $month = $this->november2026([]);
        $days = $month->days();

        self::assertCount(6, $month->weeks);
        self::assertCount(30, $days);
        self::assertSame(CalendarDay::KIND_OUTSIDE, $month->weeks[0][0]->kind);
        self::assertSame(CalendarDay::KIND_HOLIDAY, $days[0]->kind, 'All Saints\' Day on a Sunday keeps the holiday kind');
        self::assertSame('Toussaint', $days[0]->holidayName);
        self::assertSame(CalendarDay::KIND_WEEKEND, $days[6]->kind);
        self::assertSame(CalendarDay::KIND_WORKDAY, $days[1]->kind);
        self::assertTrue($days[16]->isToday);
        self::assertSame('2026-11-17', $month->focusDate);
        self::assertSame('lundi 2 novembre 2026', $days[1]->label);
    }

    public function testMonthOptionsListTheTwelvePastMonthsAndTheTwoNextOnes(): void
    {
        $options = array_column($this->november2026([])->monthOptions, 'value');

        self::assertSame('2027-01', $options[0]);
        self::assertSame('2025-11', $options[14]);
        self::assertCount(15, $options);
    }

    /**
     * @param array<string, array{state: string, note?: string}> $entries
     */
    private function november2026(array $entries): CalendarMonth
    {
        return (new CalendarMonthFactory())->create(
            new \DateTimeImmutable('2026-11-01'),
            $entries,
            static fn (string $id): string => '/?month='.$id,
            '/',
            new \DateTimeImmutable('2026-11-17'),
        );
    }
}
