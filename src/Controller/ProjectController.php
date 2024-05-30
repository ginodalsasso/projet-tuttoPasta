<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectImg;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use App\Repository\ProjectRepository;
use App\Repository\ProjectImgRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectController extends AbstractController
{

//______________________________________________________________AFFICHAGE______________________________________________________________
    // ---------------------------------Vue liste projets--------------------------------- //
    #[Route('/projects', name: 'app_projectList')]
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();

        // Vérifie si les projets et les images de projets existent
        if (!$projects || !$projectImgs) {
            throw new NotFoundHttpException('No projects or project images found');        
        }

        return $this->render('projects/index.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
        ]);
    }

    // ---------------------------------Vue détail projets--------------------------------- //
    #[Route('/projects/{slug}', name: 'app_project', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function projectShow(?Project $project, string $slug): Response
    { 
        if (!$project) {
            $this->addFlash('info', 'Projet non trouvé');
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si le slug de l'objet project correspond au slug de l'URL
        if ($project->getSlug() !== $slug) {
            $this->addFlash('info', 'Page non trouvée');    
            return $this->redirectToRoute('app_home');
        }

        return $this->render('projects/project.html.twig', [
            'project' => $project
        ]);
    }
}

    