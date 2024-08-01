<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\Service;
use App\Form\QuoteType;
use App\Services\PdfGenerator;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use App\Trait\QuoteTrait;



class QuoteController extends AbstractController
{
    private $pdfGenerator;

    public function __construct(PdfGenerator $pdfGenerator) {
        $this->pdfGenerator = $pdfGenerator;
    }
    // ---------------------------------Vue PDF DEVIS--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}', name: 'quote_pdf')]
    public function viewQuotePdf(int $id, EntityManagerInterface $entityManager, PdfGenerator $pdfGenerator): Response
    {
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        $imagePath = $this->getParameter('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        if (!$quote) {
            throw $this->createNotFoundException('Ce devis n\'existe pas');
        }

        $html = $this->renderView('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
            'logo' => $imageData,
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


    // ---------------------------------Suppression du devis PDF--------------------------------- //
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
    

    // ---------------------------------Archivage du devis(Etat) PDF--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/archive', name: 'app_archive_quote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function archiveQuote(EntityManagerInterface $entityManager, int $id, Security $security, PdfGenerator $pdfGenerator): JsonResponse
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }
    
        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);
    
        // Vérifie si l'utilisateur est autorisé
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }
        // Archive le devis en changeant son état
        $quote->setState(Quote::STATE_ARCHIVED);
        $reference = $quote->getReference();
        // Archive le PDF dans le dossier associé
        $pdfGenerator->generateAndArchivePdf($pdfGenerator, $quote, $reference);
        // Supprime le fichier PDF associé dans le dossier de stockage
        $this->deleteQuote($entityManager, $id, $security);

        // Persiste les modifications
        $entityManager->persist($quote);
        $entityManager->flush();

    
        return new JsonResponse(['success' => true]);
    }


    // ---------------------------------Transformation du devis en etat payé--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/completed', name: 'app_completed_quote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function completedQuote(EntityManagerInterface $entityManager, int $id, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }
    
        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);
    
        // Vérifie si l'utilisateur est autorisé
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }
        // Archive le devis en changeant son état
        $quote->setState(Quote::STATE_COMPLETED);

        // Persiste les modifications
        $entityManager->persist($quote);
        $entityManager->flush();

    
        return new JsonResponse(['success' => true]);
    }
}