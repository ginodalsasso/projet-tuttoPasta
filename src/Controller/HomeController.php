<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\Service;
use App\Form\QuoteType;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Services\PdfGenerator;
use App\Repository\QuoteRepository;
use Symfony\Component\Mime\Address;
use App\Repository\DayOffRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class HomeController extends AbstractController
{

    private $htmlSanitizer;

    public function __construct(HtmlSanitizerInterface  $htmlSanitizer) {
        $this->htmlSanitizer = $htmlSanitizer;
    }
#region APPOINTMENT
//________________________________________________________________APPOINTMENT______________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
    // ---------------------------------Vue RDV et Gestion de RDV--------------------------------- //
    // Gère le processus de création d'un rendez-vous
    #[Route('/home/appointment', name: 'app_appointment')]
    public function addAppointment(Request $request, Security $security, EntityManagerInterface $entityManager, DayOffRepository $dayOffRepository, MailerInterface $mailer, PdfGenerator $pdfGenerator): Response
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

            // Sanitize les champs du formulaire
            $appointment->setName($this->htmlSanitizer->sanitize($appointment->getName()));
            $appointment->setFirstName($this->htmlSanitizer->sanitize($appointment->getFirstName()));
            $appointment->setMessage($this->htmlSanitizer->sanitize($appointment->getMessage()));
            // Vérifie si l'adresse email est valide
            $emailAddress = $appointment->getEmail();
            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_appointment');
            }
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

                    // Si un utilisateur est connecté, associe ses informations au rendez-vous
                    if ($user) {
                        $appointment->setUser($user);
                    }

                    // Création du devis
                    $quote = $this->createQuote($appointment);
                    
                    // Génération et stockage du PDF
                    $reference = $quote->getReference();
                    $pdfLink  = $this->generateAndStorePdf($pdfGenerator, $quote, $reference);

                    // Persiste le rendez-vous dans la base de données
                    $entityManager->persist($appointment);
                    $entityManager->persist($quote);
                    $entityManager->flush();
                    


                    $this->sendConfirmationEmail($mailer, $emailAddress, $startDate);

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


    // Gestion de l'envoi de confiration de prise de RDV
    private function sendConfirmationEmail(MailerInterface $mailer, string $emailAddress, \DateTime $startDate): void
    {
        $emailContent = $this->renderView('emails/appointment_confirmation.html.twig', [
            'appointmentDate' => $startDate->format('d/m/Y à H:i')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
            ->to($emailAddress)
            ->subject('Confirmation de votre rendez-vous')
            ->html($emailContent);

        $mailer->send($email);
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
#endregion

#region PDF
//________________________________________________________________APPOINTMENT PDF_________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
    // ---------------------------------Vue PDF DEVIS--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}', name: 'quote_pdf')]
    public function viewQuotePdf(int $id, EntityManagerInterface $entityManager, PdfGenerator $pdfGenerator): Response
    {
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        if (!$quote) {
            throw $this->createNotFoundException('Ce devis n\'existe pas');
        }

        $html = $this->renderView('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
        ]);
        // Générer le contenu PDF
        return $pdfGenerator->showPdfFile($html);
    }

    // ---------------------------------Vue LISTE DES DEVIS--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quotes', name: 'app_quotes')]
    public function listQuotesShow (QuoteRepository $quoteRepository): Response
    {
        $quotes = $quoteRepository->findAll();

        return $this->render('admin/quote_list.html.twig', [
            'quotes' => $quotes,
        ]);
    }

    // ---------------------------------Création d'un devis--------------------------------- //
    private function createQuote(Appointment $appointment): Quote
    {
        $services = $appointment->getServices();
        if ($services->isEmpty()) {
            return null;
        }
        // Crée un nouveau devis
        $quote = new Quote();
        $reference = 'DEVIS-' . uniqid();
        $quote->setReference($reference);
        $quote->setQuoteDate(new \DateTime());
        $quote->setCustomerName($appointment->getName());
        $quote->setCustomerFirstName($appointment->getFirstName());
        $quote->setCustomerEmail($appointment->getEmail());
        $quote->setStatus(0);
        $quote->setState(Quote::STATE_PENDING);

        // Associe le rendez-vous au devis
        $quote->setAppointments($appointment);

         // Calcul du prix total des services selectionnés
        $totalPrice = $quote->calculateTotal($services);
        $quote->setTotalTTC($totalPrice);

        return $quote;
    }


    // ---------------------------------Génération et stockage du PDF--------------------------------- //
    private function generateAndStorePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $html = $this->renderView('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
        ]);
        // Générer le contenu PDF
        $pdfContent = $pdfGenerator->generatePDF($html);
        
        // Définir le chemin de stockage du PDF
        $pdfDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/';
        // Générer un nom de fichier unique
        $pdfFilename = $reference . '.pdf';
        // Chemin complet du fichier PDF
        $pdfFilepath = $pdfDirectory . $pdfFilename;
    
        // Sauvegarde le PDF sur le système de fichiers
        file_put_contents($pdfFilepath, $pdfContent);
    
        // Stocker le lien du PDF dans l'entité Quote
        $quote->setPdfContent('/uploads/pdf/' . $pdfFilename);
        // Mettre à jour l'entité Quote
        return $quote->getPdfContent();
    }



    // ---------------------------------Formulaire d'Edition du devis PDF--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/edit/{id}', name: 'quote_edit')]
    public function editQuote(int $id, Request $request, EntityManagerInterface $entityManager, HtmlSanitizerInterface $htmlSanitizer): Response
    {
        // Récupérer le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);
        // Récupérer l'appointment lié
        $appointment = $quote ? $quote->getAppointments() : null;
    
        if (!$quote) {
            throw $this->createNotFoundException('Ce devis n\'existe pas');
        }
        if (!$appointment) {
            throw $this->createNotFoundException('Ce RDV n\'existe pas');
        }
    
        // Créer le formulaire d'édition du devis
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Sanitize et valider les champs du formulaire
            $reference = $htmlSanitizer->sanitize($form->get('reference')->getData());
            $clientLastName = $htmlSanitizer->sanitize($form->get('customerName')->getData());
            $clientFirstName = $htmlSanitizer->sanitize($form->get('customerFirstName')->getData());
            $email = $form->get('customerEmail')->getData();
            $services = $form->get('services')->getData();
    
            // Vérifier si un nouveau service est défini et le sanitize si nécessaire
            $newServiceCategory = $form->get('newServiceCategory')->getData();
            $newServiceName = $form->get('newService')->getData();
            $newServicePrice = $form->get('newServicePrice')->getData();
    
            if ($newServiceName !== null) {
                $newServiceName = $htmlSanitizer->sanitize($newServiceName);
            }
            if ($newServicePrice !== null) {
                // Valider que le prix est un nombre positif
                if (!is_numeric($newServicePrice) || $newServicePrice <= 0) {
                    $this->addFlash('error', 'Le prix du service doit être un nombre positif.');
                    return $this->render('admin/edit_quote.html.twig', [
                        'quote' => $quote,
                        'form' => $form->createView(),
                    ]);
                }
                $newServicePrice = $htmlSanitizer->sanitize($newServicePrice);
            }
    
            // Valider l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->render('admin/edit_quote.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }
    
            // Vérifier si un nouveau service a été ajouté
            if ($newServiceName && $newServicePrice) {
                // Créer et sauvegarder le nouveau service
                $newService = new Service();
                $newService->setServiceName($newServiceName);
                $newService->setServicePrice($newServicePrice);
                $newService->setCategory($newServiceCategory);
    
                $entityManager->persist($newService);
                $entityManager->flush();
    
                // Ajouter le nouveau service aux services sélectionnés
                $services[] = $newService;
            }
    
            // Mettre à jour les services de l'appointment lié
            foreach ($appointment->getServices() as $service) {
                // Si le service n'est pas sélectionné, le retirer
                $appointment->removeService($service);
            }
            // Ajouter les services sélectionnés
            foreach ($services as $service) {
                $appointment->addService($service);
            }
    
            // Recalculer le total
            $totalPrice = $quote->calculateTotal($appointment->getServices());
            $quote->setTotalTTC($totalPrice);
    
            // Transformer le status du devis afin de l'afficher dans le profil user
            $quote->setStatus(1);
            $quote->setState(Quote::STATE_IN_PROGRESS);
    
            $entityManager->persist($appointment);
            $entityManager->persist($quote);
            $entityManager->flush();
    
            // Rediriger vers la vue du devis mis à jour
            return $this->redirectToRoute('quote_pdf', ['id' => $quote->getId()]);
        }
    
        return $this->render('admin/edit_quote.html.twig', [
            'quote' => $quote,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/delete', name: 'app_delete_quote', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteQuote(EntityManagerInterface $entityManager, int $id, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }
    
        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);
    
        // Vérifie si l'utilisateur est autorisé à le supprimer
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }

        // Supprime le fichier PDF associé
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public' . $quote->getPdfContent();
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
            // Supprime le devis de la base de données
        $entityManager->remove($quote);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true]);
    }
}
#endregion