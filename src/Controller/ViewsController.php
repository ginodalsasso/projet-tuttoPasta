<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Project;
use App\Form\CommentType;
use App\Form\UserFormType;
use App\Form\EditPasswordType;
use App\Repository\QuoteRepository;
use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectImgRepository;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class ViewsController extends AbstractController
{
    // ---------------------------------Vue des Erreurs--------------------------------- //
    // Vue error 404 = page non trouvée
    #[Route('/error/404', name: 'app_error_404')]
    public function showError404(): Response
    {
        return $this->render('errors/error404.html.twig');
    }
    // Vue error 500 = erreur serveur
    #[Route('/error/500', name: 'app_error_500')]
    public function showError500(): Response
    {
        return $this->render('errors/error500.html.twig');
    }


    // ---------------------------------Vue Home--------------------------------- //
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/home', name: 'app_home')]
    public function homeShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository, ServiceRepository $serviceRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();
        $services= $serviceRepository->findAll();

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
            'services' => $services,

        ]);
    }

    
    // ---------------------------------Vue profil utilisateur--------------------------------- //
    #[Route('/profil', name: 'app_profil', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profil(Security $security, AppointmentRepository $appointmentRepository, QuoteRepository $quoteRepository): Response
    {
        $user = $security->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Formulaire pour les informations utilisateur
        $form = $this->createForm(UserFormType::class, $user);

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(EditPasswordType::class, $user);

        $appointments = $appointmentRepository->findByUser($user);
        $quotes = $quoteRepository->findByUser($user);

        return $this->render('user/profil.html.twig', [
            'form' => $form->createView(),
            'passwordForm' => $passwordForm->createView(),
            'user' => $user,
            'appointments' => $appointments,
            'quotes' => $quotes,
        ]);
    }


    // ---------------------------------Vue liste projets--------------------------------- //
    #[Route('/projects', name: 'app_projectList')]
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository, CategoryRepository $categoryRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();
        $categories= $categoryRepository->findAll();

        // Vérifie si les projets et les images de projets existent
        if (!$projects || !$projectImgs || !$categories) {
            throw new NotFoundHttpException('Page non trouvée');        
        }

        return $this->render('projects/project_list.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
            'categories' => $categories,
        ]);
    }
    

    // ---------------------------------Vue détail projets--------------------------------- //
    #[Route('/projects/{slug}', name: 'app_project', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function projectShow(?Project $project, string $slug): Response
    { 
        if (!$project) {
            throw new NotFoundHttpException('Aucun projet trouvé');
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si le slug de l'objet project correspond au slug de l'URL
        if ($project->getSlug() !== $slug) {
            throw new NotFoundHttpException('Page non trouvée');   
            return $this->redirectToRoute('app_home');
        }

        return $this->render('projects/project.html.twig', [
            'project' => $project
        ]);
    }


    // ---------------------------------Vue liste articles--------------------------------- //
    #[Route('/blog', name: 'app_blog')]
    public function listArticlesShow(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        // Vérifie si les articles existent
        if (!$articles) {
            throw new NotFoundHttpException('Aucun article trouvé');;
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
            throw new NotFoundHttpException('Aucun article trouvé');;
            return $this->redirectToRoute('app_blog');
        }
        if ($article->getSlug() !== $slug) {
            throw new NotFoundHttpException('Aucun article trouvé');;
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
}