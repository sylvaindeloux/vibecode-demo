<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profile>
 */
final class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /** The single profile row, or a new empty profile when the table is empty. */
    public function get(): Profile
    {
        return $this->findOneBy([]) ?? new Profile();
    }
}
