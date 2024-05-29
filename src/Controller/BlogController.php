<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Cocur\Slugify\Slugify;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function articleShow(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw new NotFoundHttpException('Article not found');
        }
        if ($article->getSlug() !== $slug) {
        throw new NotFoundHttpException('Article not found');        
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    // ---------------------------------Ajout d'un commentaire article--------------------------------- //
    #[Route('blog/{slug}/comment', name: 'app_article_comment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]*'])]
    public function addComment(string $slug, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw new NotFoundHttpException('Article not found');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setArticle($article);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_article', ['slug' => $slug]);
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
}


