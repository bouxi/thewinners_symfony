<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Personnage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\CombatRole;

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
     * Liste publique des membres :
     * - persos publics uniquement
     * - jointure sur User (évite N+1)
     * - filtre optionnel par rôle (tank/heal/dps)
     * - on met les "main" en premier sans exclure les autres
     *
     * @return Personnage[]
     */
    public function findPublicGuildMembers(?CombatRole $role = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->addSelect('u')
            ->andWhere('p.isPublic = :public')
            ->setParameter('public', true);

        // Filtre rôle (si demandé)
        if ($role !== null) {
            $qb->andWhere('p.combatRole = :role')
               ->setParameter('role', $role);
        }

        // ✅ Tri "pro" :
        // - les mains en premier
        // - puis les plus récents
        // - puis par pseudo pour un ordre stable
        $qb->addOrderBy('p.isMain', 'DESC')
           ->addOrderBy('p.createdAt', 'DESC')
           ->addOrderBy('u.pseudo', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
