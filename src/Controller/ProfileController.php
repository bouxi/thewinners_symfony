<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserConsentRepository;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Service\AvatarUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\UserConsent;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        AvatarUploader $avatarUploader,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $me */
        $me = $this->getUser();

        /* =========================
         * FORM PROFIL
         * ========================= */
        $profileForm = $this->createForm(ProfileType::class, $me, [
            'csrf_token_id' => 'profile_update',
        ]);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $oldAvatar = $me->getAvatar();
            $avatarFile = $profileForm->get('avatarFile')->getData();

            if ($avatarFile) {
                $newPath = $avatarUploader->replace($avatarFile, $oldAvatar);
                $me->setAvatar($newPath);
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        /* =========================
         * FORM PERSONNAGE
         * ========================= */
        if (
            $request->isMethod('POST') &&
            $request->request->has('_token_character') &&
            $this->isCsrfTokenValid(
                'character_update',
                (string) $request->request->get('_token_character')
            )
        ) {
            $me->setCharacterName(
                trim((string) $request->request->get('characterName'))
            );
            $me->setCharacterClass(
                trim((string) $request->request->get('characterClass'))
            );
            $me->setCharacterSpec(
                trim((string) $request->request->get('characterSpec'))
            );

            $em->flush();
            $this->addFlash('success', 'Personnage mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        /* =========================
         * FORM MOT DE PASSE
         * ========================= */
        $passwordForm = $this->createForm(ChangePasswordType::class, null, [
            'csrf_token_id' => 'password_change',
        ]);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            if (!$passwordHasher->isPasswordValid(
                $me,
                (string) $passwordForm->get('currentPassword')->getData()
            )) {
                $this->addFlash('danger', 'Mot de passe actuel incorrect.');
                return $this->redirectToRoute('app_profile');
            }

            $me->setPassword(
                $passwordHasher->hashPassword(
                    $me,
                    (string) $passwordForm->get('newPassword')->get('first')->getData()
                )
            );

            $em->flush();
            $this->addFlash('success', 'Mot de passe modifié.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }

    #[Route('/profile/consents', name: 'app_profile_consents', methods: ['GET'])]
    public function consents(
        UserConsentRepository $userConsentRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $consent = $userConsentRepository->findOneBy(['user' => $user]);

        if (!$consent) {
            $consent = new UserConsent();
            $consent->setUser($user);
            $consent->setPrivacyAccepted(true);
            $consent->setTermsAccepted(true);
            $consent->setCookiesAccepted(false);
            $consent->setPrivacyVersion((string) $parameterBag->get('app.legal_versions.privacy'));
            $consent->setTermsVersion((string) $parameterBag->get('app.legal_versions.terms'));

            $fallbackDate = method_exists($user, 'getDateInscription') && $user->getDateInscription() !== null
                ? $user->getDateInscription()
                : new \DateTimeImmutable();

            $consent->setPrivacyAcceptedAt($fallbackDate);
            $consent->setTermsAcceptedAt($fallbackDate);

            $entityManager->persist($consent);
            $entityManager->flush();
        }

        return $this->render('profile/consents.html.twig', [
            'consent' => $consent,
        ]);
    }
}
