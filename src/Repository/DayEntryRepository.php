<?php

declare(strict_types=1);

namespace App\Repository;

use App\Calendar\DayInput;
use App\Entity\Client;
use App\Entity\DayEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @extends ServiceEntityRepository<DayEntry>
 */
final class DayEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DayEntry::class);
    }

    /**
     * The entries of one client for one month, in one query, keyed by date ('Y-m-d') and sorted.
     *
     * @return array<string, DayEntry>
     */
    public function findByClientAndMonth(Client $client, \DateTimeImmutable $month): array
    {
        $first = $month->modify('first day of this month')->setTime(0, 0);

        /** @var list<DayEntry> $entries */
        $entries = $this->createQueryBuilder('e')
            ->andWhere('e.client = :client')
            ->andWhere('e.date >= :first AND e.date < :next')
            ->setParameter('client', $client)
            ->setParameter('first', $first, Types::DATE_IMMUTABLE)
            ->setParameter('next', $first->modify('+1 month'), Types::DATE_IMMUTABLE)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        $byDate = [];
        foreach ($entries as $entry) {
            $byDate[$entry->getDate()->format('Y-m-d')] = $entry;
        }

        return $byDate;
    }

    /**
     * Applies the days of one save request to one client and one month, atomically (one flush):
     * rows are created, updated or deleted (BR-05), the last occurrence of a date wins, and a
     * date outside the month rejects the whole request with 422 (spec 5.3).
     *
     * @param DayInput[] $days
     */
    public function save(Client $client, \DateTimeImmutable $month, array $days): void
    {
        $entries = $this->findByClientAndMonth($client, $month);
        $entityManager = $this->getEntityManager();

        foreach ($days as $day) {
            if (!str_starts_with($day->date, $month->format('Y-m'))) {
                throw new UnprocessableEntityHttpException(\sprintf('The date %s is outside %s.', $day->date, $month->format('Y-m')));
            }

            $entry = $entries[$day->date] ??= new DayEntry($client, new \DateTimeImmutable($day->date));
            $entry->setState($day->state);
            $entry->setNote(trim($day->note));

            // persist() manages a new entry and re-manages one scheduled for removal; remove() ignores a new one.
            if ($entry->isBlank()) {
                $entityManager->remove($entry);
            } else {
                $entityManager->persist($entry);
            }
        }

        $entityManager->flush();
    }
}
