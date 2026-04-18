<?php

namespace App\Service;

use App\Entity\ParticipationEvenement;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class PresenceMailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendPresenceConfirmation(ParticipationEvenement $participation): void
    {
        $employe = $participation->getUtilisateur();
        $evenement = $participation->getEvenement();

        // Notification uniquement à l'admin (bochra.damak@esprit.tn)
        $email = (new TemplatedEmail())
            ->from(new Address('bochra.damak@esprit.tn', 'MatchNTeam Admin')) // Assurez-vous que cet e-mail est celui configuré dans MAILER_DSN
            ->to('bochra.damak@esprit.tn')
            ->subject('Nouveau scan de présence : ' . $employe->getPrenom() . ' ' . $employe->getNom())
            ->htmlTemplate('email/presence_confirmee.html.twig')
            ->context([
                'employe' => $employe,
                'evenement' => $evenement,
                'dateScan' => new \DateTime(),
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            // Logger l'erreur (le scan ne doit pas échouer si le mail échoue)
        }
    }
}
