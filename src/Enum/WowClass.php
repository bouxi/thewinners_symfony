<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum des classes WotLK.
 * ⚠️ Important : aucun echo, aucun texte, aucun caractère avant <?php
 */
enum WowClass: string
{
    case WARRIOR = 'Guerrier';
    case PALADIN = 'Paladin';
    case HUNTER = 'Chasseur';
    case ROGUE = 'Voleur';
    case PRIEST = 'Prêtre';
    case DEATH_KNIGHT = 'Chevalier de la mort';
    case SHAMAN = 'Chaman';
    case MAGE = 'Mage';
    case WARLOCK = 'Démoniste';
    case DRUID = 'Druide';
}
