<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RaidEvent;
use App\Entity\RaidSignup;
use App\Entity\User;
use App\Repository\RaidSignupRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\RaidRole;

final class RaidManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private RaidSignupRepository $signupRepository
    ) {}

    /**
     * Crée un raid en vérifiant que la fin est après le début.
     */
    public function createRaid(User $creator, RaidEvent $raid): void
    {
        if ($raid->getEndsAt() <= $raid->getStartsAt()) {
            throw new \InvalidArgumentException('La date de fin doit être après la date de début.');
        }

        $raid->setCreatedBy($creator);

        $this->em->persist($raid);
        $this->em->flush();
    }

    /**
     * Inscrit un utilisateur à un raid (une seule inscription par user).
     */
    public function signup(User $user, RaidEvent $raid, RaidRole $role, ?string $note): void
    {
        if ($raid->getEndsAt() < new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Ce raid est déjà terminé.');
        }

        if ($this->signupRepository->findOneByRaidAndUser($raid, $user)) {
            throw new \InvalidArgumentException('Tu es déjà inscrit à ce raid.');
        }

        $signup = (new RaidSignup())
            ->setRaid($raid)
            ->setUser($user)
            ->setRole($role)
            ->setNote($note);

        $this->em->persist($signup);
        $this->em->flush();
    }

    /**
     * Désinscription.
     */
    public function leave(User $user, RaidEvent $raid): void
    {
        $existing = $this->signupRepository->findOneByRaidAndUser($raid, $user);
        if (!$existing) {
            return; // rien à faire
        }

        $this->em->remove($existing);
        $this->em->flush();
    }
}
