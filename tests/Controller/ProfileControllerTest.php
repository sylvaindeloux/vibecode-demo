<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Profile;
use App\Tests\DatabaseTestCase;

final class ProfileControllerTest extends DatabaseTestCase
{
    public function testPageShowsTheSavedProfileWithGroupedSiret(): void
    {
        $crawler = $this->client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Profil');
        self::assertSame('Camille Durand', $crawler->filter('#profile_name')->attr('value'));
        self::assertSame('812 345 678 00013', $crawler->filter('#profile_siret')->attr('value'));
        self::assertSame('numeric', $crawler->filter('#profile_siret')->attr('inputmode'));
        self::assertStringContainsString('input--numeric', (string) $crawler->filter('#profile_siret')->attr('class'));
        self::assertSelectorTextContains('label[for="profile_company"]', '(facultatif)');
    }

    public function testValidSubmissionSavesAndStaysOnThePage(): void
    {
        $this->client->request('GET', '/profile');
        $this->client->submitForm('Enregistrer', [
            'profile[name]' => 'Dominique Petit',
            'profile[company]' => '',
            'profile[siret]' => '732 829 320 00074',
            'profile[address]' => "1 rue Neuve\n44000 Nantes",
            'profile[email]' => 'dominique@petit.fr',
        ]);

        self::assertResponseStatusCodeSame(303);
        self::assertResponseRedirects('/profile');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.toast--success .toast__title', 'Profil enregistré.');
        self::assertSelectorTextSame('h1', 'Profil');

        $profiles = $this->entityManager->getRepository(Profile::class)->findAll();
        self::assertCount(1, $profiles, 'there is never more than one profile');
        self::assertSame('73282932000074', $profiles[0]->getSiret());
        self::assertNull($profiles[0]->getCompany());
        self::assertSame('Dominique Petit', $profiles[0]->getName());
    }

    public function testSiretWithoutSpacesIsAcceptedToo(): void
    {
        $this->client->request('GET', '/profile');
        $this->client->submitForm('Enregistrer', ['profile[siret]' => '73282932000074']);

        self::assertResponseStatusCodeSame(303);
        self::assertSame('73282932000074', $this->entityManager->getRepository(Profile::class)->findOneBy([])?->getSiret());
    }

    public function testFirstSubmissionCreatesTheSingleRow(): void
    {
        foreach ($this->entityManager->getRepository(Profile::class)->findAll() as $profile) {
            $this->entityManager->remove($profile);
        }
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/profile');
        self::assertSame('', (string) $crawler->filter('#profile_name')->attr('value'));

        $this->client->submitForm('Enregistrer', [
            'profile[name]' => 'Dominique Petit',
            'profile[siret]' => '81234567800013',
            'profile[address]' => 'Nantes',
        ]);
        self::assertResponseStatusCodeSame(303);
        self::assertCount(1, $this->entityManager->getRepository(Profile::class)->findAll());
    }

    public function testInvalidSubmissionAnswers422AndSavesNothing(): void
    {
        $this->client->request('GET', '/profile');
        $crawler = $this->client->submitForm('Enregistrer', [
            'profile[name]' => '',
            'profile[siret]' => '',
            'profile[address]' => '',
            'profile[email]' => 'camille@',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('#profile_name_errors .field__error', 'Indiquez votre nom.');
        self::assertSelectorTextSame('#profile_siret_errors .field__error', 'Le SIRET compte 14 chiffres.');
        self::assertSelectorTextSame('#profile_address_errors .field__error', 'Indiquez votre adresse.');
        self::assertSelectorTextSame('#profile_email_errors .field__error', 'Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).');
        self::assertSame('true', $crawler->filter('#profile_name')->attr('aria-invalid'));
        self::assertCount(4, $crawler->filter('.field--invalid'));

        $this->entityManager->clear();
        self::assertSame('Camille Durand', $this->entityManager->getRepository(Profile::class)->findOneBy([])?->getName());
    }

    public function testSiretMustHaveFourteenDigits(): void
    {
        $this->client->request('GET', '/profile');
        $this->client->submitForm('Enregistrer', ['profile[siret]' => '812 345 678']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('#profile_siret_errors .field__error', 'Le SIRET compte 14 chiffres.');
        self::assertSelectorCount(1, '#profile_siret_errors .field__error');
    }

    public function testSiretMustPassTheLuhnCheck(): void
    {
        $this->client->request('GET', '/profile');
        $this->client->submitForm('Enregistrer', ['profile[siret]' => '812 345 678 00014']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('#profile_siret_errors .field__error', 'Ce numéro SIRET n’est pas valide : vérifiez les chiffres saisis.');
        self::assertSelectorCount(1, '#profile_siret_errors .field__error');
    }
}
