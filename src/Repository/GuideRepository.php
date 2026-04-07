<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guide;
use App\Entity\GuideCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guide>
 */
class GuideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guide::class);
    }

    /**
     * Retourne les guides filtrés et paginés pour l'administration.
     *
     * @return array{
     *     items: Guide[],
     *     total: int,
     *     page: int,
     *     limit: int,
     *     pages: int
     * }
     */
    public function findAdminList(
        ?string $query,
        ?string $status,
        ?GuideCategory $category,
        int $page = 1,
        int $limit = 10
    ): array {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.category', 'c')
            ->addSelect('c')
            ->leftJoin('g.author', 'a')
            ->addSelect('a');

        if ($query !== null && trim($query) !== '') {
            $qb
                ->andWhere('g.title LIKE :query OR g.slug LIKE :query')
                ->setParameter('query', '%' . trim($query) . '%');
        }

        if ($status === 'published') {
            $qb
                ->andWhere('g.isPublished = :published')
                ->setParameter('published', true);
        } elseif ($status === 'draft') {
            $qb
                ->andWhere('g.isPublished = :published')
                ->setParameter('published', false);
        }

        if ($category instanceof GuideCategory) {
            $qb
                ->andWhere('g.category = :category')
                ->setParameter('category', $category);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(g.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->orderBy('g.updatedAt', 'DESC')
            ->addOrderBy('g.title', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $pages = max(1, (int) ceil($total / $limit));

        if ($page > $pages) {
            $page = $pages;
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
        ];
    }
}