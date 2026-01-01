<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire d'inscription d'un nouvel utilisateur.
 * On y saisit : email, pseudo, mot de passe, CGU éventuelles.
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email (identifiant de connexion)
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de renseigner une adresse e-mail.'),
                    new Assert\Email(message: 'Merci de saisir une adresse e-mail valide.'),
                    new Assert\Length(max: 180),
                ],
            ])

            // Pseudo affiché sur le site
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de choisir un pseudo.'),
                    new Assert\Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le pseudo ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])

            // Mot de passe "en clair" (non mappé dans l'entité directement)
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // on ne l'enregistre pas directement dans l'entité
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de choisir un mot de passe.'),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ),
                ],
            ])

            // Exemple de case à cocher "conditions d'utilisation"
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions d\'utilisation',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(message: 'Vous devez accepter les conditions pour vous inscrire.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // On lie ce formulaire à l'entité User
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => false,
        ]);
    }
}
