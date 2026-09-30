<?php

declare(strict_types=1);

namespace App\Calendar;

use Symfony\Component\Clock\ClockInterface;

/**
 * "Today" as the application sees it: the current date in the Europe/Paris time zone,
 * without time part, read from the Symfony clock (BR-12; frozen in tests).
 */
final readonly class Today
{
    public const string TIMEZONE = 'Europe/Paris';

    public function __construct(private ClockInterface $clock)
    {
    }

    public function date(): \DateTimeImmutable
    {
        return $this->clock->now()->setTimezone(new \DateTimeZone(self::TIMEZONE))->setTime(0, 0);
    }
}
