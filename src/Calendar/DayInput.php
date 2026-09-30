<?php

declare(strict_types=1);

namespace App\Calendar;

use App\Entity\DayEntry;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * One day of the save endpoint payload (spec 5.3): {"date": "2026-11-18", "state": "full", "note": ""}.
 */
final readonly class DayInput
{
    public function __construct(
        #[Assert\Date]
        public string $date,
        #[Assert\Choice(choices: CalendarDay::STATES)]
        public string $state,
        #[Assert\Length(max: DayEntry::NOTE_MAX_LENGTH)]
        public string $note = '',
    ) {
    }
}
