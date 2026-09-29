<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\DayEntry;
use App\Tests\DatabaseTestCase;

final class ClientControllerTest extends DatabaseTestCase
{
    public function testListIsSortedByNameWithLinksToTheEditPage(): void
    {
        $crawler = $this->client->request('GET', '/clients');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Clients');
        $names = $crawler->filter('.data-table th[scope="row"] a')->each(static fn ($node): string => trim($node->text()));
        self::assertSame(['Atelier Numérique', 'Coopérative Horizon', 'Pharmacie Lumière'], $names);
        self::assertSame('/clients/'.$this->clientId('Pharmacie Lumière').'/edit', $crawler->filter('.data-table th[scope="row"] a')->last()->attr('href'));
        self::assertSelectorTextContains('.data-table', 'claire.martin@pharmacie-lumiere.fr');
        self::assertSelectorTextContains('.data-table', 'Refonte du back-office');
        self::assertSelectorExists('dialog#confirm-delete-client input[name="_token"]');
        self::assertSelectorExists('a.button--primary[href="/clients/new"]');
    }

    public function testEmptyState(): void
    {
        $this->deleteEveryClient();

        $this->client->request('GET', '/clients');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.data-table');
        self::assertSelectorTextSame('.empty-state h2', 'Aucun client pour l’instant');
        self::assertSelectorExists('.empty-state a[href="/clients/new"]');
    }

    public function testCreateAClient(): void
    {
        $crawler = $this->client->request('GET', '/clients/new');
        self::assertSelectorTextSame('h1', 'Nouveau client');
        self::assertSame('/clients', $crawler->filter('.form-actions a')->attr('href'), '"Annuler" goes back to the list');

        $this->client->submitForm('Enregistrer', [
            'client[name]' => 'Studio Vermeil',
            'client[address]' => "3 rue Basse\n59000 Lille",
            'client[contactName]' => 'Lou Marchand',
            'client[contactEmail]' => 'lou@vermeil.fr',
            'client[mission]' => 'Migration cloud',
        ]);

        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/clients');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.toast--success .toast__title', 'Client ajouté.');
        self::assertSelectorTextContains('.data-table', 'Studio Vermeil');
        self::assertSame('Migration cloud', $this->entityManager->getRepository(Client::class)->findOneBy(['name' => 'Studio Vermeil'])?->getMission());
    }

    public function testEditAClient(): void
    {
        $id = $this->clientId('Atelier Numérique');
        $crawler = $this->client->request('GET', '/clients/'.$id.'/edit');
        self::assertSelectorTextSame('h1', 'Modifier Atelier Numérique');
        self::assertSame('Hugo Bernard', $crawler->filter('#client_contactName')->attr('value'));

        $this->client->submitForm('Enregistrer', ['client[mission]' => 'Audit de sécurité']);

        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/clients');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.toast--success .toast__title', 'Client mis à jour.');
        $this->entityManager->clear();
        self::assertSame('Audit de sécurité', $this->entityManager->find(Client::class, $id)?->getMission());
    }

    public function testInvalidSubmissionAnswers422WithFieldErrors(): void
    {
        $this->client->request('GET', '/clients/new');
        $crawler = $this->client->submitForm('Enregistrer', [
            'client[name]' => '',
            'client[contactEmail]' => 'claire.martin@',
            'client[mission]' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('#client_name_errors .field__error', 'Indiquez le nom du client.');
        self::assertSelectorTextSame('#client_contactEmail_errors .field__error', 'Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).');
        self::assertSelectorTextSame('#client_mission_errors .field__error', 'Indiquez le nom de la mission.');
        self::assertCount(3, $crawler->filter('.field--invalid'));
        self::assertCount(3, $this->entityManager->getRepository(Client::class)->findAll());
    }

    public function testUnknownClientAnswers404(): void
    {
        $this->client->request('GET', '/clients/999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteAClientAndItsDayEntries(): void
    {
        $id = $this->clientId('Pharmacie Lumière');
        self::assertNotEmpty($this->entityManager->getRepository(DayEntry::class)->findBy(['client' => $id]));

        $this->client->request('POST', '/clients/'.$id.'/delete', ['_token' => $this->deleteToken()]);

        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/clients');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.toast--success .toast__title', 'Client supprimé.');
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Client::class, $id));
        self::assertSame([], $this->entityManager->getRepository(DayEntry::class)->findBy(['client' => $id]));
    }

    public function testDeleteWithoutAValidTokenAnswers403AndDeletesNothing(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $this->client->request('POST', '/clients/'.$id.'/delete', ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);

        $this->client->request('POST', '/clients/'.$id.'/delete');
        self::assertResponseStatusCodeSame(403);

        $this->entityManager->clear();
        self::assertNotNull($this->entityManager->find(Client::class, $id));
    }

    public function testGetOnTheDeleteUrlNeverDeletes(): void
    {
        $id = $this->clientId('Pharmacie Lumière');

        $this->client->request('GET', '/clients/'.$id.'/delete');

        self::assertResponseStatusCodeSame(405);
        $this->entityManager->clear();
        self::assertNotNull($this->entityManager->find(Client::class, $id));
    }

    private function clientId(string $name): int
    {
        $client = $this->entityManager->getRepository(Client::class)->findOneBy(['name' => $name]);
        self::assertNotNull($client);

        return $client->getId() ?? self::fail('client without id');
    }

    private function deleteToken(): string
    {
        $crawler = $this->client->request('GET', '/clients');

        return (string) $crawler->filter('dialog#confirm-delete-client input[name="_token"]')->attr('value');
    }

    private function deleteEveryClient(): void
    {
        foreach ($this->entityManager->getRepository(Client::class)->findAll() as $client) {
            $this->entityManager->remove($client);
        }
        $this->entityManager->flush();
    }
}
