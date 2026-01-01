<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RaidEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RaidEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])

            // Début / fin : HTML5 datetime-local (simple)
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
                'attr' => ['rows' => 4],
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
