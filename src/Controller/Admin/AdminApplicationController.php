<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\ApplicationStatus;
use App\Enum\GuildRank;
use App\Repository\GuildApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ApplicationMessageRepository;
use App\Entity\ApplicationMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/admin/applications')]
final class AdminApplicationController extends AbstractController
{
    #[Route('', name: 'admin_applications_index', methods: ['GET'])]
    public function index(Request $request, GuildApplicationRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Filtre status en query (?status=pending|accepted|rejected)
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

        // ✅ CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('admin_app_set_status_' . $application->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_applications_index');
        }

        // ✅ Nouveau statut depuis POST
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

        /**
         * ✅ Règle métier :
         * Si ACCEPTED => l'utilisateur passe automatiquement en RECRUE.
         */
        if ($newStatus === ApplicationStatus::ACCEPTED) {
            $user = $application->getUser();

            // On ne touche pas au grade si déjà membre / officier / etc.
            // (optionnel, mais c'est plus "pro")
            if ($user->getGuildRank() === GuildRank::VISITOR) {
                $user->setGuildRank(GuildRank::RECRUE);
            }

            // Optionnel si ton entity a ce champ
            if (method_exists($application, 'setHasJoinedGuild')) {
                $application->setHasJoinedGuild(true);
            }
        }

        /**
         * (Optionnel) si REFUSED => tu peux laisser VISITOR ou ne rien changer
         * selon ta logique de guilde.
         */

        $em->flush();
        $this->addFlash('success', 'Statut mis à jour ✅');

        // Retour au filtre courant (query d'abord, sinon hidden input)
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

        // ✅ ICI on récupère les messages liés à cette candidature
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

        // ✅ CSRF
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

        // ✅ Création du message (ADMIN = sender)
        $message = new ApplicationMessage();
        $message->setApplication($application);
        $message->setSender($this->getUser()); // ADMIN
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
