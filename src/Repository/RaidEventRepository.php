<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RaidEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class RaidEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaidEvent::class);
    }

    /**
     * Récupère tous les raids dont le début est entre 2 dates (utile pour le calendrier).
     *
     * @return RaidEvent[]
     */
    public function findBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.startsAt >= :from')
            ->andWhere('r.startsAt < :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de raids à venir.
     */
    public function countUpcoming(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.startsAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
