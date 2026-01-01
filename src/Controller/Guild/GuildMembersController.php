<?php

declare(strict_types=1);

namespace App\Controller\Guild;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page publique : liste des membres de la guilde.
 * Accessible à tous (visiteurs inclus).
 */
final class GuildMembersController extends AbstractController
{
    #[Route('/guild/members', name: 'guild_members', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // ✅ Public : pas de denyAccessUnlessGranted ici
        $members = $userRepository->findPublicGuildMembers();

        return $this->render('guild/members.html.twig', [
            'members' => $members,
        ]);
    }
}
