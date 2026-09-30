<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Tests\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\BrowserKit\Cookie;

final class CalendarControllerTest extends DatabaseTestCase
{
    public function testHomeShowsTheCurrentMonthOfTheFirstClientByName(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Calendrier');
        self::assertSelectorTextSame('#calendar-title', 'novembre 2026');
        self::assertSame('Atelier Numérique · Audit de performance', trim($crawler->filter('#client-picker option[selected]')->text()));
        self::assertCount(3, $crawler->filter('#client-picker option'));
        self::assertSelectorNotExists('a[href="/?client='.$this->clientId('Atelier Numérique').'&month=2026-11"]', 'no "Aujourd’hui" link on the current month');
    }

    public function testClientParameterSelectsTheClientAndWritesTheCookie(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $crawler = $this->client->request('GET', '/?client='.$id);

        self::assertResponseIsSuccessful();
        self::assertSame('Pharmacie Lumière · Refonte du back-office', trim($crawler->filter('#client-picker option[selected]')->text()));
        $cookie = $this->client->getResponse()->headers->getCookies()[0];
        self::assertSame('client', $cookie->getName());
        self::assertSame((string) $id, $cookie->getValue());
        self::assertSame('/', $cookie->getPath());
        self::assertTrue($cookie->isHttpOnly());
        self::assertSame('lax', $cookie->getSameSite());
        self::assertEqualsWithDelta(time() + 365 * 86400, $cookie->getExpiresTime(), 86400);

        // The cookie is now used when the parameter is absent.
        $crawler = $this->client->request('GET', '/');
        self::assertSame('Pharmacie Lumière · Refonte du back-office', trim($crawler->filter('#client-picker option[selected]')->text()));
    }

    public function testStaleCookieIsIgnored(): void
    {
        $this->client->getCookieJar()->set(new Cookie('client', '999'));

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame('Atelier Numérique · Audit de performance', trim($crawler->filter('#client-picker option[selected]')->text()));
    }

    public function testMonthParameterKeepsTheClientInEveryLink(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $crawler = $this->client->request('GET', '/?client='.$id.'&month=2026-10');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('#calendar-title', 'octobre 2026');
        self::assertSame('/?client='.$id.'&month=2026-09', $crawler->filter('[data-calendar-target="prevLink"]')->attr('href'));
        self::assertSame('/?client='.$id.'&month=2026-11', $crawler->filter('[data-calendar-target="nextLink"]')->attr('href'));
        self::assertSelectorExists('a[href="/?client='.$id.'&month=2026-11"]', '"Aujourd’hui" link');
        self::assertSame((string) $id, $crawler->filter('.calendar__month-form input[name="client"]')->attr('value'));
        self::assertSame('/', $crawler->filter('.calendar__month-form')->attr('action'));
        self::assertSame('2026-10', $crawler->filter('#client-picker')->closest('form')?->filter('input[name="month"]')->attr('value'));
        self::assertSame('/calendar/'.$id.'/2026-10', $crawler->filter('section.calendar')->attr('data-calendar-url-value'));
        self::assertSelectorExists('.calendar-summary a[href="/cra/'.$id.'/2026-10"]');
    }

    public function testFarMonthsAreReachable(): void
    {
        $crawler = $this->client->request('GET', '/?month=2031-02');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('#calendar-title', 'février 2031');
        self::assertSame('2031-02', $crawler->filter('#calendar-month option[selected]')->attr('value'), 'the displayed month is added to the list');
        self::assertCount(16, $crawler->filter('#calendar-month option'));
    }

    public function testGridShowsKindsStatesNotesHolidaysAndToday(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $crawler = $this->client->request('GET', '/?client='.$id.'&month=2026-11');

        self::assertSelectorExists('td[data-date="2026-11-11"][data-kind="holiday"][data-holiday-name="Armistice 1918"]');
        self::assertSelectorExists('td[data-date="2026-11-01"][data-kind="holiday"]');
        self::assertSelectorExists('td[data-date="2026-11-07"][data-kind="weekend"]');
        self::assertSelectorExists('td[data-date="2026-11-17"][data-today][aria-current="date"] button[tabindex="0"]');
        self::assertCount(1, $crawler->filter('.calendar__grid button[tabindex="0"]'), 'one tab stop');
        self::assertSelectorExists('td[data-kind="outside"][aria-disabled="true"]');
        self::assertSelectorExists('td[data-date="2026-11-04"][data-state="full"][data-has-note][data-note="Atelier de cadrage"]');
        self::assertSelectorExists('td[data-date="2026-11-06"][data-state="half"]');
        self::assertSelectorExists('td[data-date="2026-11-16"][data-state="off"]');
        self::assertSelectorExists('td[data-date="2026-11-14"][data-kind="weekend"][data-state="full"]');
        self::assertStringContainsString('Note : Atelier de cadrage', (string) $crawler->filter('td[data-date="2026-11-04"] button')->attr('aria-label'));
        self::assertSelectorTextSame('.calendar-summary__value', '9,5');
        self::assertSelectorTextSame('.calendar-summary__unit', 'jours travaillés');
        self::assertSelectorTextSame('[data-key="full"]', '9');
        self::assertSelectorTextSame('[data-key="half"]', '1');
        self::assertSelectorTextSame('[data-key="off"]', '1');
    }

    public function testEmptyStateWithoutAnyClient(): void
    {
        foreach ($this->entityManager->getRepository(Client::class)->findAll() as $client) {
            $this->entityManager->remove($client);
        }
        $this->entityManager->flush();

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.empty-state h2', 'Ajoutez votre premier client');
        self::assertSelectorExists('.empty-state a[href="/clients/new"]');
        self::assertSelectorNotExists('section.calendar');
        self::assertSelectorNotExists('#client-picker');
        self::assertSame([], $this->client->getResponse()->headers->getCookies());
    }

    #[DataProvider('provideInvalidParameters')]
    public function testInvalidParametersAnswer404(string $query): void
    {
        $this->client->request('GET', '/?'.$query);

        self::assertResponseStatusCodeSame(404);
    }

    /** @return iterable<string, array{string}> */
    public static function provideInvalidParameters(): iterable
    {
        yield 'unknown client' => ['client=999'];
        yield 'client that is not a number' => ['client=abc'];
        yield 'month 13' => ['month=2026-13'];
        yield 'month without a year' => ['month=11'];
        yield 'month with a day' => ['month=2026-11-01'];
    }

    private function clientId(string $name): int
    {
        return $this->entityManager->getRepository(Client::class)->findOneBy(['name' => $name])?->getId() ?? self::fail('unknown client');
    }
}
