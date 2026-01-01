<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApplicationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ApplicationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationMessage::class);
    }

    /**
     * Compte le nombre de messages non lus (par le candidat) pour une candidature donnée.
     */
    public function countUnreadForApplicant(int $applicationId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.application = :aid')
            ->andWhere('m.isReadByApplicant = false')
            ->setParameter('aid', $applicationId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marque comme lus tous les messages d’une candidature pour le candidat.
     * (simple : appelé à l’ouverture de la page “ma candidature”)
     */
    public function markAllAsReadForApplicant(int $applicationId): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.isReadByApplicant', ':read')
            ->andWhere('m.application = :aid')
            ->setParameter('read', true)
            ->setParameter('aid', $applicationId)
            ->getQuery()
            ->execute();
    }

    // ApplicationMessageRepository
    public function findByApplicationId(int $appId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.application = :id')
            ->setParameter('id', $appId)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
