<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Gère la connexion/déconnexion via form_login.
 */
class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère la dernière erreur de login s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide :
        // Symfony l'interceptera automatiquement grâce à la config firewall.logout
        throw new \LogicException('Ce code ne doit jamais être exécuté : la déconnexion est gérée par Symfony.');
    }
}
