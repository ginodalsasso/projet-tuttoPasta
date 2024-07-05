<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Controller\Admin\CategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('articleTitle'),
            // TextEditorField::new('articleTitle'),
            // TextareaField::new('articleContent')->renderAsHtml(),
            TextEditorField::new('articleContent')
                ->setFormTypeOption('attr', ['class' => 'trix-content']),
            DateTimeField::new('articleDate'),
            TextField::new('slug'),
            // CollectionField::new('categories'),
            CollectionField::new('comments'),
            // CollectionField::new('categories')->useEntryCrudForm(CategoryCrudController::class)
        ];
    }
}
