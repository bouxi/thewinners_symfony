<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GuideCategory;
use App\Repository\GuideCategoryRepository;

/**
 * Service chargé de reconstruire l'arborescence complète des catégories de guides.
 *
 * L'objectif est de centraliser ici la logique métier liée à la hiérarchie
 * afin d'éviter de la dupliquer dans les contrôleurs ou dans Twig.
 */
final class GuideTreeBuilder
{
    public function __construct(
        private readonly GuideCategoryRepository $guideCategoryRepository,
    ) {
    }

    /**
     * Construit l'arbre complet des catégories actives.
     *
     * Structure renvoyée :
     * [
     *     [
     *         'category' => GuideCategory,
     *         'children' => [...]
     *     ],
     * ]
     *
     * @return array<int, array{category: GuideCategory, children: array}>
     */
    public function buildActiveTree(): array
    {
        $categories = $this->guideCategoryRepository->findActiveOrdered();

        return $this->buildTreeFromCategories($categories);
    }

    /**
     * Transforme une liste plate de catégories en structure arborescente.
     *
     * @param GuideCategory[] $categories
     *
     * @return array<int, array{category: GuideCategory, children: array}>
     */
    private function buildTreeFromCategories(array $categories): array
    {
        $nodesById = [];
        $tree = [];

        /**
         * Étape 1 :
         * on crée un nœud vide pour chaque catégorie.
         */
        foreach ($categories as $category) {
            $categoryId = $category->getId();

            if ($categoryId === null) {
                continue;
            }

            $nodesById[$categoryId] = [
                'category' => $category,
                'children' => [],
            ];
        }

        /**
         * Étape 2 :
         * on rattache chaque nœud à son parent si le parent existe dans la liste.
         * Sinon, on le place à la racine.
         */
        foreach ($categories as $category) {
            $categoryId = $category->getId();

            if ($categoryId === null || !isset($nodesById[$categoryId])) {
                continue;
            }

            $parent = $category->getParent();

            if ($parent instanceof GuideCategory && $parent->getId() !== null && isset($nodesById[$parent->getId()])) {
                $nodesById[$parent->getId()]['children'][] = &$nodesById[$categoryId];
            } else {
                $tree[] = &$nodesById[$categoryId];
            }
        }

        return $tree;
    }
}