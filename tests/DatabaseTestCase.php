<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Base class of the tests that need data: the clock is frozen on Tuesday 17 November 2026,
 * the schema is recreated and the demo fixtures are loaded before each test (spec 8.7).
 */
abstract class DatabaseTestCase extends WebTestCase
{
    use ClockSensitiveTrait;

    public const string TODAY = '2026-11-17';

    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::mockTime(new \DateTimeImmutable(self::TODAY.' 10:00:00', new \DateTimeZone('Europe/Paris')));

        $this->client = static::createClient();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        (new ORMExecutor($this->entityManager))->execute($container->get('doctrine.fixtures.loader')->getFixtures(), true);
    }
}
