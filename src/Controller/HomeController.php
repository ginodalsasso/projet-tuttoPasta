<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\DayOffRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\ProjectImgRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HomeController extends AbstractController
{
    // ---------------------------------Vue Home--------------------------------- //
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

    // ---------------------------------Vue RDV et Gestion de RDV--------------------------------- //
    #[Route('/home/appointment', name: 'app_appointment')]
    public function addAppointment(Request $request, EntityManagerInterface $entityManager, DayOffRepository $dayOffRepository, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);

        $form->handleRequest($request);

        // Récupérer les jours de congé depuis le repository
        $dayOffs = $dayOffRepository->findAll();
        $dayOffDates = [];
        foreach ($dayOffs as $dayOff) {
            $dayOffDates[] = $dayOff->getDayOff()->format('Y-m-d');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->getData();
            $selectedSlot = $request->request->get('selectedSlot');

            if ($selectedSlot) {
                $startDate = new \DateTime($selectedSlot);
                $endDate = clone $startDate;
                $endDate->modify('+1 hour');

                // Vérifier si la date sélectionnée est un jour de congé
                if (in_array($startDate->format('Y-m-d'), $dayOffDates)) {
                    $this->addFlash('error', 'Vous ne pouvez pas prendre RDV durant nos congés.');
                } else {
                    $appointment->setStartDate($startDate);
                    $appointment->setEndDate($endDate);

                    // Persister l'appointment
                    $entityManager->persist($appointment);
                    $entityManager->flush();

                    // Rediriger ou afficher un message de succès
                    $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');
                    return $this->redirectToRoute('app_home');
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner un créneau horaire.');
            }
        }

        return $this->render('home/appointment.html.twig', [
            'form' => $form->createView(),
            'title' => 'Prise de rendez-vous'
        ]);
    }

    // Méthode de récupération des rendez vous
    // #[Route('/available_rdv', name:'available_rdv', methods:['POST'])]
    // public function getAvailableTimes(Request $request, AppointmentRepository $appointmentRepository, DayOffRepository $dayOffRepository): JsonResponse
    // {
    //     $startDate = new \DateTime($request->request->get('startDate'));

    //     // Appel de la méthode pour obtenir les créneaux disponibles
    //     $availabilities = $appointmentRepository->findAllRDV($startDate);
        
    //     // Récupère tous les jours de congé
    //     $dayoffs = $dayOffRepository->findAllDayoffs();

    //     // Convertir les objets Date en chaînes de caractères pour le JS de ma vue
    //     $dayoffDates = [];

    //     foreach ($dayoffs as $dayoff) {
    //         $dayoffDates[] = $dayoff->format('Y-m-d');
    //     }
    //     return new JsonResponse([
    //         'availabilities' => $availabilities,
    //         'dayoffDates' => $dayoffDates,
    //     ]);
    // }

    // Créneaux disponibles
    #[Route('/available_rdv', name:'available_rdv', methods:['POST'])]
    public function getAvailableTimes(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {
        $startDate = new \DateTime($request->request->get('startDate'));

        // Appel de la méthode pour obtenir les créneaux disponibles
        $availabilities = $appointmentRepository->findAllRDV($startDate);
        
        return new JsonResponse([
            'availabilities' => $availabilities,
        ]);
    }

    // Jours non travaillés
     #[Route('/get_dayoff_dates', name:'get_dayoff_dates', methods:['POST'])]
    public function getDayOffDates(DayOffRepository $dayOffRepository): JsonResponse
    {
        // Récupère tous les jours de congé
        $dayoffs = $dayOffRepository->findAllDayoffs();

        // Convertir les objets Date en chaînes de caractères pour le JS de ma vue
        $dayoffDates = [];

        foreach ($dayoffs as $dayoff) {
            $dayoffDates[] = $dayoff->format('Y-m-d');
        }

        return new JsonResponse([
            'dayoffDates' => $dayoffDates,
        ]);
    }

}
