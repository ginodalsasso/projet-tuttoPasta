<?php

namespace App\Form;

use App\Entity\Service;
use Doctrine\ORM\EntityRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('services_identite_visuelle', EntityType::class, [
                'class' => Service::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'query_builder' => function (ServiceRepository $sr) {
                    return $sr->createQueryBuilder('s')
                        ->join('s.category', 'c')
                        ->where('c.categoryName = :categoryName')
                        ->setParameter('categoryName', 'Identité visuelle');
                },
            ])
            ->add('services_site_internet', EntityType::class, [
                'class' => Service::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'query_builder' => function (ServiceRepository $sr) {
                    return $sr->createQueryBuilder('s')
                        ->join('s.category', 'c')
                        ->where('c.categoryName = :categoryName')
                        ->setParameter('categoryName', 'Site internet');
                },
            ])
            ->add('services_presta_a_la_carte', EntityType::class, [
                'class' => Service::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'query_builder' => function (ServiceRepository $sr) {
                    return $sr->createQueryBuilder('s')
                        ->join('s.category', 'c')
                        ->where('c.categoryName = :categoryName')
                        ->setParameter('categoryName', 'Presta à la carte');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Permet de ne pas lier le formulaire à une entité
        ]);
    }
}
