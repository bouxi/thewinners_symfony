<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

/**
 * Service métier : gère proprement les rôles/grades de guilde.
 * Objectif : un seul rôle "guilde" à la fois + ROLE_USER conservé.
 */
final class GuildRoleManager
{
    /**
     * Donne un grade de guilde à l'utilisateur.
     * - Supprime les anciens grades guilde
     * - Ajoute le nouveau grade
     * - Conserve ROLE_USER
     */
    public function setGuildRole(User $user, string $guildRole): void
    {
        if (!\in_array($guildRole, User::GUILD_ROLES, true)) {
            throw new \InvalidArgumentException('Rôle de guilde invalide : ' . $guildRole);
        }

        $roles = $user->getRoles();

        // 1) On supprime tous les anciens rôles de guilde
        $roles = array_values(array_diff($roles, User::GUILD_ROLES));

        // 2) On ajoute le nouveau grade
        $roles[] = $guildRole;

        // 3) Sécurité : ROLE_USER doit toujours être présent
        if (!\in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        // 4) Unicité + set
        $user->setRoles(array_values(array_unique($roles)));
    }

    /**
     * Retire tout rôle de guilde (si un jour tu veux "retirer de la guilde").
     */
    public function clearGuildRoles(User $user): void
    {
        $roles = array_values(array_diff($user->getRoles(), User::GUILD_ROLES));

        if (!\in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        $user->setRoles(array_values(array_unique($roles)));
    }

    /**
     * True si l'utilisateur a un grade guilde (donc membre/recrue/officier/vétéran).
     */
    public function isInGuild(User $user): bool
    {
        foreach (User::GUILD_ROLES as $r) {
            if (\in_array($r, $user->getRoles(), true)) {
                return true;
            }
        }
        return false;
    }
}
