<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\DayEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

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
}
