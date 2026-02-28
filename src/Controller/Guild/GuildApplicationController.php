<?php

declare(strict_types=1);

namespace App\Controller\Guild;

use App\Entity\Application;
use App\Entity\ApplicationMessage;
use App\Entity\Personnage;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Repository\ApplicationMessageRepository;
use App\Repository\GuildApplicationRepository;
use App\Service\CombatRoleResolver;
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
        GuildApplicationRepository $repo,
        CombatRoleResolver $roleResolver
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($repo->findOneByUserId((int) $user->getId())) {
            return $this->redirectToRoute('guild_application_status');
        }

        $jsonPath = $this->getParameter('kernel.project_dir') . '/public/data/wotlk-classes.json';

        $renderApply = function (array $old = []) use ($jsonPath): Response {
            $wow = [];
            if (is_file($jsonPath)) {
                $decoded = json_decode((string) file_get_contents($jsonPath), true);
                if (is_array($decoded)) {
                    $wow = $decoded;
                }
            }

            return $this->render('guild/apply.html.twig', [
                'wow' => $wow,
                'old' => $old,
            ]);
        };

        if ($request->isMethod('GET')) {
            return $renderApply();
        }

        if (!$this->isCsrfTokenValid('guild_apply', (string)$request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('guild_apply');
        }

        $playerName     = trim((string)$request->request->get('player_name'));
        $class          = trim((string)$request->request->get('class'));
        $specialization = trim((string)$request->request->get('specialization'));
        $motivation     = trim((string)$request->request->get('motivation'));

        $old = compact('playerName','class','specialization','motivation');

        if ($playerName === '' || $class === '' || $specialization === '' || $motivation === '') {
            $this->addFlash('danger', 'Tous les champs sont obligatoires.');
            return $renderApply($old);
        }

        // 🔥 Rôle calculé automatiquement
        $roleEnum = $roleResolver->resolveRoleFromSpec($specialization);

        $personnage = new Personnage();
        $personnage->setUser($user);
        $personnage->setName($playerName);
        $personnage->setClass($class);
        $personnage->setSpec($specialization);
        $personnage->setCombatRole($roleEnum);
        $personnage->setIsPublic(true);
        $personnage->setIsMain(false);

        $em->persist($personnage);

        $application = new Application();
        $application->setUser($user);
        $application->setPersonnage($personnage);
        $application->setPlayerName($playerName);
        $application->setClass($class);
        $application->setSpecialization($specialization);
        $application->setMotivation($motivation);
        $application->setStatus(ApplicationStatus::PENDING);

        $em->persist($application);
        $em->flush();

        $this->addFlash('success', 'Candidature envoyée ✅');

        return $this->redirectToRoute('guild_application_status');
    }
}