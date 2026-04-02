<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\UserConsentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/consents')]
final class AdminConsentController extends AbstractController
{
    #[Route('', name: 'admin_consents_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserConsentRepository $userConsentRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $filter = $request->query->get('filter');
        $consents = $userConsentRepository->findAllWithUsers(
            \is_string($filter) ? $filter : null
        );

        return $this->render('admin/consents/index.html.twig', [
            'consents' => $consents,
            'current_filter' => $filter,
        ]);
    }
}