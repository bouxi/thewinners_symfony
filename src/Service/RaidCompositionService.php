<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RaidRole;
use App\Entity\RaidEvent;
use App\Entity\RaidSignup;

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
     * - rec: compo recommandée [tanks, heals, dps]
     */
    private const RAIDS = [
        // ===== Icecrown Citadel =====
        'icc10' => ['label' => 'ICC 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'icc25' => ['label' => 'ICC 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // Ruby Sanctum
        'rs10' => ['label' => 'Ruby Sanctum 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'rs25' => ['label' => 'Ruby Sanctum 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // ===== Ulduar =====
        'ulduar10' => ['label' => 'Ulduar 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'ulduar25' => ['label' => 'Ulduar 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // ===== Naxxramas =====
        'naxx10' => ['label' => 'Naxxramas 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'naxx25' => ['label' => 'Naxxramas 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // ===== Trial of the Crusader (ToC) =====
        'toc10' => ['label' => 'ToC 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'toc25' => ['label' => 'ToC 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // ===== Vault of Archavon (VoA) =====
        'voa10' => ['label' => 'VoA 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'voa25' => ['label' => 'VoA 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],

        // ===== Onyxia =====
        'ony10' => ['label' => 'Onyxia 10', 'size' => 10, 'rec' => ['tanks' => 2, 'heals' => 2, 'dps' => 6]],
        'ony25' => ['label' => 'Onyxia 25', 'size' => 25, 'rec' => ['tanks' => 2, 'heals' => 5, 'dps' => 18]],
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

    /**
     * ✅ Label unique
     */
    public function getLabel(string $raidKey): string
    {
        return self::RAIDS[$raidKey]['label'] ?? $raidKey;
    }

    /**
     * ✅ Compo recommandée depuis raidKey
     * Retour: ['tanks'=>2,'heals'=>2,'dps'=>6] (valeurs fallback safe)
     */
    public function getRecommendedComp(string $raidKey): array
    {
        return self::RAIDS[$raidKey]['rec'] ?? ['tanks' => 2, 'heals' => 2, 'dps' => 6];
    }

    /**
     * ✅ Taille du raid (10/25) si connue
     */
    public function getSize(string $raidKey): ?int
    {
        return self::RAIDS[$raidKey]['size'] ?? null;
    }

    /**
     * ✅ Helper "Mythic+" : compte le roster actuel (inscriptions)
     *
     * @param RaidSignup[] $signups
     * @return array{tank:int,heal:int,dps:int,total:int}
     */
    public function countRoles(array $signups): array
    {
        $tank = 0;
        $heal = 0;
        $dps  = 0;

        foreach ($signups as $s) {
            $role = $s->getRole(); // RaidRole enum normalement
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
     * ✅ Helper "Mythic+" : calcule ce qu'il manque / ce qui dépasse
     *
     * @param RaidSignup[] $signups
     * @return array{
     *   recommended: array{tanks:int,heals:int,dps:int},
     *   current: array{tank:int,heal:int,dps:int,total:int},
     *   missing: array{tank:int,heal:int,dps:int},
     *   extra: array{tank:int,heal:int,dps:int}
     * }
     */
    public function getCompositionStatus(string $raidKey, array $signups): array
    {
        $rec = $this->getRecommendedComp($raidKey);
        $cur = $this->countRoles($signups);

        $missing = [
            'tank' => max(0, $rec['tanks'] - $cur['tank']),
            'heal' => max(0, $rec['heals'] - $cur['heal']),
            'dps'  => max(0, $rec['dps'] - $cur['dps']),
        ];

        $extra = [
            'tank' => max(0, $cur['tank'] - $rec['tanks']),
            'heal' => max(0, $cur['heal'] - $rec['heals']),
            'dps'  => max(0, $cur['dps'] - $rec['dps']),
        ];

        return [
            'recommended' => $rec,
            'current' => $cur,
            'missing' => $missing,
            'extra' => $extra,
        ];
    }
}