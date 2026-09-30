<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Smoke test: every page of the application loads (symfony-bp-tests, rule 1).
 */
final class ApplicationAvailabilityTest extends DatabaseTestCase
{
    #[DataProvider('provideUrls')]
    public function testPageIsSuccessful(string $url): void
    {
        $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    /** @return iterable<string, array{string}> */
    public static function provideUrls(): iterable
    {
        yield 'calendar' => ['/'];
        yield 'calendar of a client and month' => ['/?client=1&month=2026-10'];
        yield 'printable CRA' => ['/cra/1/2026-11'];
        yield 'client list' => ['/clients'];
        yield 'new client' => ['/clients/new'];
        yield 'edit client' => ['/clients/1/edit'];
        yield 'profile' => ['/profile'];
    }
}
