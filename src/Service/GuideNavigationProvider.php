<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\GuideCategoryRepository;
use App\Entity\GuideCategory;

/**
 * Fournit les données de navigation liées au module Guides.
 *
 * L'objectif est de centraliser ici la récupération des catégories
 * à afficher dans la navbar ou dans d'autres menus globaux.
 */
final class GuideNavigationProvider
{
    public function __construct(
        private readonly GuideCategoryRepository $guideCategoryRepository,
    ) {
    }

    /**
     * Retourne les catégories racines actives,
     * triées pour affichage dans la navbar.
     *
     * @return GuideCategory[]
     */
    public function getRootCategories(): array
    {
        return $this->guideCategoryRepository->findActiveRootsOrdered();
    }
}