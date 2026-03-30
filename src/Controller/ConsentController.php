<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserConsent;
use App\Repository\UserConsentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Gère l'enregistrement et le contrôle du consentement cookies.
 */
final class ConsentController extends AbstractController
{
    #[Route('/consent/cookies', name: 'app_consent_cookies', methods: ['POST'])]
    public function saveCookiesConsent(
        Request $request,
        EntityManagerInterface $entityManager,
        UserConsentRepository $userConsentRepository,
        ParameterBagInterface $parameterBag
    ): JsonResponse {
        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getContent(), true);
        $choice = $data['choice'] ?? null;

        if (!\in_array($choice, ['accepted', 'rejected'], true)) {
            return $this->json([
                'success' => false,
                'message' => 'Choix invalide.',
            ], 400);
        }

        $response = $this->json([
            'success' => true,
            'choice' => $choice,
        ]);

        // Cookie navigateur utilisé pour mémoriser le choix côté client.
        $cookie = Cookie::create(
            'tw_cookie_consent',
            $choice,
            new \DateTimeImmutable('+6 months'),
            '/',
            null,
            false,
            false,
            false,
            Cookie::SAMESITE_LAX
        );

        $response->headers->setCookie($cookie);

        $user = $this->getUser();

        // Si l'utilisateur est connecté, on enregistre aussi le choix en base.
        if ($user instanceof User) {
            $consent = $userConsentRepository->findOneBy(['user' => $user]);

            if (!$consent instanceof UserConsent) {
                $consent = new UserConsent();
                $consent->setUser($user);
            }

            $currentVersion = (string) $parameterBag->get('app.legal_versions.cookies');

            // Si la version actuelle diffère de celle enregistrée,
            // on réinitialise l'ancien état avant d'enregistrer le nouveau choix.
            if (
                $consent->getCookiesVersion() !== null
                && $consent->getCookiesVersion() !== $currentVersion
            ) {
                $consent->setCookiesAccepted(false);
                $consent->setCookieChoice(null);
                $consent->setCookiesAcceptedAt(null);
            }

            $now = new \DateTimeImmutable();

            $consent->setCookiesAccepted($choice === 'accepted');
            $consent->setCookieChoice($choice);
            $consent->setCookiesVersion($currentVersion);
            $consent->setCookiesAcceptedAt($now);

            $entityManager->persist($consent);
            $entityManager->flush();
        }

        return $response;
    }

    #[Route('/consent/status', name: 'app_consent_status', methods: ['GET'])]
    public function consentStatus(
        UserConsentRepository $userConsentRepository,
        ParameterBagInterface $parameterBag
    ): JsonResponse {
        $user = $this->getUser();
        $currentVersion = (string) $parameterBag->get('app.legal_versions.cookies');

        // Par défaut, on considère qu'il faut afficher la bannière.
        $mustAsk = true;

        if ($user instanceof User) {
            $consent = $userConsentRepository->findOneBy(['user' => $user]);

            if ($consent instanceof UserConsent) {
                $mustAsk = (
                    !$consent->isCookiesAccepted()
                    || $consent->getCookiesVersion() !== $currentVersion
                );
            }
        }

        return $this->json([
            'mustAskConsent' => $mustAsk,
        ]);
    }
}