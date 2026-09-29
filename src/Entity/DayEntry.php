<?php

declare(strict_types=1);

namespace App\Entity;

use App\Calendar\CalendarDay;
use App\Repository\DayEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * What the user entered for one client and one date. A row exists if, and only if,
 * the state is not `empty` or the note is not empty (BR-05).
 */
#[ORM\Entity(repositoryClass: DayEntryRepository::class)]
#[ORM\UniqueConstraint(columns: ['client_id', 'date'])]
final class DayEntry
{
    public const int NOTE_MAX_LENGTH = 140;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly Client $client,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private readonly \DateTimeImmutable $date,
        #[ORM\Column(length: 5)]
        #[Assert\Choice(choices: CalendarDay::STATES)]
        private string $state = CalendarDay::STATE_EMPTY,
        #[ORM\Column(length: self::NOTE_MAX_LENGTH, options: ['default' => ''])]
        #[Assert\Length(max: self::NOTE_MAX_LENGTH)]
        private string $note = '',
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    /** 1 for a full day, 0.5 for a half day, 0 otherwise (BR-06). */
    public function quantity(): float
    {
        return match ($this->state) {
            CalendarDay::STATE_FULL => 1.0,
            CalendarDay::STATE_HALF => 0.5,
            default => 0.0,
        };
    }

    /** True when the entry carries nothing and its row must be deleted (BR-05). */
    public function isBlank(): bool
    {
        return CalendarDay::STATE_EMPTY === $this->state && '' === $this->note;
    }
}
