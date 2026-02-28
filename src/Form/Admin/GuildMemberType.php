<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Enum\CombatRole;
use App\Enum\GuildRank;
use App\Service\WowData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GuildMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // WowData::CLASSES est normalement un tableau du style :
        // [
        //   "Guerrier" => ["Armes", "Fureur", "Protection"],
        //   "Paladin"  => ["Sacré", "Protection", "Vindicte"],
        //   ...
        // ]
        $classNames = array_keys(WowData::CLASSES);
        $classChoices = array_combine($classNames, $classNames); // ["Guerrier" => "Guerrier", ...]

        $builder
            // ✅ Le rang = le vrai “statut membre” (VISITOR, RECRUE, MEMBRE, etc.)
            ->add('guildRank', ChoiceType::class, [
                'required' => true,
                'label' => 'Grade de guilde',
                'choices' => GuildRank::cases(),
                'choice_value' => fn(?GuildRank $r) => $r?->value,
                'choice_label' => fn(GuildRank $r) => $r->label(),
                'placeholder' => '— Choisir —',
            ])

            ->add('characterName', TextType::class, [
                'required' => false,
                'label' => 'Nom du personnage',
            ])

            ->add('characterClass', ChoiceType::class, [
                'required' => false,
                'label' => 'Classe',
                'choices' => $classChoices,
                'placeholder' => '— Choisir —',
            ])

            // ✅ Simple pour l’instant : texte libre
            // (on pourra upgrader plus tard en ChoiceType dépendant de la classe)
            ->add('characterSpec', TextType::class, [
                'required' => false,
                'label' => 'Spécialisation',
                'help' => 'Ex: Protection, Sacré, Fureur…',
            ])

            ->add('combatRole', ChoiceType::class, [
                'required' => false,
                'label' => 'Rôle (Tank/Heal/DPS)',
                'choices' => CombatRole::cases(),
                'choice_value' => fn(?CombatRole $r) => $r?->value,
                'choice_label' => fn(CombatRole $r) => $r->label(),
                'placeholder' => '— Choisir —',
            ])

            ->add('isPublicMember', CheckboxType::class, [
                'required' => false,
                'label' => 'Visible publiquement sur /guild/members',
            ])
        ;
        // ❌ On a supprimé isGuildMember car il n’existe pas dans User
        // ✅ La “membreté” se déduit de guildRank != VISITOR
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
