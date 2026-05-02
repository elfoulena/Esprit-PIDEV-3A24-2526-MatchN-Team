<?php

namespace App\Tests\Service;

use App\Entity\Evenement;
use App\Entity\ParticipationEvenement;
use App\Entity\User;
use App\Service\PresenceMailerService;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class PresenceMailerServiceTest extends TestCase
{
    public function testSendPresenceConfirmationSuccess(): void
    {
        // 1. Préparation (comme dans le workshop)
        $user = new User();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');

        $evenement = new Evenement();
        $evenement->setTitre('Atelier Symfony');

        $participation = new ParticipationEvenement();
        $participation->setUtilisateur($user);
        $participation->setEvenement($evenement);

        // Simulation du Mailer
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        // 2. Exécution
        $service = new PresenceMailerService($mailer);
        $service->sendPresenceConfirmation($participation);

        // 3. La vérification est gérée par expects(once())
    }

    public function testSendPresenceConfirmationHandlesException(): void
    {
        // 1. Préparation
        $user = new User();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        
        $participation = new ParticipationEvenement();
        $participation->setUtilisateur($user);
        $participation->setEvenement(new Evenement());

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new \Exception('Mailer failure'));

        // 2. Exécution
        $service = new PresenceMailerService($mailer);
        
        // 3. Vérification que ça ne plante pas
        $this->expectNotToPerformAssertions();
        try {
            $service->sendPresenceConfirmation($participation);
        } catch (\Throwable $e) {
            $this->fail('Le service devrait capturer les exceptions du mailer');
        }
    }
}
