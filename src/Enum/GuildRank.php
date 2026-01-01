<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Grades de guilde (stockés en base via enumType).
 * ✅ Source de vérité pour le "grade" d'un utilisateur.
 * ⚠️ ROLE_ADMIN n'est PAS un grade : c'est un rôle "site" (stocké dans User::$roles).
 */
enum GuildRank: string
{
    case VISITOR      = 'VISITOR';
    case RECRUE       = 'RECRUE';
    case MEMBER       = 'MEMBER';
    case VETERAN      = 'VETERAN';
    case OFFICER      = 'OFFICER';
    case GUILD_MASTER = 'GUILD_MASTER';

    /**
     * Retourne le rôle Symfony associé au grade (ou null si aucun).
     */
    public function role(): ?string
    {
        return match ($this) {
            self::VISITOR      => null,
            self::RECRUE       => 'ROLE_RECRUE',
            self::MEMBER       => 'ROLE_MEMBER',
            self::VETERAN      => 'ROLE_VETERAN',
            self::OFFICER      => 'ROLE_OFFICER',
            self::GUILD_MASTER => 'ROLE_GUILD_MASTER',
        };
    }

    /**
     * True si le grade correspond à un membre de la guilde.
     */
    public function isGuildMember(): bool
    {
        return $this !== self::VISITOR;
    }

    /**
     * Labels propres pour UI / admin.
     */
    public function label(): string
    {
        return match ($this) {
            self::VISITOR      => 'Visiteur',
            self::RECRUE       => 'Recrue',
            self::MEMBER       => 'Membre',
            self::VETERAN      => 'Vétéran',
            self::OFFICER      => 'Officier',
            self::GUILD_MASTER => 'Maître de Guilde',
        };
    }
}
