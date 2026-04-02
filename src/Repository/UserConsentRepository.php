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

    /**
     * Retourne la liste des consentements avec leur utilisateur.
     *
     * @return UserConsent[]
     */
    public function findAllWithUsers(?string $filter = null): array
    {
        $qb = $this->createQueryBuilder('uc')
            ->leftJoin('uc.user', 'u')
            ->addSelect('u')
            ->orderBy('u.id', 'DESC');

        if ($filter === 'cookies_accepted') {
            $qb->andWhere('uc.cookiesAccepted = :accepted')
                ->setParameter('accepted', true);
        }

        if ($filter === 'cookies_refused') {
            $qb->andWhere('uc.cookiesAccepted = :accepted')
                ->setParameter('accepted', false);
        }

        if ($filter === 'missing_cookie_choice') {
            $qb->andWhere('uc.cookieChoice IS NULL');
        }

        if ($filter === 'missing_legal_dates') {
            $qb->andWhere('uc.termsAcceptedAt IS NULL OR uc.privacyAcceptedAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }
}