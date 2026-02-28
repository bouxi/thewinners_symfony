<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RaidEvent;
use App\Service\RaidCompositionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RaidEventType extends AbstractType
{
    public function __construct(
        private readonly RaidCompositionService $raidComp
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ✅ Instance (dropdown)
            ->add('raidKey', ChoiceType::class, [
                'label' => 'Instance',
                'choices' => $this->raidComp->getRaidChoices(),
                'placeholder' => '— Choisir un raid —',
                'required' => true,
            ])

            // ✅ Titre optionnel (nom de soirée / objectif)
            ->add('title', TextType::class, [
                'label' => 'Titre (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: ICC tryhard / Alt run / Progress…',
                ],
            ])

            ->add('startsAt', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description / consignes',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Ex: Discord requis, strat, compo, liens, etc.'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RaidEvent::class,
            'translation_domain' => false,
        ]);
    }
}