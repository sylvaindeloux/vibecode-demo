<?php

declare(strict_types=1);

namespace App\Calendar;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payload of the save endpoint (spec 5.3): {"days": [DayInput, …]}, 1 to 31 days.
 */
final readonly class DaysInput
{
    /**
     * @param DayInput[] $days
     */
    public function __construct(
        #[Assert\Count(min: 1, max: 31)]
        #[Assert\Valid]
        public array $days,
    ) {
    }
}
