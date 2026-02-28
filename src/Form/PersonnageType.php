<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Personnage;
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
        // ✅ Classes
        $classChoices = [];
        foreach (array_keys(WowData::CLASSES) as $className) {
            $classChoices[$className] = $className;
        }

        // ✅ Specs par défaut (sera remplacé dynamiquement par JS)
        $firstClass = array_key_first(WowData::CLASSES);
        $specChoices = [];
        foreach (WowData::CLASSES[$firstClass] as $spec) {
            $specChoices[$spec] = $spec;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du personnage',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Bouxî',
                ],
            ])

            // ⚠️ champ entity = "class"
            ->add('class', ChoiceType::class, [
                'label' => 'Classe',
                'required' => true,
                'choices' => $classChoices,
                'placeholder' => '— Choisir —',
            ])

            // ⚠️ champ entity = "spec"
            ->add('spec', ChoiceType::class, [
                'label' => 'Spécialisation',
                'required' => true,
                'choices' => $specChoices,
                'placeholder' => '— Choisir —',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
        ]);
    }
}