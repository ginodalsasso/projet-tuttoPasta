<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends AbstractController
{
//______________________________________________________________AFFICHAGE______________________________________________________________
    // ---------------------------------Vue liste articles--------------------------------- //
    #[Route('/blog', name: 'app_blog')]
    public function listArticlesShow(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        // Vérifie si les articles existent
        if (!$articles) {
            throw new NotFoundHttpException('No articles found');        
        }

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    // ---------------------------------Vue détail article--------------------------------- //
    #[Route('blog/{slug}', name: 'app_article', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function articleShow(Article $article, string $slug): Response
    { 
        // Vérifie si le slug de l'objet project correspond au slug de l'URL
        if ($article->getSlug() !== $slug) {
            throw new NotFoundHttpException('Article not found');        
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article
        ]);
    }
}


