<?php

namespace App\Form;

use App\Entity\Service;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('services_identite_visuelle', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'serviceName', 
            'multiple' => true,
            'expanded' => true, // true pour checkboxes
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->join('s.category', 'c')
                    ->where('c.categoryName = :categoryName')
                    ->setParameter('categoryName', 'Identité Visuelle');
            },
        ])
        ->add('services_site_internet', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'serviceName', 
            'multiple' => true,
            'expanded' => true, // true pour checkboxes
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->join('s.category', 'c')
                    ->where('c.categoryName = :categoryName')
                    ->setParameter('categoryName', 'Site Internet');
            },
        ])
        ->add('services_presta_a_la_carte', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'serviceName', 
            'multiple' => true,
            'expanded' => true, // true pour checkboxes
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->join('s.category', 'c')
                    ->where('c.categoryName = :categoryName')
                    ->setParameter('categoryName', 'Presta à la carte');
            },
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
