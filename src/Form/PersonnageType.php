<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Personnage;
use App\Enum\CombatRole;
use App\Service\WowData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = (bool) ($options['admin'] ?? false);

        // ✅ Choices "Classe"
        $classChoices = [];
        foreach (\array_keys(WowData::CLASSES) as $className) {
            $classChoices[$className] = $className;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du personnage',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Bouxî',
                    'maxlength' => 50,
                ],
            ])
            ->add('class', ChoiceType::class, [
                'label' => 'Classe',
                'required' => true,
                'choices' => $classChoices,
                'placeholder' => '— Choisir —',
            ])
            // ✅ spec: sera reconstruite selon la classe
            ->add('spec', ChoiceType::class, [
                'label' => 'Spécialisation',
                'required' => true,
                'choices' => [],
                'placeholder' => '— Choisir d’abord une classe —',
                'disabled' => true,
            ])
        ;

        // ✅ Champs admin (editable côté admin)
        if ($isAdmin) {
            $builder
                ->add('combatRole', EnumType::class, [
                    'label' => 'Rôle (combat)',
                    'class' => CombatRole::class,
                    'required' => false,
                    'placeholder' => '— Choisir —',
                    'choice_label' => fn (?CombatRole $role) => $role?->label() ?? '',
                ])
                ->add('profession1', TextType::class, [
                    'label' => 'Profession 1',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 50,
                        'placeholder' => 'Ex: Alchimie',
                    ],
                ])
                ->add('profession2', TextType::class, [
                    'label' => 'Profession 2',
                    'required' => false,
                    'attr' => [
                        'maxlength' => 50,
                        'placeholder' => 'Ex: Herboristerie',
                    ],
                ])
                ->add('isMain', CheckboxType::class, [
                    'label' => 'Personnage principal',
                    'required' => false,
                ])
                // Optionnel : si tu veux gérer la visibilité côté admin
                ->add('isPublic', CheckboxType::class, [
                    'label' => 'Visible publiquement',
                    'required' => false,
                ])
            ;
        }

        /**
         * ✅ PRE_SET_DATA : au chargement, on remplit spec selon class existante
         */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data instanceof Personnage) {
                return;
            }

            $className = $data->getClass();
            $this->rebuildSpecField($form, $className);
        });

        /**
         * ✅ PRE_SUBMIT : au submit, on remplit spec selon class envoyée
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $submitted = $event->getData();
            $form = $event->getForm();

            $className = \is_array($submitted) ? ($submitted['class'] ?? null) : null;
            $this->rebuildSpecField($form, \is_string($className) ? $className : null);
        });
    }

    private function rebuildSpecField($form, ?string $className): void
    {
        $specChoices = [];

        if ($className && isset(WowData::CLASSES[$className])) {
            foreach (WowData::CLASSES[$className] as $spec) {
                $specChoices[$spec] = $spec;
            }
        }

        $form->add('spec', ChoiceType::class, [
            'label' => 'Spécialisation',
            'required' => true,
            'choices' => $specChoices,
            'placeholder' => $className ? '— Choisir —' : '— Choisir d’abord une classe —',
            'disabled' => $className ? false : true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
            // ✅ option “pro” réutilisable
            'admin' => false,
        ]);

        $resolver->setAllowedTypes('admin', 'bool');
    }
}