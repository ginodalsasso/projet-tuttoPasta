<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Cocur\Slugify\Slugify;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    // ---------------------------------Ajout/Edition d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_ADMIN')] 
    #[IsGranted('ROLE_USER')]
    #[Route('blog/{slug}/comment', name: 'app_article_addComment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]*'])]
    #[Route('blog/{slug}/comment/{id}/edit', name: 'app_article_editComment', requirements: ['id' => '\d+'])]
    public function add_editComment(string $slug, Request $request, Comment $comment = null, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if ((!$comment->getUser() === $user) || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Veuillez vous connecter ou vous assurer d\'avoir les droits !');
        }
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw new NotFoundHttpException('Article not found');
        }

        if(!$comment){
            $comment = new Comment();
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur actuel au commentaire
            $comment->setUser($user);
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
    
    // ---------------------------------Suppression d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_ADMIN')] 
    #[IsGranted('ROLE_USER')]
    #[Route('blog/{slug}/comment/{id}/delete', name: 'app_article_deleteComment', requirements: ['id' => '\d+'])]
    public function deleteComment(string $slug, int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw new NotFoundHttpException('Article not found');
        }

        $user = $security->getUser();

        // Recherche le commentaire à supprimer
        $comment = $entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }
        if (($comment->getUser() === $user) || $this->isGranted('ROLE_ADMIN')) {
            // Supprimez le commentaire
            $entityManager->remove($comment);
            $entityManager->flush();
        }else{
            $this->addFlash('error', 'Veuillez vous connecter ou vous assurer d\'avoir les droits !');
        }

        return $this->redirectToRoute('app_article', ['slug' => $slug]);
    }

}


