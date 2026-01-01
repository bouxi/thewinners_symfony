<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Service\AvatarUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        $me = $this->getUser();
        \assert($me instanceof User);

        /* =========================
         * FORMULAIRE PROFIL
         * ========================= */
        $profileForm = $this->createForm(ProfileType::class, $me, [
            'csrf_token_id' => 'profile_update',
        ]);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $oldAvatar = $me->getAvatar();

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $avatarFile */
            $avatarFile = $profileForm->get('avatarFile')->getData();

            if ($avatarFile) {
                try {
                    // Upload nouveau + suppression ancien
                    $newPath = $avatarUploader->replace($avatarFile, $oldAvatar);
                    $me->setAvatar($newPath);
                } catch (\RuntimeException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_profile');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        /* =========================
         * FORMULAIRE MOT DE PASSE
         * ========================= */
        $passwordForm = $this->createForm(ChangePasswordType::class, null, [
            'csrf_token_id' => 'password_change',
        ]);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = (string) $passwordForm->get('currentPassword')->getData();
            $newPassword = (string) $passwordForm->get('newPassword')->get('first')->getData();

            if (!$passwordHasher->isPasswordValid($me, $currentPassword)) {
                $this->addFlash('danger', 'Mot de passe actuel incorrect.');
                return $this->redirectToRoute('app_profile');
            }

            $me->setPassword(
                $passwordHasher->hashPassword($me, $newPassword)
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
}
