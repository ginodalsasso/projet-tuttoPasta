<?php

namespace App\Trait;

use App\Entity\Quote;
use App\Entity\Appointment;
use App\Services\PdfGenerator;

trait QuoteTrait
{

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


    // ---------------------------------Archivage et stockage du PDF--------------------------------- //
    private function generateAndArchivePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $html = $this->renderView('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
        ]);
        // Générer le contenu PDF
        $pdfContent = $pdfGenerator->generatePDF($html);

        // Définir le chemin de stockage du PDF
        $pdfDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/archive/';
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
}