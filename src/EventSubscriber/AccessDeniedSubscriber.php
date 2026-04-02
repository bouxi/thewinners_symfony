<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * Ce subscriber intercepte les exceptions liées à un accès refusé
 * et affiche une page Twig propre à la place du message technique Symfony.
 */
final class AccessDeniedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // On écoute les exceptions du noyau Symfony.
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // On cible uniquement les erreurs d'accès refusé.
        $isAccessDenied =
            $exception instanceof AccessDeniedHttpException
            || $exception instanceof AccessDeniedException;

        if (!$isAccessDenied) {
            return;
        }

        /**
         * Si l'utilisateur n'est pas connecté, on laisse Symfony gérer
         * normalement (par exemple redirection vers login selon ta config).
         */
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        // On rend une page 403 personnalisée.
        $content = $this->twig->render('errors/403.html.twig', [
            'page_title' => 'Accès refusé',
        ]);

        $response = new Response($content, Response::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }
}