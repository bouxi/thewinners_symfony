<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Guide;
use App\Entity\GuideCategory;
use App\Form\Admin\GuideType;
use App\Repository\GuideCategoryRepository;
use App\Repository\GuideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/guides', name: 'admin_guides_')]
final class AdminGuideController extends AbstractController
{
    public function __construct(
        private readonly GuideRepository $guideRepository,
        private readonly GuideCategoryRepository $guideCategoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $status = trim((string) $request->query->get('status', ''));
        $categoryId = $request->query->get('category');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;

        $selectedCategory = null;

        if ($categoryId !== null && $categoryId !== '') {
            $selectedCategory = $this->guideCategoryRepository->find((int) $categoryId);

            if (!$selectedCategory instanceof GuideCategory) {
                $selectedCategory = null;
            }
        }

        $result = $this->guideRepository->findAdminList(
            $query !== '' ? $query : null,
            $status !== '' ? $status : null,
            $selectedCategory,
            $page,
            $limit
        );

        $categories = $this->guideCategoryRepository->createQueryBuilder('gc')
            ->andWhere('gc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('gc.position', 'ASC')
            ->addOrderBy('gc.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/guides/index.html.twig', [
            'guides' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'limit' => $result['limit'],
            'query' => $query,
            'status' => $status,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $guide = new Guide();

        $form = $this->createForm(GuideType::class, $guide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();

            if ($user !== null) {
                $guide->setAuthor($user);
            }

            if ($guide->isPublished() && $guide->getPublishedAt() === null) {
                $guide->setPublishedAt(new \DateTimeImmutable());
            }

            $this->entityManager->persist($guide);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le guide a été créé avec succès.');

            return $this->redirectToRoute('admin_guides_index');
        }

        return $this->render('admin/guides/form.html.twig', [
            'form' => $form->createView(),
            'guide' => $guide,
            'pageTitle' => 'Créer un guide',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Guide $guide): Response
    {
        $form = $this->createForm(GuideType::class, $guide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($guide->isPublished() && $guide->getPublishedAt() === null) {
                $guide->setPublishedAt(new \DateTimeImmutable());
            }

            if (!$guide->isPublished()) {
                $guide->setPublishedAt(null);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Le guide a été modifié avec succès.');

            return $this->redirectToRoute('admin_guides_index');
        }

        return $this->render('admin/guides/form.html.twig', [
            'form' => $form->createView(),
            'guide' => $guide,
            'pageTitle' => 'Modifier un guide',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Guide $guide): Response
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_guide_' . $guide->getId(), (string) $submittedToken)) {
            $this->addFlash('danger', 'Jeton CSRF invalide. Suppression annulée.');

            return $this->redirectToRoute('admin_guides_index');
        }

        $this->entityManager->remove($guide);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le guide a été supprimé avec succès.');

        return $this->redirectToRoute('admin_guides_index');
    }
}