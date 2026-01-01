<?php

declare(strict_types=1);

namespace App\Controller\Guild;

use App\Entity\Application;
use App\Entity\ApplicationMessage;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Repository\ApplicationMessageRepository;
use App\Repository\GuildApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GuildApplicationController extends AbstractController
{
    #[Route('/guild/apply', name: 'guild_apply', methods: ['GET', 'POST'])]
    public function apply(
        Request $request,
        EntityManagerInterface $em,
        GuildApplicationRepository $repo
    ): Response {
        // ✅ Seuls les utilisateurs connectés peuvent postuler
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        \assert($user instanceof User);

        // ✅ Empêche de postuler 2 fois
        $existing = $repo->findOneByUserId((int) $user->getId());
        if ($existing) {
            return $this->redirectToRoute('guild_application_status');
        }

        // ✅ Données WoW LK (classe => spécialisations)
        // (Tu pourras ensuite déplacer ça dans un JSON / service, mais déjà là c'est clean)
        $wow = [
            'Chevalier de la mort' => ['Sang', 'Givre', 'Impie'],
            'Druide' => ['Équilibre', 'Farouche', 'Restauration'],
            'Chasseur' => ['Maîtrise des bêtes', 'Précision', 'Survie'],
            'Mage' => ['Arcanes', 'Feu', 'Givre'],
            'Paladin' => ['Sacré', 'Protection', 'Vindicte'],
            'Prêtre' => ['Discipline', 'Sacré', 'Ombre'],
            'Voleur' => ['Assassinat', 'Combat', 'Finesse'],
            'Chaman' => ['Élémentaire', 'Amélioration', 'Restauration'],
            'Démoniste' => ['Affliction', 'Démonologie', 'Destruction'],
            'Guerrier' => ['Armes', 'Fureur', 'Protection'],
        ];

        // ✅ Valeurs par défaut pour pré-remplir le form (utile si erreur)
        $old = [
            'class' => '',
            'specialization' => '',
            'motivation' => '',
        ];

        // ✅ GET : affiche le formulaire
        if ($request->isMethod('GET')) {
            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        }

        // ✅ POST : vérification CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('guild_apply', $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');

            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        }

        // ✅ Récupération des champs
        $class = trim((string) $request->request->get('class', ''));
        $specialization = trim((string) $request->request->get('specialization', ''));
        $motivation = trim((string) $request->request->get('motivation', ''));

        // ✅ On garde les valeurs pour réafficher si erreur
        $old = [
            'class' => $class,
            'specialization' => $specialization,
            'motivation' => $motivation,
        ];

        // ✅ Validation minimale
        if ($class === '' || $specialization === '' || $motivation === '') {
            $this->addFlash('danger', 'Merci de remplir : Classe, Spécialisation et Motivation.');
            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        }

        // ✅ Validation “pro” : classe existante + spé correspondante
        if (!array_key_exists($class, $wow)) {
            $this->addFlash('danger', 'Classe invalide.');
            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        }

        if (!in_array($specialization, $wow[$class], true)) {
            $this->addFlash('danger', 'Spécialisation invalide pour cette classe.');
            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        }

        // ✅ Re-check anti double POST
        $existing = $repo->findOneByUserId((int) $user->getId());
        if ($existing) {
            return $this->redirectToRoute('guild_application_status');
        }

        // ✅ Création candidature
        $application = new Application();
        $application->setUser($user);
        $application->setClass($class);
        $application->setSpecialization($specialization);
        $application->setMotivation($motivation);
        $application->setStatus(ApplicationStatus::PENDING);

        if (method_exists($application, 'setSubmittedAt')) {
            $application->setSubmittedAt(new \DateTimeImmutable());
        }

        $em->persist($application);
        $em->flush();

        $this->addFlash('success', 'Candidature envoyée ✅');
        return $this->redirectToRoute('guild_application_status');
    }

    /**
     * ✅ Page "Ma candidature" :
     * - GET  : affiche candidature + messages
     * - POST : envoie un nouveau message au staff (admin)
     */
    #[Route('/guild/application', name: 'guild_application_status', methods: ['GET', 'POST'])]
    public function status(
        Request $request,
        EntityManagerInterface $em,
        GuildApplicationRepository $repo,
        ApplicationMessageRepository $messageRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        \assert($user instanceof User);

        // ✅ On récupère la candidature du user connecté
        $application = $repo->findOneByUserId((int) $user->getId());
        if (!$application) {
            return $this->redirectToRoute('guild_apply');
        }

        // ✅ POST : envoi d'un message lié à la candidature
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('application_reply', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token CSRF invalide.');
                return $this->redirectToRoute('guild_application_status');
            }

            $content = trim((string) $request->request->get('content', ''));

            if ($content === '') {
                $this->addFlash('warning', 'Ton message est vide.');
                return $this->redirectToRoute('guild_application_status');
            }

            $message = new ApplicationMessage();
            $message->setApplication($application);
            $message->setSender($user);
            $message->setContent($content);
            $message->setCreatedAt(new \DateTimeImmutable());

            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message envoyé ✅');
            return $this->redirectToRoute('guild_application_status');
        }

        // ✅ GET : charge les messages
        $messages = $messageRepo->findBy(
            ['application' => $application],
            ['createdAt' => 'ASC']
        );

        if (method_exists($messageRepo, 'markAllAsReadForApplicant')) {
            $messageRepo->markAllAsReadForApplicant((int) $application->getId());
        }

        return $this->render('guild/application_status.html.twig', [
            'application' => $application,
            'messages' => $messages,
        ]);
    }
}
