<?php

declare(strict_types=1);

namespace App\Service;

final class RaidData
{
    /**
     * Liste WotLK 3.3.5 (instances + formats)
     * Clé = valeur stockée (stable), Valeur = label affiché
     */
    public const CHOICES = [
        // Tier 7
        'naxx_10' => 'Naxxramas (10)',
        'naxx_25' => 'Naxxramas (25)',
        'os_10'   => 'Sartharion / Obsidian Sanctum (10)',
        'os_25'   => 'Sartharion / Obsidian Sanctum (25)',
        'eoe_10'  => 'Malygos / Eye of Eternity (10)',
        'eoe_25'  => 'Malygos / Eye of Eternity (25)',

        // Tier 8
        'ulduar_10' => 'Ulduar (10)',
        'ulduar_25' => 'Ulduar (25)',

        // Tier 9
        'toc_10' => "Trial of the Crusader (10)",
        'toc_25' => "Trial of the Crusader (25)",

        // Tier 10
        'icc_10' => "Icecrown Citadel (10)",
        'icc_25' => "Icecrown Citadel (25)",
        'rs_10'  => "Ruby Sanctum (10)",
        'rs_25'  => "Ruby Sanctum (25)",

        // Spécial WotLK
        'ony_10' => "Onyxia (10)",
        'ony_25' => "Onyxia (25)",
        'voa_10' => "Vault of Archavon (10)",
        'voa_25' => "Vault of Archavon (25)",
    ];
}