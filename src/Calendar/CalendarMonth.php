<?php

declare(strict_types=1);

namespace App\Calendar;

/**
 * View model of a month, consumed by <twig:Calendar> and its sub-components.
 */
final readonly class CalendarMonth
{
    /**
     * @param list<list<CalendarDay>>                    $weeks        Monday-first rows of 7 days (outside days included)
     * @param list<array{value: string, label: string}> $monthOptions options of the month <select>
     */
    public function __construct(
        public string $id,            // '2026-09'
        public string $label,         // 'septembre 2026'
        public array $weeks,
        public string $focusDate,     // date receiving tabindex=0 (today, else the 1st)
        public bool $isCurrent,       // true when this month contains today
        public string $previousUrl,
        public string $nextUrl,
        public string $currentUrl,
        public string $formAction,    // action of the month <select> GET form
        public array $monthOptions,
    ) {
    }

    /** @return list<CalendarDay> days of the month only */
    public function days(): array
    {
        return array_values(array_filter(array_merge(...$this->weeks), static fn (CalendarDay $d): bool => CalendarDay::KIND_OUTSIDE !== $d->kind));
    }

    public function workdayCount(): int
    {
        return \count(array_filter($this->days(), static fn (CalendarDay $d): bool => CalendarDay::KIND_WORKDAY === $d->kind));
    }

    /** @return array{full: int, half: int, off: int, total: float} */
    public function counts(): array
    {
        $count = fn (string $state): int => \count(array_filter($this->days(), static fn (CalendarDay $d): bool => $state === $d->state));
        $full = $count(CalendarDay::STATE_FULL);
        $half = $count(CalendarDay::STATE_HALF);

        return ['full' => $full, 'half' => $half, 'off' => $count(CalendarDay::STATE_OFF), 'total' => $full + $half / 2];
    }
}
