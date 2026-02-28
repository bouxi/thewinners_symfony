<?php
// src/Service/WowClassIconResolver.php
namespace App\Service;

final class WowClassIconResolver
{
    public static function resolve(?string $class): string
    {
        return match (strtolower((string) $class)) {
            'guerrier', 'warrior' => 'images/classes/warrior.png',
            'paladin' => 'images/classes/paladin.png',
            'dk', 'death knight' => 'images/classes/death_knight.png',
            'mage' => 'images/classes/mage.png',
            'prêtre', 'priest' => 'images/classes/priest.png',
            'voleur', 'rogue' => 'images/classes/rogue.png',
            'druide', 'druid' => 'images/classes/druid.png',
            'chasseur', 'hunter' => 'images/classes/hunter.png',
            'démoniste', 'warlock' => 'images/classes/warlock.png',
            'chaman', 'shaman' => 'images/classes/shaman.png',
            default => 'images/classes/unknown.png',
        };
    }
}
