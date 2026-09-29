<?php

declare(strict_types=1);

namespace App\Calendar;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Builds the CalendarMonth view model. Reference implementation: adapt the URL callback
 * and the source of day entries to the application.
 */
final readonly class CalendarMonthFactory
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @param array<string, array{state: string, note?: string}> $entries saved days, keyed by 'Y-m-d'
     * @param callable(string $monthId): string                   $urlFor     URL of the calendar for a month ('Y-m')
     * @param string                                              $formAction URL of the calendar WITHOUT query string (GET form action)
     */
    public function create(
        \DateTimeImmutable $month,
        array $entries,
        callable $urlFor,
        string $formAction,
        ?\DateTimeImmutable $today = null,
        string $locale = 'fr',
        bool $alsaceMoselle = false,
    ): CalendarMonth {
        $today ??= new \DateTimeImmutable('today');
        $first = $month->modify('first day of this month')->setTime(0, 0);
        $last = $first->modify('last day of this month');
        $start = $first->modify('-'.((int) $first->format('N') - 1).' days');
        $end = $last->modify('+'.(7 - (int) $last->format('N')).' days');
        $holidays = FrenchHolidays::forYear((int) $first->format('Y'), $alsaceMoselle);
        $dayFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'EEEE d MMMM y');
        $monthFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'LLLL y');

        $weeks = [];
        $week = [];
        for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
            $key = $date->format('Y-m-d');
            $inMonth = $date->format('Y-m') === $first->format('Y-m');
            $kind = match (true) {
                !$inMonth => CalendarDay::KIND_OUTSIDE,
                isset($holidays[$key]) => CalendarDay::KIND_HOLIDAY,
                (int) $date->format('N') >= 6 => CalendarDay::KIND_WEEKEND,
                default => CalendarDay::KIND_WORKDAY,
            };
            $entry = $inMonth ? ($entries[$key] ?? null) : null;

            $week[] = new CalendarDay(
                date: $key,
                number: (int) $date->format('j'),
                kind: $kind,
                state: \in_array($entry['state'] ?? null, CalendarDay::STATES, true) ? $entry['state'] : CalendarDay::STATE_EMPTY,
                note: (string) ($entry['note'] ?? ''),
                isToday: $key === $today->format('Y-m-d'),
                holidayName: isset($holidays[$key]) && $inMonth ? $this->translator->trans($holidays[$key]) : null,
                label: (string) $dayFormatter->format($date),
            );

            if (7 === \count($week)) {
                $weeks[] = $week;
                $week = [];
            }
        }

        $options = [];
        for ($i = -12; $i <= 2; ++$i) {
            $m = $today->modify('first day of this month')->modify(sprintf('%+d months', $i));
            $options[] = ['value' => $m->format('Y-m'), 'label' => ucfirst((string) $monthFormatter->format($m))];
        }
        if (!\in_array($first->format('Y-m'), array_column($options, 'value'), true)) {
            array_unshift($options, ['value' => $first->format('Y-m'), 'label' => ucfirst((string) $monthFormatter->format($first))]);
        }

        $isCurrent = $first->format('Y-m') === $today->format('Y-m');

        return new CalendarMonth(
            id: $first->format('Y-m'),
            label: (string) $monthFormatter->format($first),
            weeks: $weeks,
            focusDate: $isCurrent ? $today->format('Y-m-d') : $first->format('Y-m-d'),
            isCurrent: $isCurrent,
            previousUrl: $urlFor($first->modify('-1 month')->format('Y-m')),
            nextUrl: $urlFor($first->modify('+1 month')->format('Y-m')),
            currentUrl: $urlFor($today->format('Y-m')),
            formAction: $formAction,
            monthOptions: array_reverse($options),
        );
    }
}
