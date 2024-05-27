<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectImg;
use App\Repository\ProjectRepository;
use App\Repository\ProjectImgRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{

//______________________________________________________________AFFICHAGE______________________________________________________________
    // ---------------------------------Vue liste projets--------------------------------- //
    #[Route('/projectList', name: 'app_projectList')]
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();

        return $this->render('projectList/index.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
        ]);
    }

    // ---------------------------------Vue détail projets--------------------------------- //
    // #[Route('/project/{id}', name: 'app_project')]
    // public function projectShow(Project $project): Response
    // { 
    //     // $projectRepository->findOneBy(['projectName' => $slug]);

    //     return $this->render('project/index.html.twig', [
    //         'project' => $project
    //     ]);
    // }
    
    //utiliser le bundle slugify pour un slug en URL
    // #[Route('/project/{projectName}', name: 'app_project', requirements:['projectName' => '[a-z0-9-]+'])]
    // public function projectShow(Project $project, Request $request): Response
    // { 
    //     dd($request->attributes->get('projectName'));

    //     return $this->render('project/index.html.twig', [
    //         'project' => $project
    //     ]);
    // }

}
