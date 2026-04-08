<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Guide;
use App\Entity\GuideCategory;
use App\Repository\GuideCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GuideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Ex : Guide Démoniste Affliction PvE 3.3.5',
                ],
            ])

            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => [
                    'placeholder' => 'ex : guide-demoniste-affliction-pve-3-3-5',
                ],
                'help' => 'Doit être unique. Utilisé dans l’URL publique.',
            ])

            ->add('excerpt', TextareaType::class, [
                'label' => 'Extrait',
                'required' => false, 
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Petit résumé affiché dans les listes et cartes.',
                ],
            ])

            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 14,
                    'placeholder' => 'Rédige ici le contenu complet du guide...',
                ],
                'help' => 'Pour l’instant, le contenu est saisi en texte brut. Un éditeur riche pourra être ajouté ensuite.',
            ])

            ->add('featuredImage', TextType::class, [
                'label' => 'Image mise en avant',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex : uploads/guides/mon-image.jpg',
                ],
                'help' => 'Chemin ou identifiant de l’image. L’upload réel pourra être ajouté plus tard.',
            ])

            ->add('category', EntityType::class, [
                'class' => GuideCategory::class,
                'choice_label' => static fn (GuideCategory $category): string => $category->getBreadcrumbName(),
                'label' => 'Catégorie',
                'placeholder' => '— Choisir une catégorie —',
                'query_builder' => static function (GuideCategoryRepository $repository) {
                    return $repository->createQueryBuilder('gc')
                        ->andWhere('gc.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('gc.position', 'ASC')
                        ->addOrderBy('gc.name', 'ASC');
                },
                'help' => 'Choisis la catégorie la plus précise possible pour bien ranger le guide.',
            ])

            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier ce guide',
                'required' => false,
            ])

            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Date de publication',
                'required' => false,
                'widget' => 'single_text',
                'help' => 'Laisser vide pour que la date soit définie automatiquement lors de la publication.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Guide::class,
        ]);
    }
}