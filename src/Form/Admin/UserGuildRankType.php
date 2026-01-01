<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Enum\GuildRank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form admin : édition du grade guilde d'un user.
 */
final class UserGuildRankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('guildRank', EnumType::class, [
            'class' => GuildRank::class,
            'label' => 'Grade de guilde',
            'choice_label' => static fn (GuildRank $rank) => $rank->label(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
