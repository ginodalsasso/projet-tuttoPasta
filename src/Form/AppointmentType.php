<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ]),

                ]
            ])
            ->add('email', EmailType::class,[
                'constraints' => [
                    new Email([
                        'message' => "L\'adresse email doit être au format valide. Exemple : exemple@domaine.com.",
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                ]])
            ->add('message', TextareaType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre commentaire doit contenir au minimum {{ limit }} caractères',
                    ]),
                ]
            ])
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                // 'choice_label' => 'serviceName',
                'multiple' => true,
                'expanded' => true
            ])
            // ->add('serviceId', HiddenType::class, [
            //     'mapped' => false
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
