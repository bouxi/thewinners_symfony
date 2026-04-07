<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Guide;
use App\Form\Admin\GuideType;
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
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $guides = $this->guideRepository->createQueryBuilder('g')
            ->leftJoin('g.category', 'c')
            ->addSelect('c')
            ->leftJoin('g.author', 'a')
            ->addSelect('a')
            ->orderBy('g.updatedAt', 'DESC')
            ->addOrderBy('g.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/guides/index.html.twig', [
            'guides' => $guides,
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
}