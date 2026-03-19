<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ApplicationMessage;
use App\Enum\ApplicationStatus;
use App\Enum\GuildRank;
use App\Repository\ApplicationMessageRepository;
use App\Repository\GuildApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/applications')]
final class AdminApplicationController extends AbstractController
{
    #[Route('', name: 'admin_applications_index', methods: ['GET'])]
    public function index(Request $request, GuildApplicationRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $statusRaw = trim((string) $request->query->get('status', ''));

        $allowed = [
            ApplicationStatus::PENDING->value,
            ApplicationStatus::ACCEPTED->value,
            ApplicationStatus::REJECTED->value,
        ];

        if ($statusRaw !== '' && in_array($statusRaw, $allowed, true)) {
            $applications = $repo->findBy(
                ['status' => ApplicationStatus::from($statusRaw)],
                ['submittedAt' => 'DESC']
            );
            $status = $statusRaw;
        } else {
            $applications = $repo->findBy([], ['submittedAt' => 'DESC']);
            $status = '';
        }

        return $this->render('admin/applications/index.html.twig', [
            'applications' => $applications,
            'status' => $status,
        ]);
    }

    #[Route('/{id}/set-status', name: 'admin_applications_set_status', methods: ['POST'])]
    public function setStatus(
        int $id,
        Request $request,
        GuildApplicationRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $application = $repo->find($id);
        if (!$application) {
            throw $this->createNotFoundException('Candidature introuvable.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('admin_app_set_status_' . $application->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_applications_index');
        }

        $newStatusRaw = trim((string) $request->request->get('status', ''));

        $allowed = [
            ApplicationStatus::PENDING->value,
            ApplicationStatus::ACCEPTED->value,
            ApplicationStatus::REJECTED->value,
        ];

        if (!in_array($newStatusRaw, $allowed, true)) {
            $this->addFlash('danger', 'Statut invalide.');
            return $this->redirectToRoute('admin_applications_index');
        }

        $newStatus = ApplicationStatus::from($newStatusRaw);
        $application->setStatus($newStatus);

        $user = $application->getUser();

        /**
         * ✅ Règle métier :
         * - ACCEPTED  => devient membre de guilde
         * - PENDING/REJECTED => n'est pas encore (ou plus) membre
         */
        if ($newStatus === ApplicationStatus::ACCEPTED) {
            $user->setIsGuildMember(true);

            // Si l'utilisateur est encore simple visiteur, on le passe recrue
            if ($user->getGuildRank() === GuildRank::VISITOR) {
                $user->setGuildRank(GuildRank::RECRUE);
            }

            if (method_exists($application, 'setHasJoinedGuild')) {
                $application->setHasJoinedGuild(true);
            }
        } else {
            $user->setIsGuildMember(false);

            // ✅ Si tu veux revenir à VISITOR quand ce n'est plus accepté
            if ($user->getGuildRank() === GuildRank::RECRUE) {
                $user->setGuildRank(GuildRank::VISITOR);
            }

            if (method_exists($application, 'setHasJoinedGuild')) {
                $application->setHasJoinedGuild(false);
            }
        }

        $em->flush();
        $this->addFlash('success', 'Statut mis à jour ✅');

        $currentFilter = (string) $request->query->get('status', '');
        if ($currentFilter === '') {
            $currentFilter = (string) $request->request->get('current_status_filter', '');
        }

        return $this->redirectToRoute('admin_applications_index', [
            'status' => $currentFilter,
        ]);
    }

    #[Route('/{id}', name: 'admin_applications_show', methods: ['GET'])]
    public function show(
        int $id,
        GuildApplicationRepository $repo,
        ApplicationMessageRepository $applicationMessageRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $application = $repo->find($id);
        if (!$application) {
            throw $this->createNotFoundException('Candidature introuvable.');
        }

        $messages = $applicationMessageRepository->findBy(
            ['application' => $application],
            ['createdAt' => 'ASC']
        );

        return $this->render('admin/applications/show.html.twig', [
            'application' => $application,
            'messages' => $messages,
        ]);
    }

    #[Route('/{id}/reply', name: 'admin_applications_reply', methods: ['POST'])]
    public function reply(
        int $id,
        Request $request,
        GuildApplicationRepository $applicationRepo,
        EntityManagerInterface $em
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $application = $applicationRepo->find($id);
        if (!$application) {
            throw $this->createNotFoundException('Candidature introuvable.');
        }

        if (
            !$this->isCsrfTokenValid(
                'admin_reply_' . $application->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_applications_show', ['id' => $id]);
        }

        $content = trim((string) $request->request->get('content', ''));

        if ($content === '') {
            $this->addFlash('warning', 'Message vide.');
            return $this->redirectToRoute('admin_applications_show', ['id' => $id]);
        }

        $message = new ApplicationMessage();
        $message->setApplication($application);
        $message->setSender($this->getUser());
        $message->setContent($content);
        $message->setCreatedAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        $this->addFlash('success', 'Réponse envoyée au candidat ✅');

        return $this->redirectToRoute('admin_applications_show', [
            'id' => $id,
        ]);
    }
}