<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $this->addFlash('info', 'Article non trouvé');
        return $this->redirectToRoute('app_blog');
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
            $this->addFlash('info', 'Article non trouvé');
            return $this->redirectToRoute('app_blog');
        }
        if ($article->getSlug() !== $slug) {
            $this->addFlash('info', 'Article non trouvé');
            return $this->redirectToRoute('app_blog');
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
    #[Route('blog/{slug}/comment', name: 'app_article_addComment', methods: ['POST'])]
    #[Route('blog/{slug}/comment/{id}/edit', name: 'app_article_editComment', methods: ['POST'])]
    public function add_editComment(string $slug, Request $request, ?Comment $comment = null, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {

        $user = $security->getUser();

        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé !');
            return $this->redirectToRoute('app_blog');
        }

        // Vérifier si l'utilisateur est autorisé à ajouter ou éditer un commentaire
        if ($user && !($user === $comment->getUser() || $this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Veuillez vous connecter ou vous assurer d\'avoir les droits !');
            return $this->redirectToRoute('app_blog');
        }

        if (!$comment) {
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
    
            return new JsonResponse([
                'success' => true,
                'comment' => [
                    'id' => $comment->getId(),
                    'username' => $user->getUsername(),
                    'commentContent' => $comment->getCommentContent(),
                    'date' => $comment->getCommentDate()->format('d/m/Y à H:i')
                ]
            ]);
        }
    
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
    
        return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }
    
    // ---------------------------------Suppression d'un commentaire article--------------------------------- //
    // #[IsGranted('ROLE_ADMIN')] 
    // #[IsGranted('ROLE_USER')]
    // #[Route('blog/{slug}/comment/{id}/delete', name: 'app_article_deleteComment', requirements: ['id' => '\d+'])]
    // public function deleteComment(string $slug, int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): Response
    // {
    //     // Récupère l'article associé au slug
    //     $article = $articleRepository->findOneBy(['slug' => $slug]);
    //     if (!$article) {
    //         return $this->redirectToRoute('app_blog');
    //         $this->addFlash('info', 'Article non trouvé');
    //     }
    //     // Récupère l'utilisateur actuel
    //     $user = $security->getUser();

    //     // Recherche le commentaire à supprimer
    //     $comment = $entityManager->getRepository(Comment::class)->find($id);
    //     if (!$comment) {
    //         return $this->redirectToRoute('app_blog');
    //         $this->addFlash('info', 'Commentaire non trouvé');
    //     }
    //     // Vérifie si l'utilisateur est l'auteur du commentaire ou s'il a le rôle admin
    //     if (($comment->getUser() === $user) || $this->isGranted('ROLE_ADMIN')) {
    //         // Supprimez le commentaire
    //         $entityManager->remove($comment);
    //         $entityManager->flush();
    //     }else{
    //         $this->addFlash('error', 'Veuillez vous connecter ou vous assurer d\'avoir les droits !');
    //     }

    //     return $this->redirectToRoute('app_article', ['slug' => $slug]);
    // }

    #[IsGranted('ROLE_ADMIN')]
    #[IsGranted('ROLE_USER')]
    #[Route('/blog/{slug}/comment/{id}/delete', name: 'app_article_deleteComment', methods: ['DELETE'])]
    public function deleteComment(string $slug, int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère l'article associé au slug
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupère l'utilisateur actuel
        $user = $security->getUser();

        // Recherche le commentaire à supprimer
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        // Vérifie si l'utilisateur est autorisé à supprimer le commentaire
        if ($user && !($user === $comment->getUser() || $this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Veuillez vous connecter ou vous assurer d\'avoir les droits !');
            return $this->redirectToRoute('app_blog');
        }

        if (!$comment) {
            return new JsonResponse(['success' => false, 'error' => 'Commentaire non trouvé !'], Response::HTTP_NOT_FOUND);
        }

        // Supprime le commentaire
        $entityManager->remove($comment);
        $entityManager->flush();

        // Retourne une réponse indiquant le succès de la suppression
        return new JsonResponse(['success' => true]);
    }

}


