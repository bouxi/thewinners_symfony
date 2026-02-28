<?php

declare(strict_types=1);

namespace App\Controller\Guild;

use App\Enum\CombatRole;
use App\Repository\PersonnageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GuildMembersController extends AbstractController
{
    #[Route('/guild/members', name: 'guild_members', methods: ['GET'])]
    public function index(Request $request, PersonnageRepository $repo): Response
    {
        // role attendu: "tank" | "heal" | "dps" (ou vide)
        $roleParam = trim(strtolower($request->query->getString('role', '')));

        // tryFrom => null si invalide
        $roleEnum = $roleParam !== '' ? CombatRole::tryFrom($roleParam) : null;

        $members = $repo->findPublicGuildMembers($roleEnum);

        return $this->render('guild/members.html.twig', [
            'members' => $members,          // ⚠️ maintenant ce sont des Personnage[]
            'role' => $roleParam !== '' ? $roleParam : null,
        ]);
    }
}
