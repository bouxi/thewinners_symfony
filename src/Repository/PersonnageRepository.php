<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Personnage;
use App\Enum\CombatRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personnage>
 */
class PersonnageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnage::class);
    }

    /**
     * Récupère les personnages publics d'un utilisateur
     *
     * @return Personnage[]
     */
    public function findPublicByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.isPublic = true')
            ->setParameter('user', $userId)
            ->orderBy('p.isMain', 'DESC')
            ->addOrderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ✅ Liste publique des membres réels de la guilde
     *
     * Règles :
     * - personnage public uniquement
     * - utilisateur public uniquement
     * - utilisateur accepté dans la guilde uniquement
     * - filtre optionnel par rôle (tank / heal / dps)
     *
     * @return Personnage[]
     */
    public function findPublicGuildMembers(?CombatRole $role = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->addSelect('u')
            ->andWhere('p.isPublic = :personnagePublic')
            ->andWhere('u.isPublicMember = :userPublic')
            ->andWhere('u.isGuildMember = :isGuildMember')
            ->setParameter('personnagePublic', true)
            ->setParameter('userPublic', true)
            ->setParameter('isGuildMember', true);

        // ✅ On garde le filtre rôle
        if ($role !== null) {
            $qb->andWhere('p.combatRole = :role')
               ->setParameter('role', $role);
        }

        // ✅ Tri propre
        $qb->addOrderBy('p.isMain', 'DESC')
           ->addOrderBy('p.createdAt', 'DESC')
           ->addOrderBy('u.pseudo', 'ASC');

        return $qb->getQuery()->getResult();
    }
}