<?php

namespace App\Service;

use App\Entity\ParticipationEvenement;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TicketPdfService
{
    private UrlGeneratorInterface $router;
    private Environment $twig;
    private string $scanBaseUrl;

    public function __construct(UrlGeneratorInterface $router, Environment $twig, string $scanBaseUrl)
    {
        $this->router = $router;
        $this->twig = $twig;
        $this->scanBaseUrl = $scanBaseUrl;
    }

    public function generateTicketPdf(ParticipationEvenement $participation): string
    {
        // Utilisation de l'URL de base configurée dans .env (ex: http://IP_LOCALE:8000)
        $scanUrl = sprintf('%s/p/scan/%s', rtrim($this->scanBaseUrl, '/'), $participation->getJeton());

        $qrCode = new \Endroid\QrCode\QrCode(
            data: $scanUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 200,
            margin: 10
        );

        $writer = new \Endroid\QrCode\Writer\SvgWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = $result->getDataUri();

        // 2. Générer le HTML avec Twig
        $html = $this->twig->render('employe/evenement/ticket_pdf.html.twig', [
            'participation' => $participation,
            'evenement' => $participation->getEvenement(),
            'utilisateur' => $participation->getUtilisateur(),
            'qrCode' => $qrCodeBase64,
        ]);

        // 3. Configurer et générer le PDF avec Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        // Taille du format (portrait, format A5 ou ticket spécial)
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
