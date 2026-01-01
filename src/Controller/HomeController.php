<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page d'accueil publique du site TheWinners.
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Plus tard on pourra passer des données (news, events, etc.)
        return $this->render('home/index.html.twig');
    }
}
