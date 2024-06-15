<?php

namespace App\Form;

use DateTimeInterface;
use App\Entity\Service;
use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
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
                'label' => "Votre nom",
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
                ],
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ]),

                ]
            ])
            ->add('email', EmailType::class,[
                'label' => "Votre e-mail",
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
                ],
                'constraints' => [
                    new Email([
                        'message' => "L'adresse email doit être au format valide.",
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                ]])
            ->add('message', TextareaType::class, [
                'label' => "Notes supplémentaires",
                'attr' => [
                    'autocomplete' => 'off',
                    "class" => "data"
                ],
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez entrer un message',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre commentaire doit contenir au minimum {{ limit }} caractères',
                    ]),
                ]
            ])
            ->add('startDate', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'constraints'=>[
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Veuillez séléctionner une date dans le présent !',
                    ]),
                ],

            ])
            // ->add('services', EntityType::class, [
            //     'class' => Service::class,
            //     'choice_label' => 'serviceName', 
            //     'multiple' => true,
            //     'expanded' => true, // true pour checkboxes
            // ])
            // ->add('services', CollectionType::class, [
            //     'entry_type' => ServiceType::class,
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            //     'entry_options' => ['label' => false],
            // ])
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
