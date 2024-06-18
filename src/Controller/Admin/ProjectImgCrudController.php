<?php

namespace App\Controller\Admin;

use App\Entity\ProjectImg;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ProjectImgCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectImg::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            CollectionField::new('project'),
            ImageField::new('image')->setUploadDir('public/img/'),
            TextField::new('alt'),
        ];
    }
    
}
