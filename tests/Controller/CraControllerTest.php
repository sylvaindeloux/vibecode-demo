<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\Profile;
use App\Tests\DatabaseTestCase;

final class CraControllerTest extends DatabaseTestCase
{
    public function testSheetContent(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $crawler = $this->client->request('GET', '/cra/'.$id.'/2026-11');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('title', 'CRA - Pharmacie Lumière - Novembre 2026');
        self::assertSelectorTextSame('.cra-toolbar__title', 'CRA - Pharmacie Lumière - Novembre 2026');
        self::assertSame('/?client='.$id.'&month=2026-11', $crawler->filter('.cra-toolbar a')->attr('href'), '"Retour au calendrier"');

        // Header
        self::assertSelectorTextSame('h1#cra-title', 'Novembre 2026');
        self::assertSelectorTextSame('.cra-doc__eyebrow', 'Compte rendu d’activité');
        $meta = $crawler->filter('.cra-doc__meta dd')->each(static fn ($node): string => trim($node->text()));
        self::assertSame(['Refonte du back-office', '01/11/2026 – 30/11/2026', '9,5', '17/11/2026'], $meta);

        // Parties
        $freelancer = $crawler->filter('.cra-party')->first();
        self::assertStringContainsString('Camille Durand', $freelancer->text());
        self::assertStringContainsString('Durand Conseil SASU', $freelancer->text());
        self::assertStringContainsString('25 rue des Lilas', $freelancer->text());
        self::assertSame('812 345 678 00013', trim($freelancer->filter('.cra-party__id')->text()));
        self::assertStringContainsString('camille@durand-conseil.fr', $freelancer->text());
        $client = $crawler->filter('.cra-party')->last();
        self::assertStringContainsString('Pharmacie Lumière', $client->text());
        self::assertStringContainsString('12 rue de la Paix', $client->text());
        self::assertStringContainsString('Contact : Claire Martin · claire.martin@pharmacie-lumiere.fr', $client->text());

        // Days: full and half days only, chronological, with weekday, quantity and note
        $rows = $crawler->filter('.cra-days tbody tr')->each(static fn ($row): array => $row->filter('td')->each(static fn ($cell): string => trim($cell->text())));
        self::assertCount(10, $rows);
        self::assertSame(['02/11/2026', 'Lundi', '1', ''], $rows[0]);
        self::assertSame(['04/11/2026', 'Mercredi', '1', 'Atelier de cadrage'], $rows[2]);
        self::assertSame(['06/11/2026', 'Vendredi', '0,5', 'Démo client (après-midi)'], $rows[4]);
        self::assertSame(['14/11/2026', 'Samedi', '1', 'Mise en production'], $rows[9]);
        self::assertStringNotContainsString('16/11/2026', $crawler->filter('.cra-days')->text(), 'leave days are not printed');
        self::assertSelectorTextSame('.cra-days tfoot .cra-days__qty', '9,5');
        self::assertSelectorTextContains('.cra-days tfoot', 'jours travaillés');

        // Signatures
        $signatures = $crawler->filter('.cra-signature')->each(static fn ($node): string => trim($node->filter('p')->eq(1)->text()));
        self::assertSame(['Camille Durand', 'Claire Martin'], $signatures);
    }

    public function testEmptyMonthAndPartyWithoutOptionalValues(): void
    {
        $id = $this->clientId('Coopérative Horizon');

        $crawler = $this->client->request('GET', '/cra/'.$id.'/2026-11');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.cra-days tbody td', 'Aucun jour travaillé ce mois-ci.');
        self::assertSelectorTextSame('.cra-days tfoot .cra-days__qty', '0');
        self::assertSelectorTextContains('.cra-days tfoot', 'jour travaillé');
        self::assertSame('0', trim($crawler->filter('.cra-doc__meta dd')->eq(2)->text()));
        self::assertStringNotContainsString('·', $crawler->filter('.cra-party')->last()->text(), 'no e-mail: no separator');
        self::assertStringContainsString('Contact : Inès Robert', $crawler->filter('.cra-party')->last()->text());
    }

    public function testSignatureFallsBackToTheClientNameWithoutContact(): void
    {
        $id = $this->clientId('Coopérative Horizon');
        $client = $this->entityManager->find(Client::class, $id);
        $client?->setContactName(null);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/cra/'.$id.'/2026-11');

        self::assertSame('Coopérative Horizon', trim($crawler->filter('.cra-signature')->last()->filter('p')->eq(1)->text()));
        self::assertStringNotContainsString('Contact', $crawler->filter('.cra-party')->last()->text());
    }

    public function testTitleReplacesCharactersInvalidInFileNames(): void
    {
        $id = $this->clientId('Pharmacie Lumière');
        $client = $this->entityManager->find(Client::class, $id);
        $client?->setName('A/B\\C: D');
        $this->entityManager->flush();

        $this->client->request('GET', '/cra/'.$id.'/2026-11');

        self::assertSelectorTextSame('title', 'CRA - A-B-C- D - Novembre 2026');
    }

    public function testIncompleteProfileRedirectsToTheProfileWithAWarning(): void
    {
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy([]);
        $profile?->setAddress('');
        $this->entityManager->flush();

        $this->client->request('GET', '/cra/'.$this->clientId('Pharmacie Lumière').'/2026-11');

        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/profile');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.toast--warning .toast__title', 'Complétez votre profil pour ouvrir le CRA.');
    }

    public function testMissingProfileRedirectsToo(): void
    {
        foreach ($this->entityManager->getRepository(Profile::class)->findAll() as $profile) {
            $this->entityManager->remove($profile);
        }
        $this->entityManager->flush();

        $this->client->request('GET', '/cra/'.$this->clientId('Pharmacie Lumière').'/2026-11');

        self::assertResponseRedirects('/profile');
    }

    public function testUnknownClientOrInvalidMonthAnswers404(): void
    {
        $this->client->request('GET', '/cra/999/2026-11');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/cra/'.$this->clientId('Pharmacie Lumière').'/2026-13');
        self::assertResponseStatusCodeSame(404);
    }

    private function clientId(string $name): int
    {
        return $this->entityManager->getRepository(Client::class)->findOneBy(['name' => $name])?->getId() ?? self::fail('unknown client');
    }
}
