<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\ProjectImgRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
// #[Route('/home/appointment', name: 'app_appointment')]
// public function addAppointment(Request $request, EntityManagerInterface $entityManager): Response
// {
//     $appointment = new Appointment(); 
//     $form = $this->createForm(AppointmentType::class, $appointment);
//     $form->handleRequest($request);

//     // Traite le formulaire s'il est soumis et valide
//     if ($form->isSubmitted() && $form->isValid()) {
//         // Récupère les services sélectionnés dans le formulaire
//         $selectedServices = $form->get('services')->getData();

//         // Associe chaque service sélectionné à l'entité Appointment
//         foreach ($selectedServices as $service) {
//             $appointment->addService($service);
//         }
        
//         $entityManager->persist($appointment);
//         $entityManager->flush();

//         $this->addFlash('success', 'Vous venez de prendre RDV');
//         return $this->redirectToRoute('app_home');
//     }

//     return $this->render('home/appointment.html.twig', [
//         'form' => $form->createView(),
//     ]);
// }


#[Route('/home/appointment', name: 'app_appointment')]
public function addAppointment(Request $request, EntityManagerInterface $entityManager): Response
{
    $appointment = new Appointment();
    $form = $this->createForm(AppointmentType::class, $appointment);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $appointment = $form->getData();
        // Persister l'appointment
        $entityManager->persist($appointment);
        // dd($appointment);
        
        $entityManager->flush();

        // Rediriger ou afficher un message de succès
        $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');
        return $this->redirectToRoute('app_home');
    }

    return $this->render('home/appointment.html.twig', [
        'form' => $form->createView(),
    ]);
}
// #[Route('/home/services', name: 'app_services')]
// public function addService(Request $request, EntityManagerInterface $entityManager): Response
// {
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
