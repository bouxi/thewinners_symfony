<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Application;
use App\Enum\ApplicationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class GuildApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * Liste admin avec filtre statut + recherche (pseudo/email).
     *
     * Note : $status est un ApplicationStatus (Enum) ou null.
     */
    public function findForAdminList(?ApplicationStatus $status, string $q): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->addSelect('u')
            ->orderBy('a.submittedAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('a.status = :status')->setParameter('status', $status);
        }

        $q = trim($q);
        if ($q !== '') {
            $qb->andWhere('u.pseudo LIKE :q OR u.email LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère la candidature d’un utilisateur (1 candidature max).
     */
    public function findOneByUserId(int $userId): ?Application
    {
        return $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->andWhere('u.id = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
