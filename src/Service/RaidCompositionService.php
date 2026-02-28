<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RaidRole;

/**
 * Service "source de vérité" pour :
 * - la liste des raids WotLK (dropdown)
 * - labels (affichage calendrier)
 * - compos recommandées (10/25)
 * - helpers pour calculer ce qu'il manque selon les inscriptions
 *
 * ✅ Pro: toute la logique de contenu est isolée ici.
 *    Le Controller / Twig ne fait que consommer.
 */
final class RaidCompositionService
{
    /**
     * ✅ Catalogue complet WotLK (raidKey => données)
     * - label: affichage propre
     * - size: 10 ou 25
     * - targets: compo recommandée [tank, heal, dps]
     */
    private const RAIDS = [
        // ===== Icecrown Citadel =====
        'icc10'  => ['label' => 'ICC 10',  'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'icc25'  => ['label' => 'ICC 25',  'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Ruby Sanctum =====
        'rs10'   => ['label' => 'Ruby Sanctum 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'rs25'   => ['label' => 'Ruby Sanctum 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Ulduar =====
        'ulduar10' => ['label' => 'Ulduar 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'ulduar25' => ['label' => 'Ulduar 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Naxxramas =====
        'naxx10' => ['label' => 'Naxxramas 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'naxx25' => ['label' => 'Naxxramas 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Trial of the Crusader =====
        'toc10'  => ['label' => 'ToC 10',  'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'toc25'  => ['label' => 'ToC 25',  'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],
        // Bonus (souvent utilisé sur 3.3.5)
        'togc10' => ['label' => 'ToGC 10 (HM)', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'togc25' => ['label' => 'ToGC 25 (HM)', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Vault of Archavon =====
        'voa10'  => ['label' => 'VoA 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'voa25'  => ['label' => 'VoA 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Onyxia =====
        'ony10'  => ['label' => 'Onyxia 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'ony25'  => ['label' => 'Onyxia 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Obsidian Sanctum (Sartharion) =====
        'os10'   => ['label' => 'Obsidian Sanctum 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'os25'   => ['label' => 'Obsidian Sanctum 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],

        // ===== Eye of Eternity (Malygos) =====
        'eoe10'  => ['label' => 'Oeil de l\'éternité 10', 'size' => 10, 'targets' => ['tank' => 2, 'heal' => 2, 'dps' => 6]],
        'eoe25'  => ['label' => 'Oeil de l\'éternité 25', 'size' => 25, 'targets' => ['tank' => 2, 'heal' => 5, 'dps' => 18]],
    ];

    /**
     * ✅ Pour ton dropdown Symfony (ChoiceType)
     * Retour: ["ICC 10" => "icc10", ...]
     */
    public function getRaidChoices(): array
    {
        $choices = [];
        foreach (self::RAIDS as $key => $data) {
            $choices[$data['label']] = $key;
        }

        // ✅ tri par label (optionnel mais agréable)
        ksort($choices);

        return $choices;
    }

    /**
     * ✅ Pour Twig / calendrier : raidKey => label
     * Retour: ["icc10" => "ICC 10", ...]
     */
    public function getRaidLabels(): array
    {
        $labels = [];
        foreach (self::RAIDS as $key => $data) {
            $labels[$key] = $data['label'];
        }
        return $labels;
    }

    public function getLabel(string $raidKey): string
    {
        return self::RAIDS[$raidKey]['label'] ?? $raidKey;
    }

    /**
     * ✅ Alias "pro" pour le controller : targets (tank/heal/dps)
     * Retour: ['tank'=>2,'heal'=>2,'dps'=>6]
     */
    public function getTargets(string $raidKey): array
    {
        return self::RAIDS[$raidKey]['targets']
            ?? ['tank' => 2, 'heal' => 2, 'dps' => 6];
    }

    /**
     * ✅ Taille du raid (10/25) si connue
     */
    public function getSize(string $raidKey): ?int
    {
        return self::RAIDS[$raidKey]['size'] ?? null;
    }

    /**
     * ✅ Compte le roster actuel (inscriptions)
     *
     * @param iterable $signups (RaidSignup[])
     * @return array{tank:int,heal:int,dps:int,total:int}
     */
    public function countRoles(iterable $signups): array
    {
        $tank = 0;
        $heal = 0;
        $dps  = 0;

        foreach ($signups as $s) {
            $role = $s->getRole(); // RaidRole enum
            if ($role === RaidRole::TANK) {
                $tank++;
            } elseif ($role === RaidRole::HEAL) {
                $heal++;
            } else {
                $dps++;
            }
        }

        return [
            'tank' => $tank,
            'heal' => $heal,
            'dps' => $dps,
            'total' => $tank + $heal + $dps,
        ];
    }

    /**
     * ✅ Calcule ce qu'il manque / ce qui dépasse
     *
     * @param iterable $signups (RaidSignup[])
     * @return array{
     *   targets: array{tank:int,heal:int,dps:int},
     *   current: array{tank:int,heal:int,dps:int,total:int},
     *   missing: array{tank:int,heal:int,dps:int},
     *   extra: array{tank:int,heal:int,dps:int}
     * }
     */
    public function getCompositionStatus(string $raidKey, iterable $signups): array
    {
        $targets = $this->getTargets($raidKey);
        $cur = $this->countRoles($signups);

        $missing = [
            'tank' => max(0, $targets['tank'] - $cur['tank']),
            'heal' => max(0, $targets['heal'] - $cur['heal']),
            'dps'  => max(0, $targets['dps']  - $cur['dps']),
        ];

        $extra = [
            'tank' => max(0, $cur['tank'] - $targets['tank']),
            'heal' => max(0, $cur['heal'] - $targets['heal']),
            'dps'  => max(0, $cur['dps']  - $targets['dps']),
        ];

        return [
            'targets' => $targets,
            'current' => $cur,
            'missing' => $missing,
            'extra' => $extra,
        ];
    }
}