<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Guide;
use App\Entity\GuideCategory;
use App\Repository\GuideCategoryRepository;
use App\Repository\GuideRepository;
use App\Service\GuideTreeBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/guides', name: 'guides_')]
final class GuideController extends AbstractController
{
    public function __construct(
        private readonly GuideCategoryRepository $guideCategoryRepository,
        private readonly GuideRepository $guideRepository,
        private readonly GuideTreeBuilder $guideTreeBuilder,
    ) {
    }

    /**
     * Page d'accueil du module Guides.
     *
     * Cette page affiche :
     * - l'arborescence des catégories actives
     * - les derniers guides publiés
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $guideTree = $this->guideTreeBuilder->buildActiveTree();

        $latestGuides = $this->guideRepository->findBy(
            [
                'isPublished' => true,
            ],
            [
                'publishedAt' => 'DESC',
                'title' => 'ASC',
            ],
            6
        );

        return $this->render('guides/index.html.twig', [
            'guideTree' => $guideTree,
            'latestGuides' => $latestGuides,
        ]);
    }

    /**
     * Affiche une catégorie de guides à partir de son slug.
     *
     * Cette page affiche :
     * - la catégorie courante
     * - ses sous-catégories actives
     * - les guides publiés liés à cette catégorie
     */
    #[Route('/category/{slug}', name: 'category_show', methods: ['GET'])]
    public function showCategory(string $slug): Response
    {
        $category = $this->guideCategoryRepository->findOneBy([
            'slug' => $slug,
            'isActive' => true,
        ]);

        if (!$category instanceof GuideCategory) {
            throw $this->createNotFoundException('La catégorie demandée est introuvable.');
        }

        $children = $category->getChildren()
            ->filter(static fn (GuideCategory $child): bool => $child->isActive())
            ->toArray();

        usort(
            $children,
            static fn (GuideCategory $a, GuideCategory $b): int => [$a->getPosition(), $a->getName()] <=> [$b->getPosition(), $b->getName()]
        );

        $guides = $this->guideRepository->findBy(
            [
                'category' => $category,
                'isPublished' => true,
            ],
            [
                'publishedAt' => 'DESC',
                'title' => 'ASC',
            ]
        );

        return $this->render('guides/category_show.html.twig', [
            'category' => $category,
            'children' => $children,
            'guides' => $guides,
        ]);
    }

    /**
     * Affiche le détail d'un guide publié à partir de son slug.
     */
    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $guide = $this->guideRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$guide instanceof Guide) {
            throw $this->createNotFoundException('Le guide demandé est introuvable.');
        }

        return $this->render('guides/show.html.twig', [
            'guide' => $guide,
        ]);
    }
}