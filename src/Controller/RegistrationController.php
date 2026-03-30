<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\GuildRank;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\UserConsent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Gère l'inscription des nouveaux utilisateurs.
 * À l'inscription :
 *  - roles = ["ROLE_USER"]
 *  - guildRank = VISITOR (défaut)
 */
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag
    ): Response {
        // On crée un nouvel utilisateur vide
        $user = new User();

        // On crée le formulaire lié à l'utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Soumission + validation OK
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du mot de passe "en clair" depuis le formulaire
            /** @var string $plainPassword */
            $plainPassword = (string) $form->get('plainPassword')->getData();

            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Rôles par défaut : simple utilisateur du site
            $user->setRoles(['ROLE_USER']);

            // Grade de guilde par défaut : VISITOR
            $user->setGuildRank(GuildRank::VISITOR);

            // dateInscription est déjà initialisé dans le constructeur,
            // mais tu peux forcer ici si tu veux une autre logique.

            // Persistance de User en BDD
            $em->persist($user);

            // Enregistrement du consentement légal lors de l'inscription
            // (CGU + politique de confidentialité)
            $now = new \DateTimeImmutable();

            $userConsent = new UserConsent();
            $userConsent->setUser($user);
            $userConsent->setPrivacyAccepted(true);
            $userConsent->setTermsAccepted(true);
            $userConsent->setPrivacyVersion((string) $parameterBag->get('app.legal_versions.privacy'));
            $userConsent->setTermsVersion((string) $parameterBag->get('app.legal_versions.terms'));
            $userConsent->setPrivacyAcceptedAt($now);
            $userConsent->setTermsAcceptedAt($now);

            $em->persist($userConsent);
            $em->flush();

            // Message flash pour notifier l'utilisateur
            $this->addFlash('success', 'Votre compte a bien été créé, vous pouvez vous connecter.');

            // Redirection vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // Affichage du formulaire
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
