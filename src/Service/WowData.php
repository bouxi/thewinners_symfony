<?php

declare(strict_types=1);

namespace App\Service;

final class WowData
{
    /**
     * Mapping officiel WotLK 3.3.5
     */
    public const CLASSES = [

        'Guerrier' => ['Armes', 'Fureur', 'Protection'],
        'Paladin' => ['Sacré', 'Protection', 'Vindicte'],
        'Chasseur' => ['Précision', 'Survie', 'Maîtrise des bêtes'],
        'Voleur' => ['Assassinat', 'Combat', 'Finesse'],
        'Prêtre' => ['Discipline', 'Sacré', 'Ombre'],
        'Chaman' => ['Élémentaire', 'Amélioration', 'Restauration'],
        'Mage' => ['Arcanes', 'Feu', 'Givre'],
        'Démoniste' => ['Affliction', 'Démonologie', 'Destruction'],
        'Druide' => ['Équilibre', 'Feral (DPS)', 'Feral (Tank)', 'Restauration'],
        'Chevalier de la mort' => ['Blood', 'Frost', 'Unholy'],
    ];
}