<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\GuideCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GuideCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var GuideCategory|null $currentCategory */
        $currentCategory = $options['current_category'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex : Démoniste, ICC, Alchimie...',
                ],
            ])

            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => [
                    'placeholder' => 'ex : demoniste, icc, alchimie...',
                ],
                'help' => 'Doit être unique. Utilisé dans l’URL publique.',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Petite description de la catégorie...',
                ],
            ])

            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
                'empty_data' => '0',
                'help' => 'Plus la valeur est petite, plus la catégorie remonte dans l’affichage.',
            ])

            ->add('icon', TextType::class, [
                'label' => 'Icône',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex : fa-solid fa-book, ou un identifiant interne...',
                ],
            ])

            ->add('parent', EntityType::class, [
                'class' => GuideCategory::class,
                'choice_label' => static function (GuideCategory $category): string {
                    return $category->getName();
                },
                'label' => 'Catégorie parente',
                'required' => false,
                'placeholder' => '— Aucune (catégorie racine) —',
                'query_builder' => static function (\App\Repository\GuideCategoryRepository $repository) {
                    return $repository->createQueryBuilder('gc')
                        ->orderBy('gc.position', 'ASC')
                        ->addOrderBy('gc.name', 'ASC');
                },
                'help' => 'Laisser vide pour créer une catégorie racine.',
            ])

            ->add('isActive', CheckboxType::class, [
                'label' => 'Catégorie active',
                'required' => false,
            ])
        ;

        /**
         * Bonus simple de sécurité métier :
         * lors de l’édition, on pourrait exclure la catégorie elle-même de la liste des parents.
         * Pour garder la première version simple, on ne le fait pas encore ici,
         * mais on le contrôlera côté contrôleur.
         */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GuideCategory::class,
            'current_category' => null,
        ]);

        $resolver->setAllowedTypes('current_category', ['null', GuideCategory::class]);
    }
}