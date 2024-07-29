<?php

namespace App\Form;

use App\Entity\Quote;
use App\Entity\Service;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'serviceName',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false, // Ce champ n'est pas mappé directement à l'entité Quote
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.category IS NOT NULL');
                },
                'label' => 'Services',
            ])
            ->add('newService', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ajouter un nouveau service',
                'label_attr' => ['id' => 'newService'],
                'attr' => [
                    'id' => 'newService',
                ],
            ])
            ->add('newServicePrice', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Prix du nouveau service',
                'label_attr' => ['id' => 'newServicePrice'],
                'scale' => 2, // Assure que le champ accepte un nombre avec deux chiffres après la virgule.
                'attr' => [
                    'step' => '0.01', 
                    'min' => '0', // Empêche l'utilisateur de saisir un nombre négatif
                ],
            ])
            ->add('newServiceCategory', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'categoryName',
                'mapped' => false,
                'required' => false,
                'label' => 'Catégorie du nouveau service',
                'label_attr' => ['id' => 'newServiceCategory']
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}