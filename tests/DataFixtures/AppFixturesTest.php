<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Calendar\CalendarDay;
use App\Entity\Client;
use App\Entity\DayEntry;
use App\Repository\ClientRepository;
use App\Repository\DayEntryRepository;
use App\Repository\ProfileRepository;
use App\Tests\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AppFixturesTest extends DatabaseTestCase
{
    public function testProfileAndClients(): void
    {
        $profile = static::getContainer()->get(ProfileRepository::class)->get();
        self::assertSame('Camille Durand', $profile->getName());
        self::assertSame('81234567800013', $profile->getSiret());

        $names = array_map(static fn (Client $client): ?string => $client->getName(), static::getContainer()->get(ClientRepository::class)->findAllSortedByName());
        self::assertSame(['Atelier Numérique', 'Coopérative Horizon', 'Pharmacie Lumière'], $names);
    }

    /** Worked example of spec section 6.1, with today frozen on Tuesday 17 November 2026. */
    #[DataProvider('provideMonths')]
    public function testDayEntries(string $clientName, string $month, int $full, int $half, int $off, float $total): void
    {
        $client = static::getContainer()->get(ClientRepository::class)->findOneBy(['name' => $clientName]);
        self::assertNotNull($client);
        $entries = static::getContainer()->get(DayEntryRepository::class)->findByClientAndMonth($client, new \DateTimeImmutable($month.'-01'));

        $count = static fn (string $state): int => \count(array_filter($entries, static fn (DayEntry $e): bool => $state === $e->getState()));
        self::assertSame($full, $count(CalendarDay::STATE_FULL));
        self::assertSame($half, $count(CalendarDay::STATE_HALF));
        self::assertSame($off, $count(CalendarDay::STATE_OFF));
        self::assertSame($total, (float) array_sum(array_map(static fn (DayEntry $e): float => $e->quantity(), $entries)));
    }

    /** @return iterable<string, array{string, string, int, int, int, float}> */
    public static function provideMonths(): iterable
    {
        yield 'Pharmacie Lumière, November 2026' => ['Pharmacie Lumière', '2026-11', 9, 1, 1, 9.5];
        yield 'Pharmacie Lumière, October 2026' => ['Pharmacie Lumière', '2026-10', 19, 1, 2, 19.5];
        yield 'Atelier Numérique, October 2026' => ['Atelier Numérique', '2026-10', 1, 1, 0, 1.5];
        yield 'Coopérative Horizon, November 2026' => ['Coopérative Horizon', '2026-11', 0, 0, 0, 0.0];
    }

    public function testNovemberDetails(): void
    {
        $client = static::getContainer()->get(ClientRepository::class)->findOneBy(['name' => 'Pharmacie Lumière']);
        self::assertNotNull($client);
        $entries = static::getContainer()->get(DayEntryRepository::class)->findByClientAndMonth($client, new \DateTimeImmutable('2026-11-01'));

        self::assertSame(['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05', '2026-11-06', '2026-11-09', '2026-11-10', '2026-11-12', '2026-11-13', '2026-11-14', '2026-11-16'], array_keys($entries));
        self::assertSame('Atelier de cadrage', $entries['2026-11-04']->getNote());
        self::assertSame(CalendarDay::STATE_HALF, $entries['2026-11-06']->getState());
        self::assertSame('Mise en production', $entries['2026-11-14']->getNote());
        self::assertSame(CalendarDay::STATE_OFF, $entries['2026-11-16']->getState());
        self::assertArrayNotHasKey('2026-11-17', $entries, 'today is never pre-filled');
    }
}
