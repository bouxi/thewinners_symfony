<?php

namespace App\Service;

use App\Enum\CombatRole;

final class CombatRoleResolver
{
    /**
     * Mapping spécialisation → rôle (WotLK 3.3.5)
     */
    private const SPEC_ROLE_MAP = [

        // Tanks
        'Protection' => CombatRole::TANK,
        'Blood' => CombatRole::TANK,
        'Feral (Tank)' => CombatRole::TANK,

        // Heals
        'Holy' => CombatRole::HEAL,
        'Restauration' => CombatRole::HEAL,
        'Discipline' => CombatRole::HEAL,

        // DPS (par défaut pour toutes autres spés)
        // On ne les met pas toutes ici, on fallback automatiquement
    ];

    public function resolveRoleFromSpec(string $spec): CombatRole
    {
        return self::SPEC_ROLE_MAP[$spec] ?? CombatRole::DPS;
    }
}