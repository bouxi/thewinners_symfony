<?php

// src/Enum/CombatRole.php
namespace App\Enum;

enum CombatRole: string
{
    case TANK = 'tank';
    case HEAL = 'heal';
    case DPS  = 'dps';

    public function label(): string
    {
        return match ($this) {
            self::TANK => 'Tank',
            self::HEAL => 'Heal',
            self::DPS  => 'DPS',
        };
    }
}
