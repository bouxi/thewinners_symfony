<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Repository\GuildApplicationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class GuildExtension extends AbstractExtension
{
    public function __construct(
        private Security $security,
        private GuildApplicationRepository $applicationRepository,
    ) {}

    public function getFunctions(): array
    {
        return [
            // Utilisation dans Twig : {{ can_apply_to_guild() }}
            new TwigFunction('can_apply_to_guild', [$this, 'canApplyToGuild']),
        ];
    }

    /**
     * Retourne true si on doit afficher "Postuler" dans la navbar.
     */
    public function canApplyToGuild(): bool
    {
        $user = $this->security->getUser();

        // Pas connecté => pas de lien "Postuler"
        if (!$user instanceof User) {
            return false;
        }

        // Si l'utilisateur a déjà un rôle de guilde, on ne doit plus afficher "Postuler"
        // ⚠️ Adapte les rôles selon ton projet si besoin
        if ($this->security->isGranted('ROLE_GUILD_MEMBER')
            || $this->security->isGranted('ROLE_RECRUE')
            || $this->security->isGranted('ROLE_MEMBER')
            || $this->security->isGranted('ROLE_VETERAN')
            || $this->security->isGranted('ROLE_OFFICER')
            || $this->security->isGranted('ROLE_ADMIN')
        ) {
            return false;
        }

        // Sinon on check la candidature
        $application = $this->applicationRepository->findOneByUserId((int) $user->getId());

        // Pas de candidature => il peut postuler
        if (!$application) {
            return true;
        }

        // Candidature acceptée => on cache "Postuler"
        if ($application->getStatus() === ApplicationStatus::ACCEPTED) {
            return false;
        }

        // Dans les autres cas (pending/refused), tu choisis :
        // Ici : on cache aussi (car il a déjà postulé)
        return false;
    }
}
