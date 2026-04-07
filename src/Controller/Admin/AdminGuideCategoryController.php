<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\GuideCategory;
use App\Form\Admin\GuideCategoryType;
use App\Repository\GuideCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/guides/categories', name: 'admin_guides_categories_')]
final class AdminGuideCategoryController extends AbstractController
{
    public function __construct(
        private readonly GuideCategoryRepository $guideCategoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->guideCategoryRepository->createQueryBuilder('gc')
            ->leftJoin('gc.parent', 'parent')
            ->addSelect('parent')
            ->orderBy('gc.position', 'ASC')
            ->addOrderBy('gc.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/guides/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new GuideCategory();
        $category->setIsActive(true);

        $form = $this->createForm(GuideCategoryType::class, $category, [
            'current_category' => $category,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($category->getParent() === $category) {
                $this->addFlash('danger', 'Une catégorie ne peut pas être son propre parent.');

                return $this->redirectToRoute('admin_guides_categories_new');
            }

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'La catégorie a été créée avec succès.');

            return $this->redirectToRoute('admin_guides_categories_index');
        }

        return $this->render('admin/guides/categories/form.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'pageTitle' => 'Créer une catégorie de guide',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GuideCategory $category): Response
    {
        $form = $this->createForm(GuideCategoryType::class, $category, [
            'current_category' => $category,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($category->getParent() === $category) {
                $this->addFlash('danger', 'Une catégorie ne peut pas être son propre parent.');

                return $this->redirectToRoute('admin_guides_categories_edit', [
                    'id' => $category->getId(),
                ]);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'La catégorie a été modifiée avec succès.');

            return $this->redirectToRoute('admin_guides_categories_index');
        }

        return $this->render('admin/guides/categories/form.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'pageTitle' => 'Modifier une catégorie de guide',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, GuideCategory $category): Response
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_guide_category_' . $category->getId(), (string) $submittedToken)) {
            $this->addFlash('danger', 'Jeton CSRF invalide. Suppression annulée.');

            return $this->redirectToRoute('admin_guides_categories_index');
        }

        if (!$category->getChildren()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer cette catégorie : elle contient des sous-catégories.');

            return $this->redirectToRoute('admin_guides_categories_index');
        }

        if (!$category->getGuides()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer cette catégorie : elle contient encore des guides.');

            return $this->redirectToRoute('admin_guides_categories_index');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->addFlash('success', 'La catégorie a été supprimée avec succès.');

        return $this->redirectToRoute('admin_guides_categories_index');
    }
}