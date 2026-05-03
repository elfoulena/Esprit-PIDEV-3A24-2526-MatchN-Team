<?php

namespace App\Tests\Service;

use App\Entity\Evenement;
use App\Entity\ParticipationEvenement;
use App\Entity\User;
use App\Service\TicketPdfService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TicketPdfServiceTest extends TestCase
{
    public function testGenerateTicketPdf(): void
    {
        // 1. Préparation (comme dans le workshop)
        $user = new User();
        $user->setNom('Nom');
        $user->setPrenom('Prenom');

        $evenement = new Evenement();
        $evenement->setTitre('Event Titre');

        $participation = new ParticipationEvenement();
        $participation->setUtilisateur($user);
        $participation->setEvenement($evenement);
        $participation->setJeton('test-jeton-123');

        // On crée les simulateurs
        $twig = $this->createMock(Environment::class);
        
        $twig->method('render')
            ->willReturn('<html><body>Mock PDF Content</body></html>');

        // 2. Exécution du service (comme $manager = new AuthorManager() dans le workshop)
        $service = new TicketPdfService($twig, 'http://localhost:8000');
        $pdfOutput = $service->generateTicketPdf($participation);

        // 3. Vérification (Assert)
        $this->assertNotEmpty($pdfOutput);
        $this->assertStringContainsString('%PDF', substr($pdfOutput, 0, 5));
    }
}
