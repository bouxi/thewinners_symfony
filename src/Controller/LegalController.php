<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur des pages légales :
 * - Politique de confidentialité
 * - Politique cookies
 * - Conditions générales d'utilisation
 */
final class LegalController extends AbstractController
{
    #[Route('/politique-confidentialite', name: 'app_legal_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }

    #[Route('/politique-cookies', name: 'app_legal_cookies', methods: ['GET'])]
    public function cookies(): Response
    {
        return $this->render('legal/cookies.html.twig');
    }

    #[Route('/conditions-generales', name: 'app_legal_terms', methods: ['GET'])]
    public function terms(): Response
    {
        return $this->render('legal/terms.html.twig');
    }
}