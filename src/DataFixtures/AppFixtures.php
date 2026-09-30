<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Calendar\CalendarDay;
use App\Calendar\FrenchHolidays;
use App\Calendar\Today;
use App\Entity\Client;
use App\Entity\DayEntry;
use App\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Demo data (spec section 6.1). Day entries are relative to the loading date: the two
 * previous months and the current month, dates strictly before today only.
 */
final class AppFixtures extends Fixture
{
    public function __construct(private readonly Today $today)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $profile = new Profile();
        $profile->setName('Camille Durand');
        $profile->setCompany('Durand Conseil SASU');
        $profile->setSiret('81234567800013'); // fictitious: valid on 14 digits, invalid as a SIREN
        $profile->setAddress("25 rue des Lilas\n75020 Paris");
        $profile->setEmail('camille@durand-conseil.fr');
        $manager->persist($profile);

        $pharmacie = $this->client($manager, 'Pharmacie Lumière', 'Claire Martin', 'claire.martin@pharmacie-lumiere.fr', 'Refonte du back-office', "12 rue de la Paix\n75002 Paris");
        $atelier = $this->client($manager, 'Atelier Numérique', 'Hugo Bernard', 'hugo@atelier-numerique.fr', 'Audit de performance', "4 quai des Chartrons\n33000 Bordeaux");
        $this->client($manager, 'Coopérative Horizon', 'Inès Robert', null, 'Accompagnement technique', "8 place Bellecour\n69002 Lyon");

        $today = $this->today->date();
        for ($offset = -2; $offset <= 0; ++$offset) {
            $month = $today->modify('first day of this month')->modify(\sprintf('%+d months', $offset));
            $workdays = $this->workdays($month);
            $secondSaturday = $month->modify('second saturday of this month');

            foreach ($workdays as $rank => $date) {
                $entry = match ($rank + 1) {
                    3 => new DayEntry($pharmacie, $date, CalendarDay::STATE_FULL, 'Atelier de cadrage'),
                    5 => new DayEntry($pharmacie, $date, CalendarDay::STATE_HALF, 'Démo client (après-midi)'),
                    10, 11 => new DayEntry($pharmacie, $date, CalendarDay::STATE_OFF),
                    15 => null,
                    default => new DayEntry($pharmacie, $date, CalendarDay::STATE_FULL),
                };
                $this->persistBefore($manager, $today, $entry);
            }
            $this->persistBefore($manager, $today, new DayEntry($pharmacie, $secondSaturday, CalendarDay::STATE_FULL, 'Mise en production'));

            if (isset($workdays[4])) {
                $this->persistBefore($manager, $today, new DayEntry($atelier, $workdays[4], CalendarDay::STATE_HALF, 'Restitution (matin)'));
            }
            if (isset($workdays[14])) {
                $this->persistBefore($manager, $today, new DayEntry($atelier, $workdays[14], CalendarDay::STATE_FULL));
            }
        }

        $manager->flush();
    }

    private function client(ObjectManager $manager, string $name, string $contactName, ?string $contactEmail, string $mission, string $address): Client
    {
        $client = new Client();
        $client->setName($name);
        $client->setContactName($contactName);
        $client->setContactEmail($contactEmail);
        $client->setMission($mission);
        $client->setAddress($address);
        $manager->persist($client);

        return $client;
    }

    /** @return list<\DateTimeImmutable> Monday-to-Friday days of the month that are not public holidays */
    private function workdays(\DateTimeImmutable $month): array
    {
        $holidays = FrenchHolidays::forYear((int) $month->format('Y'));
        $workdays = [];
        for ($date = $month; $date->format('m') === $month->format('m'); $date = $date->modify('+1 day')) {
            if ((int) $date->format('N') <= 5 && !isset($holidays[$date->format('Y-m-d')])) {
                $workdays[] = $date;
            }
        }

        return $workdays;
    }

    private function persistBefore(ObjectManager $manager, \DateTimeImmutable $today, ?DayEntry $entry): void
    {
        if (null !== $entry && $entry->getDate() < $today) {
            $manager->persist($entry);
        }
    }
}
