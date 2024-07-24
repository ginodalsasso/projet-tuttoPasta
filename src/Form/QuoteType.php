<?php

namespace App\Form;

use App\Entity\Quote;
use App\Entity\Service;
use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $appointment = $options['appointment']; // Récupérer l'entité Appointment passée en option
        // $services = $appointment ? $appointment->getServices() : [];

        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence',
            ])
            ->add('customerName', TextType::class, [
                'label' => 'Nom du client',
            ])
            ->add('customerFirstName', TextType::class, [
                'label' => 'Prénom du client',
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email du client',
            ])
            // ->add('services', ChoiceType::class, [
            //     'choices' => $services,
            //     'choice_label' => function($service) {
            //         return $service->getServiceName();
            //     },
            //     'multiple' => true,
            //     'expanded' => true,
            //     'mapped' => false, // Ce champ n'est pas mappé directement à l'entité Quote
            //     'label' => 'Services',
            // ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'serviceName',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false, // Ce champ n'est pas mappé directement à l'entité Quote
                'label' => 'Services',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
            // 'appointment' => null, // Ajouter l'option appointment
        ]);
    }
}
