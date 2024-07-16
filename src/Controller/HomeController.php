<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Repository\DayOffRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectImgRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

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
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository, CategoryRepository $categoryRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs= $projectImgRepository->findAll();
        $categories= $categoryRepository->findAll();

        // Vérifie si les projets et les images de projets existent
        if (!$projects || !$projectImgs || !$categories) {
            throw new NotFoundHttpException('No projects or project images found');        
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
    // Gère le processus de création d'un rendez-vous
    #[Route('/home/appointment', name: 'app_appointment')]
    public function addAppointment(Request $request, Security $security, EntityManagerInterface $entityManager, DayOffRepository $dayOffRepository, MailerInterface $mailer): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);

        $form->handleRequest($request);

        // Récupère tous les jours de congé depuis le repository
        $dayOffs = $dayOffRepository->findAll();

        $dayOffDates = [];
        // Convertit les objets DayOff en un tableau de dates
        foreach ($dayOffs as $dayOff) {
            $dayOffDates[] = $dayOff->getDayOff()->format('Y-m-d');
        }
        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire
            $appointment = $form->getData();
            // Récupère le créneau horaire sélectionné depuis la requête
            $selectedSlot = $request->request->get('selectedSlot');

            if ($selectedSlot) {
                // Crée des objets DateTime pour le début et la fin du rendez-vous
                $startDate = new \DateTime($selectedSlot);
                $endDate = clone $startDate;
                $endDate->modify('+1 hour');

                // Vérifie si la date sélectionnée est un jour de congé
                if (in_array($startDate->format('Y-m-d'), $dayOffDates)) {
                    $this->addFlash('error', 'Vous ne pouvez pas prendre RDV durant nos congés.');
                } else {
                    // Définit les dates de début et de fin du rendez-vous
                    $appointment->setStartDate($startDate);
                    $appointment->setEndDate($endDate);

                    //Vérifie si un utilisateur est connecté
                    $user = $security->getUser();
                    if ($user) {
                        // Si un utilisateur est connecté, associe ses informations au rendez-vous
                        $appointment->setUser($user);
                    }

                    // Persiste le rendez-vous dans la base de données
                    $entityManager->persist($appointment);
                    $entityManager->flush();

                    // Envoi de l'email de confirmation
                    $email = (new Email())
                    ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
                    ->to($appointment->getEmail())
                    ->subject('Confirmation de votre rendez-vous')
                    ->html('<p>Votre rendez-vous a été enregistré avec succès pour le ' . $startDate->format('d/m/Y à H:i') . '.</p>');

                    $mailer->send($email);

                    // Ajoute un message de succès et redirige vers la page d'accueil
                    $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès. Un email de confirmation vous a été envoyé.');
                    return $this->redirectToRoute('app_home');
                }
            } else {
                // Si aucun créneau horaire n'est sélectionné, ajoute un message d'erreur
                $this->addFlash('error', 'Veuillez sélectionner un créneau horaire.');
            }
        }

        return $this->render('home/appointment.html.twig', [
            'form' => $form->createView(),
            'title' => 'Prise de rendez-vous'
        ]);
    }

    // Récupère les créneaux horaires disponibles pour une date donnée
    #[Route('/available_rdv', name:'available_rdv', methods:['POST'])]
    public function getAvailableTimes(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {   
        // Crée un objet DateTime à partir de la date de début postée
        $startDate = new \DateTime($request->request->get('startDate'));

        // Récupère les créneaux horaires disponibles
        $availabilities = $appointmentRepository->findAllRDV($startDate);

        // Retourne les disponibilités sous forme de réponse JSON
        return new JsonResponse([
            'availabilities' => $availabilities,
        ]);
    }

    // Récupère toutes les dates de congé
     #[Route('/get_dayoff_dates', name:'get_dayoff_dates', methods:['POST'])]
    public function getDayOffDates(DayOffRepository $dayOffRepository): JsonResponse
    {
        // Récupère tous les jours de congé depuis le repository
        $dayoffs = $dayOffRepository->findAllDayoffs();

        // Convertit les objets DateTime en format string pour JavaScript
        $dayoffDates = [];

        foreach ($dayoffs as $dayoff) {
            $dayoffDates[] = $dayoff->format('Y-m-d');
        }
        // Retourne les dates de congé sous forme de réponse JSON
        return new JsonResponse([
            'dayoffDates' => $dayoffDates,
        ]);
    }

    #[Route('/profil/appointment/{id}/delete', name: 'app_cancel_appointment', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancelAppointment(EntityManagerInterface $entityManager, int $id, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }
    
        // Récupère le rendez-vous
        $appointment = $entityManager->getRepository(Appointment::class)->find($id);
    
        // Vérifie si le rendez-vous existe et si l'utilisateur est autorisé à le supprimer
        if (!$appointment || !($user === $appointment->getUser() || $this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }
    
        // Supprime le rendez-vous de la base de données
        $entityManager->remove($appointment);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true]);
    }

}
