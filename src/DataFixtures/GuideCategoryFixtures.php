<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\GuideCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Injecte l'arborescence de base des catégories de guides WoW 3.3.5.
 */
final class GuideCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $position = 0;

        /*
         * =========================
         * Catégories racines
         * =========================
         */
        $classes = $this->createCategory(
            name: 'Classes',
            slug: 'classes',
            description: 'Guides des classes et spécialisations pour WoW 3.3.5.',
            position: $position++
        );
        $manager->persist($classes);

        $raids = $this->createCategory(
            name: 'Raids',
            slug: 'raids',
            description: 'Stratégies, compositions et conseils pour les raids WotLK.',
            position: $position++
        );
        $manager->persist($raids);

        $dungeons = $this->createCategory(
            name: 'Donjons',
            slug: 'donjons',
            description: 'Guides des donjons héroïques et classiques de WotLK.',
            position: $position++
        );
        $manager->persist($dungeons);

        $professions = $this->createCategory(
            name: 'Métiers',
            slug: 'metiers',
            description: 'Guides des métiers principaux pour progresser efficacement.',
            position: $position++
        );
        $manager->persist($professions);

        /*
         * =========================
         * Classes
         * =========================
         */
        $this->createClassTree($manager, $classes, 'Démoniste', 'demoniste', [
            'Affliction' => 'affliction',
            'Démonologie' => 'demonologie',
            'Destruction' => 'destruction',
        ]);

        $this->createClassTree($manager, $classes, 'Voleur', 'voleur', [
            'Assassinat' => 'assassinat',
            'Combat' => 'combat',
            'Finesse' => 'finesse',
        ]);

        $this->createClassTree($manager, $classes, 'Paladin', 'paladin', [
            'Sacré' => 'sacre-paladin',
            'Protection' => 'protection-paladin',
            'Vindicte' => 'vindicte',
        ]);

        $this->createClassTree($manager, $classes, 'Guerrier', 'guerrier', [
            'Armes' => 'armes',
            'Fureur' => 'fureur',
            'Protection' => 'protection-guerrier',
        ]);

        $this->createClassTree($manager, $classes, 'Mage', 'mage', [
            'Arcane' => 'arcane',
            'Feu' => 'feu',
            'Givre' => 'givre',
        ]);

        $this->createClassTree($manager, $classes, 'Chasseur', 'chasseur', [
            'Maîtrise des bêtes' => 'maitrise-des-betes',
            'Précision' => 'precision',
            'Survie' => 'survie',
        ]);

        $this->createClassTree($manager, $classes, 'Prêtre', 'pretre', [
            'Discipline' => 'discipline',
            'Sacré' => 'sacre-pretre',
            'Ombre' => 'ombre',
        ]);

        $this->createClassTree($manager, $classes, 'Druide', 'druide', [
            'Équilibre' => 'equilibre',
            'Farouche' => 'farouche',
            'Restauration' => 'restauration-druide',
        ]);

        $this->createClassTree($manager, $classes, 'Chaman', 'chaman', [
            'Élémentaire' => 'elementaire',
            'Amélioration' => 'amelioration',
            'Restauration' => 'restauration-chaman',
        ]);

        $this->createClassTree($manager, $classes, 'Chevalier de la mort', 'chevalier-de-la-mort', [
            'Sang' => 'sang',
            'Givre' => 'givre-dk',
            'Impie' => 'impie',
        ]);

        /*
         * =========================
         * Raids
         * =========================
         */
        $this->createRaidTree($manager, $raids, 'ICC', 'icc', [
            '10 normal' => 'icc-10-normal',
            '25 normal' => 'icc-25-normal',
            '10 héroïque' => 'icc-10-heroique',
            '25 héroïque' => 'icc-25-heroique',
        ]);

        $this->createRaidTree($manager, $raids, 'Ulduar', 'ulduar', [
            '10 normal' => 'ulduar-10-normal',
            '25 normal' => 'ulduar-25-normal',
        ]);

        $this->createRaidTree($manager, $raids, 'Naxxramas', 'naxxramas', [
            '10 normal' => 'naxxramas-10-normal',
            '25 normal' => 'naxxramas-25-normal',
        ]);

        $this->createRaidTree($manager, $raids, 'Onyxia', 'onyxia', [
            '10 normal' => 'onyxia-10-normal',
            '25 normal' => 'onyxia-25-normal',
        ]);

        $this->createRaidTree($manager, $raids, 'Sanctum Rubis', 'sanctum-rubis', [
            '10 normal' => 'sanctum-rubis-10-normal',
            '25 normal' => 'sanctum-rubis-25-normal',
        ]);

        /*
         * =========================
         * Donjons
         * =========================
         */
        $this->createSimpleChild($manager, $dungeons, 'Fosse de Saron', 'fosse-de-saron', 0);
        $this->createSimpleChild($manager, $dungeons, 'Salle des Reflets', 'salle-des-reflets', 1);
        $this->createSimpleChild($manager, $dungeons, 'Donjon des âmes', 'donjon-des-ames', 2);
        $this->createSimpleChild($manager, $dungeons, 'Cime d’Utgarde', 'cime-dutgarde', 3);
        $this->createSimpleChild($manager, $dungeons, 'Gundrak', 'gundrak', 4);

        /*
         * =========================
         * Métiers
         * =========================
         */
        $this->createSimpleChild($manager, $professions, 'Alchimie', 'alchimie', 0);
        $this->createSimpleChild($manager, $professions, 'Couture', 'couture', 1);
        $this->createSimpleChild($manager, $professions, 'Forge', 'forge', 2);
        $this->createSimpleChild($manager, $professions, 'Enchantement', 'enchantement', 3);
        $this->createSimpleChild($manager, $professions, 'Herboristerie', 'herboristerie', 4);
        $this->createSimpleChild($manager, $professions, 'Joaillerie', 'joaillerie', 5);
        $this->createSimpleChild($manager, $professions, 'Travail du cuir', 'travail-du-cuir', 6);
        $this->createSimpleChild($manager, $professions, 'Minage', 'minage', 7);
        $this->createSimpleChild($manager, $professions, 'Dépeçage', 'depecage', 8);
        $this->createSimpleChild($manager, $professions, 'Calligraphie', 'calligraphie', 9);
        $this->createSimpleChild($manager, $professions, 'Ingénierie', 'ingenierie', 10);

        $manager->flush();
    }

    /**
     * Crée une catégorie simple.
     */
    private function createCategory(
        string $name,
        string $slug,
        ?string $description = null,
        int $position = 0,
        ?GuideCategory $parent = null
    ): GuideCategory {
        $category = new GuideCategory();
        $category
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($description)
            ->setPosition($position)
            ->setIsActive(true)
            ->setParent($parent);

        return $category;
    }

    /**
     * Crée une branche "classe > spécialisations".
     *
     * @param array<string, string> $specializations
     */
    private function createClassTree(
        ObjectManager $manager,
        GuideCategory $root,
        string $className,
        string $classSlug,
        array $specializations
    ): void {
        $classCategory = $this->createCategory(
            name: $className,
            slug: $classSlug,
            description: sprintf('Guides dédiés à la classe %s.', $className),
            position: 0,
            parent: $root
        );
        $manager->persist($classCategory);

        $position = 0;
        foreach ($specializations as $name => $slug) {
            $specialization = $this->createCategory(
                name: $name,
                slug: $slug,
                description: sprintf('Guides de spécialisation %s pour %s.', $name, $className),
                position: $position++,
                parent: $classCategory
            );
            $manager->persist($specialization);
        }
    }

    /**
     * Crée une branche "raid > modes".
     *
     * @param array<string, string> $modes
     */
    private function createRaidTree(
        ObjectManager $manager,
        GuideCategory $root,
        string $raidName,
        string $raidSlug,
        array $modes
    ): void {
        $raidCategory = $this->createCategory(
            name: $raidName,
            slug: $raidSlug,
            description: sprintf('Guides et stratégies pour %s.', $raidName),
            position: 0,
            parent: $root
        );
        $manager->persist($raidCategory);

        $position = 0;
        foreach ($modes as $name => $slug) {
            $modeCategory = $this->createCategory(
                name: $name,
                slug: $slug,
                description: sprintf('Guides %s pour %s.', $name, $raidName),
                position: $position++,
                parent: $raidCategory
            );
            $manager->persist($modeCategory);
        }
    }

    /**
     * Crée un enfant simple sous une catégorie parente.
     */
    private function createSimpleChild(
        ObjectManager $manager,
        GuideCategory $parent,
        string $name,
        string $slug,
        int $position
    ): void {
        $child = $this->createCategory(
            name: $name,
            slug: $slug,
            description: sprintf('Guides liés à %s.', $name),
            position: $position,
            parent: $parent
        );

        $manager->persist($child);
    }
}