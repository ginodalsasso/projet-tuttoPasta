<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security){
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $user = $this->security->getUser();
            
            if($user && $user->isGoogleUser() == false){
                $builder->add('email', EmailType::class,[
                    'attr' => [
                        'class' => 'data'
                    ],
                ]);
            };

            $builder
            ->add('username', TextType::class,[
                'label' => "Pseudo",
                'attr' => [
                    'class' => 'data'
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
