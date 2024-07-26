<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class PdfGenerator
{
    private $domPdf;

    public function __construct(){

        $this->domPdf = new Dompdf();

        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Arial');

        $this->domPdf->setPaper('A4', 'portrait');

        $this->domPdf->setOptions($pdfOptions);
    }

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

    public function generatePDF($html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }
    
}