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
    #[Route('/project-list', name: 'app_projectList')]
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();

        // Vérifie si les projets et les images de projets existent
        if (!$projects || !$projectImgs) {
            throw new NotFoundHttpException('No projects or project images found');        
        }

        return $this->render('projectList/index.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
        ]);
    }

    // ---------------------------------Vue détail projets--------------------------------- //
    #[Route('project-list/{slug}', name: 'app_project', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function projectShow(Project $project, string $slug): Response
    { 
        // Vérifie si le slug de l'objet project correspond au slug de l'URL
        if ($project->getSlug() !== $slug) {
            throw new NotFoundHttpException('Project not found');        
        }

        return $this->render('project/index.html.twig', [
            'project' => $project
        ]);
    }
}

    