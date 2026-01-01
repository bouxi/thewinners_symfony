<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'attr' => [
                    'maxlength' => 50,
                    'autocomplete' => 'nickname',
                ],
            ])

            // Champ non mappé : on upload et on setAvatar() nous-mêmes dans le controller
            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar (png/jpg/webp) — 2 Mo max',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/png,image/jpeg,image/webp',
                ],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/png', 'image/jpeg', 'image/webp'],
                        maxSizeMessage: 'Taille maximale : 2 Mo.',
                        mimeTypesMessage: 'Formats autorisés : PNG, JPG/JPEG, WEBP.'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => false,
        ]);
    }
}
