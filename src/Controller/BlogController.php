<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
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

        return $this->render('blog/article_list.html.twig', [
            'articles' => $articles,
        ]);
    }

    // ---------------------------------Vue détail article--------------------------------- //
    #[Route('blog/{slug}', name: 'app_article', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function articleShow(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        $articles = $articleRepository->findAll();


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
            'articles' => $articles,
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    // ---------------------------------Ajout/Edition d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_USER')]
    #[Route('blog/{slug}/comment', name: 'app_article_addComment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]+'])]
    #[Route('blog/{slug}/comment/{id}/edit', name: 'app_article_editComment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function add_editComment(string $slug, Request $request,  ?int $id = null, ?int $commentId = null, CommentRepository $commentRepository, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Vérifie si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté.'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Recherche de l'article correspondant au slug fourni
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            return new JsonResponse(['error' => 'Article non trouvé !'], Response::HTTP_NOT_FOUND);
        }

        if ($id !== null) {
            // Si un ID de commentaire est fourni, recherche le commentaire
            $comment = $commentRepository->find($id);
            if (!$comment) {
                return new JsonResponse(['error' => 'Commentaire non trouvé !'], Response::HTTP_NOT_FOUND);
            }
            // Vérifie si l'utilisateur est autorisé à modifier le commentaire
            if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à modifier ce commentaire.'], Response::HTTP_FORBIDDEN);
            }
        } else {
            // Si aucun ID de commentaire n'est fourni, création d'un nouveau commentaire
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setArticle($article);
            $comment->setCommentDate(new \DateTime());
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            // Retourne une réponse JSON avec les détails du commentaire
            return new JsonResponse([
                'success' => true,
                'comment' => [
                    'id' => $comment->getId(),
                    //si un utilisateur est associé au commentaire, si non, elle retourne Utilisateur supprimé
                    'username' => $comment->getUser() ? $comment->getUser()->getUsername() : 'Utilisateur supprimé',
                    'commentContent' => $comment->getCommentContent(),
                    'date' => $comment->getCommentDate()->format('d/m/Y à H:i')
                ]
            ]);
        }
        // Collecte des erreurs du formulaire pour les retourner en réponse JSON
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
    
        return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }
    
    // ---------------------------------Suppression d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_USER')]
    #[Route('/blog/{slug}/comment/{id}/delete', name: 'app_article_deleteComment', methods: ['DELETE'], requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function deleteComment(string $slug, int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère l'article associé au slug
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
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


