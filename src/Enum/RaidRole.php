<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Rôles possibles en raid.
 */
enum RaidRole: string
{
    case TANK = 'tank';
    case HEAL = 'heal';
    case DPS  = 'dps';

    /**
     * Labels “propres” pour l'affichage (facile à utiliser en Twig).
     */
    public function label(): string
    {
        return match ($this) {
            self::TANK => 'Tank',
            self::HEAL => 'Heal',
            self::DPS => 'DPS',
        };
    }
}
