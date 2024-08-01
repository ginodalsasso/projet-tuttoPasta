<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Quote;
use App\Entity\Appointment;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfGenerator
{
    private $domPdf;
    private $twig;
    private $params;

    public function __construct(Environment $twig, ParameterBagInterface $params){

        $this->domPdf = new Dompdf();

        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Arial');

        $this->domPdf->setPaper('A4', 'portrait');

        $this->domPdf->setOptions($pdfOptions);

        $this->twig = $twig;

        $this->params = $params;
    }


    // ---------------------------------Affichage du PDF--------------------------------- //
    public function showPdfFile($html): Response
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        
        $pdfContent = $this->domPdf->output();
        
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"'
            ]
        );
    }

    // ---------------------------------Génération du PDF--------------------------------- //
    public function generatePDF($html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }


    // ---------------------------------Création d'un devis--------------------------------- //
    public function createQuote(Appointment $appointment): Quote
    {
        $services = $appointment->getServices();

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

        // Calcul du prix total des services sélectionnés*
        if (!$services->isEmpty()) {
            $totalPrice = $quote->calculateTotal($services);
        } else {
            $totalPrice = 0;
        }

        // Calcul du prix total des services selectionnés
        // $totalPrice = $quote->calculateTotal($services);
        $quote->setTotalTTC($totalPrice);

        return $quote;
    }


    // ---------------------------------Génération et stockage du PDF--------------------------------- //
    public function generateAndStorePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $html = $this->twig->render('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
        ]);
        // Générer le contenu PDF
        $pdfContent = $pdfGenerator->generatePDF($html);
        
        // Définir le chemin de stockage du PDF
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/uploads/pdf/';
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


    // ---------------------------------Archivage et stockage du PDF--------------------------------- //
    public function generateAndArchivePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $html = $this->twig->render('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
        ]);
        // Générer le contenu PDF
        $pdfContent = $pdfGenerator->generatePDF($html);

        // Définir le chemin de stockage du PDF
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/uploads/pdf/archive/';
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


    // ---------------------------------Génération de l'offre de prix PDF--------------------------------- //
    public function generateOfferPricePdf(array $selectedServices): Response
    {
        $imagePath = $this->params->get('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        $html = $this->twig->render('admin/offerPrice.html.twig', [
            'services' => $selectedServices,
            'logo' => $imageData,
        ]);
        // Génére le contenu PDF
        $this->domPdf->loadHtml($html);
        // Rendu du PDF
        $this->domPdf->render();
        // Renvoie le PDF en réponse HTTP
        $pdfContent = $this->domPdf->output();
        
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis_services.pdf"'
            ]
        );
    }
}