<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Repository\ConversationRepository;
use App\Repository\GuildApplicationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        ConversationRepository $conversationRepository,
        GuildApplicationRepository $guildApplicationRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Compte d'utilisateurs
        $usersCount = (int) $userRepository->count([]);

        // Conversations (= module messages)
        $messagesCount = (int) $conversationRepository->count([]);

        // Candidatures : total + en attente
        $applicationsCount = (int) $guildApplicationRepository->count([]);

        // Option A : pending
        $applicationsPending = (int) $guildApplicationRepository->count([
            'status' => Application::STATUS_PENDING,
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'usersCount' => $usersCount,
            'messagesCount' => $messagesCount,
            'applicationsCount' => $applicationsCount,
            'applicationsPending' => $applicationsPending,
        ]);
    }
}
