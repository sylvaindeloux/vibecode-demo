<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\DayEntry;
use App\Repository\DayEntryRepository;
use App\Tests\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Every row of the table of spec section 5.3.
 */
final class CalendarSaveTest extends DatabaseTestCase
{
    public function testValidDaysAreCreatedUpdatedAndDeleted(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $this->post('/calendar/'.$id.'/2026-11', [
            ['date' => '2026-11-18', 'state' => 'full', 'note' => ''],          // created
            ['date' => '2026-11-04', 'state' => 'half', 'note' => ' Revue '],   // updated, note trimmed
            ['date' => '2026-11-16', 'state' => 'empty', 'note' => ''],         // deleted (was off)
            ['date' => '2026-11-19', 'state' => 'empty', 'note' => 'Prévu'],    // created: a note alone keeps a row
        ]);

        self::assertResponseStatusCodeSame(204);
        self::assertSame('', $this->client->getResponse()->getContent());
        $entries = $this->entries($id, '2026-11');
        self::assertSame('full', $entries['2026-11-18']->getState());
        self::assertSame('half', $entries['2026-11-04']->getState());
        self::assertSame('Revue', $entries['2026-11-04']->getNote());
        self::assertArrayNotHasKey('2026-11-16', $entries);
        self::assertSame('empty', $entries['2026-11-19']->getState());
        self::assertSame('Prévu', $entries['2026-11-19']->getNote());
    }

    public function testTheLastOccurrenceOfADateWins(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $this->post('/calendar/'.$id.'/2026-11', [
            ['date' => '2026-11-04', 'state' => 'empty', 'note' => ''],
            ['date' => '2026-11-04', 'state' => 'off', 'note' => 'Congé'],
            ['date' => '2026-11-20', 'state' => 'full', 'note' => ''],
            ['date' => '2026-11-20', 'state' => 'empty', 'note' => ''],
        ]);

        self::assertResponseStatusCodeSame(204);
        $entries = $this->entries($id, '2026-11');
        self::assertSame('off', $entries['2026-11-04']->getState());
        self::assertSame('Congé', $entries['2026-11-04']->getNote());
        self::assertArrayNotHasKey('2026-11-20', $entries);
    }

    public function testEntriesAreIndependentFromOneClientToAnother(): void
    {
        $pharmacie = $this->clientId('Pharmacie Lumière');
        $atelier = $this->clientId('Atelier Numérique');

        $this->post('/calendar/'.$atelier.'/2026-11', [['date' => '2026-11-04', 'state' => 'full', 'note' => '']]);

        self::assertResponseStatusCodeSame(204);
        self::assertSame('full', $this->entries($atelier, '2026-11')['2026-11-04']->getState());
        self::assertSame('Atelier de cadrage', $this->entries($pharmacie, '2026-11')['2026-11-04']->getNote());
    }

    public function testMissingOrInvalidTokenAnswers403(): void
    {
        $id = $this->clientId('Pharmacie Lumière');
        $days = [['date' => '2026-11-18', 'state' => 'full', 'note' => '']];

        $this->post('/calendar/'.$id.'/2026-11', $days, token: null);
        self::assertResponseStatusCodeSame(403);

        $this->post('/calendar/'.$id.'/2026-11', $days, token: 'wrong');
        self::assertResponseStatusCodeSame(403);

        self::assertArrayNotHasKey('2026-11-18', $this->entries($id, '2026-11'));
    }

    public function testUnknownClientOrInvalidMonthAnswers404(): void
    {
        $id = $this->clientId('Pharmacie Lumière');
        $days = [['date' => '2026-11-18', 'state' => 'full', 'note' => '']];

        $this->post('/calendar/999/2026-11', $days);
        self::assertResponseStatusCodeSame(404);

        $this->post('/calendar/'.$id.'/2026-13', $days);
        self::assertResponseStatusCodeSame(404);
    }

    public function testInvalidJsonAnswers400(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $this->client->request('POST', '/calendar/'.$id.'/2026-11', server: $this->headers($this->token()), content: '{"days": [');

        self::assertResponseStatusCodeSame(400);
    }

    /** @param list<array<string, mixed>> $days */
    #[DataProvider('provideInvalidDays')]
    public function testInvalidDaysAnswer422AndSaveNothing(array $days): void
    {
        $id = $this->clientId('Pharmacie Lumière');
        $before = array_keys($this->entries($id, '2026-11'));

        $this->post('/calendar/'.$id.'/2026-11', $days);

        self::assertResponseStatusCodeSame(422);
        $this->entityManager->clear();
        self::assertSame($before, array_keys($this->entries($id, '2026-11')), 'the request is atomic');
        self::assertSame('Atelier de cadrage', $this->entries($id, '2026-11')['2026-11-04']->getNote());
    }

    /** @return iterable<string, array{list<array<string, mixed>>}> */
    public static function provideInvalidDays(): iterable
    {
        $valid = ['date' => '2026-11-18', 'state' => 'full', 'note' => ''];

        yield 'empty list' => [[]];
        yield 'more than 31 days' => [array_fill(0, 32, $valid)];
        yield 'invalid date' => [[['date' => '2026-11-31', 'state' => 'full', 'note' => '']]];
        yield 'date that is not a date' => [[['date' => 'tomorrow', 'state' => 'full', 'note' => '']]];
        yield 'date outside the month, after a valid day' => [[$valid, ['date' => '2026-12-01', 'state' => 'full', 'note' => ''], ['date' => '2026-11-04', 'state' => 'empty', 'note' => '']]];
        yield 'unknown state' => [[['date' => '2026-11-18', 'state' => 'sick', 'note' => '']]];
        yield 'note longer than 140 characters' => [[['date' => '2026-11-18', 'state' => 'full', 'note' => str_repeat('a', 141)]]];
        yield 'missing state' => [[['date' => '2026-11-18', 'note' => '']]];
    }

    /** @param list<array<string, mixed>> $days */
    private function post(string $url, array $days, ?string $token = ''): void
    {
        $this->client->request('POST', $url, server: $this->headers('' === $token ? $this->token() : $token), content: json_encode(['days' => $days], \JSON_THROW_ON_ERROR));
    }

    /** @return array<string, string> */
    private function headers(?string $token): array
    {
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if (null !== $token) {
            $headers['HTTP_X_CSRF_TOKEN'] = $token;
        }

        return $headers;
    }

    /** The token of id "calendar", as the calendar page gives it to the Stimulus controller. */
    private function token(): string
    {
        $crawler = $this->client->request('GET', '/');

        return (string) $crawler->filter('section.calendar')->attr('data-calendar-csrf-token-value');
    }

    /** @return array<string, DayEntry> */
    private function entries(int $clientId, string $month): array
    {
        $this->entityManager->clear();
        $client = $this->entityManager->find(Client::class, $clientId) ?? self::fail('unknown client');

        return static::getContainer()->get(DayEntryRepository::class)->findByClientAndMonth($client, new \DateTimeImmutable($month.'-01'));
    }

    private function clientId(string $name): int
    {
        return $this->entityManager->getRepository(Client::class)->findOneBy(['name' => $name])?->getId() ?? self::fail('unknown client');
    }
}
