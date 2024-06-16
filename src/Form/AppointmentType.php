<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom",
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => "E-mail",
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
            ]])
            ->add('message', TextareaType::class, [
                'label' => "Notes supplémentaires",
                'attr' => [
                    'autocomplete' => 'off',
                    "class" => "data"
                ]
            ])
            ->add('startDate', DateType::class, [
                'label' => "Rendez-vous",
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'data'
                ],
                'constraints'=>[
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Veuillez séléctionner une date dans le présent !',
                    ]),
                ],

            ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'serviceName', 
                'multiple' => true,
                'expanded' => true, // true pour checkboxes
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
            ])        
        ;
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
