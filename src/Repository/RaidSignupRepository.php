<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RaidEvent;
use App\Entity\RaidSignup;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class RaidSignupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaidSignup::class);
    }

    public function findOneByRaidAndUser(RaidEvent $raid, User $user): ?RaidSignup
    {
        return $this->findOneBy(['raid' => $raid, 'user' => $user]);
    }

    /**
     * Liste des inscriptions d’un raid triées.
     *
     * @return RaidSignup[]
     */
    public function findForRaid(RaidEvent $raid): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.raid = :raid')
            ->setParameter('raid', $raid)
            ->orderBy('s.role', 'ASC')
            ->addOrderBy('s.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
