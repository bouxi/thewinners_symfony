<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Personnage;
use App\Form\EventSubscriber\PersonnageSpecSubscriber;
use App\Service\WowData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $classChoices = [];
        foreach (array_keys(WowData::CLASSES) as $class) {
            $classChoices[$class] = $class;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du personnage',
                'required' => true,
                'attr' => [
                    'maxlength' => 50,
                ],
            ])
            ->add('class', ChoiceType::class, [
                'label' => 'Classe',
                'choices' => $classChoices,
                'placeholder' => '— Choisir —',
                'required' => true,
            ])
            // Champ vide au départ (sera reconstruit par le Subscriber)
            ->add('spec', ChoiceType::class, [
                'label' => 'Spécialisation',
                'choices' => [],
                'placeholder' => '— Choisir d’abord une classe —',
                'required' => true,
                'disabled' => true,
            ])
        ;

        // 🔥 Ajout du Subscriber PRO
        $builder->addEventSubscriber(new PersonnageSpecSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
        ]);
    }
}