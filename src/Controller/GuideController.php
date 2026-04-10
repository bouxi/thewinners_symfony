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
        // On construit l'arborescence des catégories actives.
        $guideTree = $this->guideTreeBuilder->buildActiveTree();

        // On récupère les 6 derniers guides publiés.
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

        // On affiche la page d'accueil du module Guides.
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
     *
     * Si la catégorie n'existe pas ou n'est pas active,
     * on déclenche une erreur 404.
     */
    #[Route('/category/{slug}', name: 'category_show', methods: ['GET'])]
    public function showCategory(string $slug): Response
    {
        // On recherche une catégorie active correspondant au slug demandé.
        $category = $this->guideCategoryRepository->findOneBy([
            'slug' => $slug,
            'isActive' => true,
        ]);

        // Si aucune catégorie active n'est trouvée, on renvoie une 404.
        // Symfony affichera alors la page d'erreur 404 personnalisée en production.
        if (!$category instanceof GuideCategory) {
            throw $this->createNotFoundException('La catégorie demandée est introuvable.');
        }

        // On récupère uniquement les sous-catégories actives.
        $children = $category->getChildren()
            ->filter(static fn (GuideCategory $child): bool => $child->isActive())
            ->toArray();

        // On trie les sous-catégories par position, puis par nom.
        usort(
            $children,
            static fn (GuideCategory $a, GuideCategory $b): int => [$a->getPosition(), $a->getName()] <=> [$b->getPosition(), $b->getName()]
        );

        // On récupère les guides publiés liés à cette catégorie.
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

        // On affiche la page de détail de la catégorie.
        return $this->render('guides/category_show.html.twig', [
            'category' => $category,
            'children' => $children,
            'guides' => $guides,
        ]);
    }

    /**
     * Affiche le détail d'un guide publié à partir de son slug.
     *
     * Si le guide n'existe pas ou n'est pas publié,
     * on déclenche une erreur 404.
     */
    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        // On recherche un guide publié correspondant au slug demandé.
        $guide = $this->guideRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true,
        ]);

        // Si aucun guide publié n'est trouvé, on renvoie une 404.
        // Symfony affichera alors la page d'erreur 404 personnalisée en production.
        if (!$guide instanceof Guide) {
            throw $this->createNotFoundException('Le guide demandé est introuvable.');
        }

        // On affiche la page de détail du guide.
        return $this->render('guides/show.html.twig', [
            'guide' => $guide,
        ]);
    }
}