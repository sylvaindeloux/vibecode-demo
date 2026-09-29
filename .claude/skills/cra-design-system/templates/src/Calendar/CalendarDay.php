<?php

declare(strict_types=1);

namespace App\Calendar;

/**
 * View model of one calendar cell, consumed by <twig:Calendar:Day>.
 */
final readonly class CalendarDay
{
    public const KIND_WORKDAY = 'workday';
    public const KIND_WEEKEND = 'weekend';
    public const KIND_HOLIDAY = 'holiday';
    public const KIND_OUTSIDE = 'outside';

    public const STATE_EMPTY = 'empty';
    public const STATE_FULL = 'full';
    public const STATE_HALF = 'half';
    public const STATE_OFF = 'off';

    public const STATES = [self::STATE_EMPTY, self::STATE_FULL, self::STATE_HALF, self::STATE_OFF];

    public function __construct(
        public string $date,          // 'Y-m-d'
        public int $number,           // day of month
        public string $kind,          // KIND_*
        public string $state,         // STATE_*
        public string $note,          // '' when none, max 140 chars
        public bool $isToday,
        public ?string $holidayName,  // translated name, holidays only
        public string $label,         // 'lundi 14 septembre 2026' (localized, accessible name)
    ) {
    }

    public function quantity(): float
    {
        return match ($this->state) {
            self::STATE_FULL => 1.0,
            self::STATE_HALF => 0.5,
            default => 0.0,
        };
    }
}
