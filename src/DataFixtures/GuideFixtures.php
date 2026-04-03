<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Guide;
use App\Entity\GuideCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Injecte quelques guides factices publiés pour tester le module.
 */
final class GuideFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $guides = [
            [
                'title' => 'Guide Démoniste Affliction PvE 3.3.5',
                'slug' => 'guide-demoniste-affliction-pve-3-3-5',
                'excerpt' => 'Les bases pour jouer Démoniste Affliction en PvE sur WoW 3.3.5.',
                'content' => "Le Démoniste Affliction repose sur la gestion efficace des dégâts sur la durée.\n\nPriorisez vos dots, gardez une bonne uptime sur Corruption, Affliction instable et Agonie, puis complétez avec Trait de l’ombre selon le contexte.\n\nTravaillez aussi votre placement, votre gestion de menace et vos consommables pour optimiser votre DPS en raid.",
                'reference' => 'guide_category_affliction',
                'publishedAt' => '-12 days',
            ],
            [
                'title' => 'Guide Paladin Vindicte PvE 3.3.5',
                'slug' => 'guide-paladin-vindicte-pve-3-3-5',
                'excerpt' => 'Rotation, priorités et optimisation du Paladin Vindicte en environnement PvE.',
                'content' => "Le Paladin Vindicte en 3.3.5 s’appuie sur un cycle de priorités plus que sur une rotation figée.\n\nApprenez à utiliser Jugement, Tempête divine, Exorcisme et Consécration au bon moment.\n\nUne bonne maîtrise du placement et du burst sur les fenêtres importantes fera une vraie différence en raid.",
                'reference' => 'guide_category_vindicte',
                'publishedAt' => '-10 days',
            ],
            [
                'title' => 'Guide Prêtre Discipline PvE 3.3.5',
                'slug' => 'guide-pretre-discipline-pve-3-3-5',
                'excerpt' => 'Comprendre le rôle du Prêtre Discipline en raid et optimiser ses boucliers.',
                'content' => "Le Prêtre Discipline est l’un des piliers du raid en WotLK.\n\nSon rôle ne se limite pas à remonter des points de vie : il anticipe les dégâts grâce aux boucliers et sécurise les phases critiques.\n\nLa gestion du mana, du timing et de l’anticipation est centrale pour exploiter pleinement cette spécialisation.",
                'reference' => 'guide_category_discipline',
                'publishedAt' => '-8 days',
            ],
            [
                'title' => 'ICC 10 normal : stratégie complète',
                'slug' => 'icc-10-normal-strategie-complete',
                'excerpt' => 'Aperçu global des boss et des points de vigilance pour ICC 10 normal.',
                'content' => "Ce guide présente une vue d’ensemble de la Citadelle de la Couronne de glace en mode 10 normal.\n\nTu y trouveras les grandes mécaniques de chaque affrontement, les compositions recommandées et les erreurs classiques à éviter.\n\nL’objectif est d’aider la guilde à structurer ses premières sorties ou à consolider son clean hebdomadaire.",
                'reference' => 'guide_category_icc_10_normal',
                'publishedAt' => '-6 days',
            ],
            [
                'title' => 'ICC 25 héroïque : préparation et exigences',
                'slug' => 'icc-25-heroique-preparation-et-exigences',
                'excerpt' => 'Préparer un roster solide et comprendre les attentes pour ICC 25 héroïque.',
                'content' => "Le mode 25 héroïque demande rigueur, discipline et excellente préparation.\n\nCe guide aborde les prérequis du roster, la qualité d’exécution attendue, ainsi que les points d’attention les plus courants sur les boss majeurs.\n\nIl sert de base de travail pour structurer les soirées de progression.",
                'reference' => 'guide_category_icc_25_heroique',
                'publishedAt' => '-4 days',
            ],
            [
                'title' => 'Fosse de Saron : guide rapide',
                'slug' => 'fosse-de-saron-guide-rapide',
                'excerpt' => 'Un guide simple pour comprendre les points clés de Fosse de Saron.',
                'content' => "Fosse de Saron est un donjon important de la fin de WotLK.\n\nMême si ses mécaniques sont globalement accessibles, certains packs et boss peuvent rapidement punir un groupe mal préparé.\n\nCe guide résume les points à connaître pour sécuriser le run, optimiser le rythme et éviter les erreurs fréquentes.",
                'reference' => 'guide_category_fosse_de_saron',
                'publishedAt' => '-3 days',
            ],
            [
                'title' => 'Alchimie 3.3.5 : bien débuter',
                'slug' => 'alchimie-3-3-5-bien-debuter',
                'excerpt' => 'Conseils de progression et intérêt du métier Alchimie sur WoW 3.3.5.',
                'content' => "L’Alchimie est un métier très rentable et particulièrement utile en environnement PvE.\n\nEntre les flacons, les potions et certaines spécialisations, elle apporte une vraie valeur ajoutée au joueur comme à la guilde.\n\nCe guide présente les bases pour bien démarrer, optimiser la progression et cibler les recettes utiles.",
                'reference' => 'guide_category_alchimie',
                'publishedAt' => '-1 day',
            ],
        ];

        foreach ($guides as $item) {
            /** @var GuideCategory $category */
            $category = $this->getReference($item['reference'], GuideCategory::class);

            $guide = new Guide();
            $guide
                ->setTitle($item['title'])
                ->setSlug($item['slug'])
                ->setExcerpt($item['excerpt'])
                ->setContent($item['content'])
                ->setCategory($category)
                ->setIsPublished(true)
                ->setPublishedAt(new \DateTimeImmutable($item['publishedAt']));

            $manager->persist($guide);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GuideCategoryFixtures::class,
        ];
    }
}