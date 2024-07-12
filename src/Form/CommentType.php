<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentContent', TextareaType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Un commentaire ne peut être vide !',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre commentaire doit contenir au minimum {{ limit }} caractères',
                    ]),
                ],
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre message',
                    'autocomplete' => 'off',
                    ]
            ]);    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}

