<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('projectName'),
            TextField::new('projectTitle'),
            TextEditorField::new('projectContent'),
            DateTimeField::new('projectDate'),
            AssociationField::new('categories')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
            AssociationField::new('images')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
        
            TextField::new('slug'),
        ];
    }
    
}
