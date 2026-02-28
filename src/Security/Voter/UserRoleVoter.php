<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter "pro" pour sécuriser les changements de rôles sensibles.
 *
 * Idée :
 * - Les admins "normaux" peuvent gérer les users (membre, profil, etc.)
 * - MAIS seuls les SUPER_ADMIN (ou GM via hiérarchie) peuvent :
 *   - donner/enlever ROLE_ADMIN
 *   - donner/enlever ROLE_GM / ROLE_SUPER_ADMIN
 * - Interdit de retirer le dernier GM.
 * - Interdit de modifier ses propres privilèges (anti lock-out).
 */
final class UserRoleVoter extends Voter
{
    public const TOGGLE_ADMIN = 'USER_TOGGLE_ADMIN';
    public const TOGGLE_GM = 'USER_TOGGLE_GM';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof User
            && \in_array($attribute, [self::TOGGLE_ADMIN, self::TOGGLE_GM], true);
    }

    /**
     * ✅ Signature Symfony 7.3+ (4e argument optionnel ?Vote $vote = null)
     * @param User $subject
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $actor = $token->getUser();
        if (!$actor instanceof User) {
            $vote?->addReason('Utilisateur non connecté.');
            return false;
        }

        /** @var User $target */
        $target = $subject;

        // ✅ Anti lock-out : pas de modification sur soi-même via ces actions
        if ($actor->getId() !== null && $target->getId() === $actor->getId()) {
            $vote?->addReason('Action refusée : modification de ses propres privilèges.');
            return false;
        }

        // ✅ Seuls les super admins peuvent gérer ces actions sensibles
        // (ROLE_GM => ROLE_SUPER_ADMIN via ta hiérarchie)
        if (!\in_array('ROLE_SUPER_ADMIN', $actor->getRoles(), true)) {
            $vote?->addReason('Action réservée au Super Admin.');
            return false;
        }

        return match ($attribute) {
            self::TOGGLE_ADMIN => true,
            self::TOGGLE_GM => $this->canToggleGm($target, $vote),
            default => false,
        };
    }

    private function canToggleGm(User $target, ?Vote $vote = null): bool
    {
        // Empêche de retirer le dernier GM
        $stored = $target->getStoredRoles();

        if (\in_array('ROLE_GM', $stored, true)) {
            $gmCount = $this->userRepository->countUsersWithRole('ROLE_GM');
            if ($gmCount <= 1) {
                $vote?->addReason('Impossible : cet utilisateur est le dernier GM.');
                return false;
            }
        }

        return true;
    }
}