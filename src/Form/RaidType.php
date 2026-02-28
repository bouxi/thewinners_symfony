<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RaidEvent; // adapte le namespace/nom
use App\Service\RaidData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RaidType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Symfony attend: 'Label' => 'value'
        $choices = array_flip(RaidData::CHOICES);

        $builder
            ->add('raidKey', ChoiceType::class, [
                'label' => 'Instance',
                'required' => true,
                'choices' => $choices,
                'placeholder' => '— Choisir un raid —',
            ]);

        // + tes autres champs (date, heure, commentaire, etc.)
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RaidEvent::class,
        ]);
    }
}