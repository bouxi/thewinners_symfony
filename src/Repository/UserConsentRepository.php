<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserConsent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository dédié à l'entité UserConsent.
 *
 * Il permet de centraliser les requêtes liées aux consentements.
 */
final class UserConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserConsent::class);
    }

    /**
     * Retourne le consentement d'un utilisateur par son ID.
     */
    public function findOneByUserId(int $userId): ?UserConsent
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}