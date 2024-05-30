<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\ProjectImgRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
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

    #[Route('/home/appointment', name: 'app_appointment')]
    public function appointmentShow(): Response
    {
        $appointment = new Appointment();

        $form = $this->createForm(AppointmentType::class, $appointment);

        return $this->render('home/appointment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
