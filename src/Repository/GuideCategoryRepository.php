<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GuideCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GuideCategory>
 */
class GuideCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuideCategory::class);
    }

    /**
     * Retourne toutes les catégories actives triées pour construire l'arborescence.
     *
     * On trie d'abord par position, puis par nom afin d'obtenir
     * un affichage stable et prévisible dans l'interface.
     *
     * @return GuideCategory[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('gc')
            ->andWhere('gc.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('gc.position', 'ASC')
            ->addOrderBy('gc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne uniquement les catégories racines actives.
     *
     * @return GuideCategory[]
     */
    public function findActiveRootsOrdered(): array
    {
        return $this->createQueryBuilder('gc')
            ->andWhere('gc.isActive = :isActive')
            ->andWhere('gc.parent IS NULL')
            ->setParameter('isActive', true)
            ->orderBy('gc.position', 'ASC')
            ->addOrderBy('gc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}