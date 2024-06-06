<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\ProjectImgRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

// ---------------------------------Vue RDV et ajout de RDV--------------------------------- //
#[Route('/home/appointment', name: 'app_appointment')]
public function addAppointment(Request $request, EntityManagerInterface $entityManager, AppointmentRepository $appointmentRepository): Response
{
    $appointment = new Appointment();
    $form = $this->createForm(AppointmentType::class, $appointment);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $appointment = $form->getData();
        $selectedSlot = $request->request->get('selectedSlot');

        if ($selectedSlot) {
            $startDate = new \DateTime($selectedSlot);
            $endDate = clone $startDate;
            $endDate->modify('+1 hour');

            $appointment->setStartDate($startDate);
            $appointment->setEndDate($endDate);

            // Persister l'appointment
            $entityManager->persist($appointment);
            $entityManager->flush();

            // Rediriger ou afficher un message de succès
            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');
            return $this->redirectToRoute('app_home');
        } else {
            $this->addFlash('error', 'Veuillez sélectionner un créneau horaire.');
        }
    }

    return $this->render('home/appointment.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/available_rdv', name:'available_rdv', methods:['POST'])]
public function getAvailableTimes(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
{
    $startDate = new \DateTime($request->request->get('startDate'));

    // Appel de la méthode pour obtenir les créneaux disponibles
    $availabilities = $appointmentRepository->availabilities($startDate);

    return new JsonResponse([
        'availabilities' => $availabilities,
    ]);
}
//     $services = new Service();
//     $form = $this->createForm(ServiceType::class, $services);

//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
        
//         $services = $form->getData();
//         // Persister l'appointment
//         $entityManager->persist($services);
//         $entityManager->flush();

//         // Rediriger ou afficher un message de succès
//         $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');
//         return $this->redirectToRoute('app_home');
//     }

//     return $this->render('home/services.html.twig', [
//         'form' => $form->createView(),
//     ]);
// }

}
