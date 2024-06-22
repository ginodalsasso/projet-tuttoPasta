<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,[
                'label' => "Pseudo",
                'attr' => [
                    'class' => 'data'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un Pseudo',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9._-]{3,15}$/',
                        'message' => 'Le nom d\'utilisateur doit contenir entre 3 et 15 caractères et ne peut contenir que des lettres, des chiffres, des tirets (-) et des underscores (_) ou des points.'
                    ]),
                ],
            ])
            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'data'
                ],
                'constraints' => [
                    new Email([
                        'message' => "L\'adresse email doit être au format valide. Exemple : exemple@domaine.com.",
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les termes et conditions',
                'mapped' => false,
                'attr' => [
                    'class' => 'data'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Erreur de saisie des mots de passes',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-group data'
                ],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez votre mot de passe'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{13,}$/', 
                        'message' => 'Votre mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial et faire au moins 12 caractères.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
