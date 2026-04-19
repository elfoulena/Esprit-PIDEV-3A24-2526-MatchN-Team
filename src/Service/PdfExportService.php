<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;

class PdfExportService
{
    private Environment $twig;
    private Dompdf $dompdf;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', realpath(__DIR__ . '/../../public'));
        
        $this->dompdf = new Dompdf($options);
    }

    public function generateEquipePdf($equipe, string $template, array $extraData = []): Response
    {
        $html = $this->twig->render($template, array_merge([
            'equipe' => $equipe,
            'generated_at' => new \DateTime(),
        ], $extraData));

        $this->dompdf->loadHtml($html, 'UTF-8');
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        $filename = sprintf('equipe_%s_%s.pdf', 
            $equipe->getNomEquipe(), 
            (new \DateTime())->format('Ymd_His')
        );

        return new Response(
            $this->dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\"",
            ]
        );
    }

    public function generateEquipeListPdf(array $equipes, string $template, array $extraData = []): Response
    {
        $html = $this->twig->render($template, array_merge([
            'equipes' => $equipes,
            'generated_at' => new \DateTime(),
            'total' => count($equipes),
        ], $extraData));

        $this->dompdf->loadHtml($html, 'UTF-8');
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->render();

        $filename = sprintf('equipes_list_%s.pdf', (new \DateTime())->format('Ymd_His'));

        return new Response(
            $this->dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\"",
            ]
        );
    }
}