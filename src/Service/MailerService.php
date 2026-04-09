<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendConfirmationEmail(string $to, string $nom, string $code): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('Code de vérification')
            ->html("
                <h2>Bonjour {$nom},</h2>
                <p>Votre code de vérification est : <strong style='font-size:24px'>{$code}</strong></p>
                <p>Ce code expire dans 24h.</p>
            ");

        $this->mailer->send($email);
    }

    public function sendEmployeeCredentials(string $to, string $nom, string $plainPassword): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('Vos identifiants de connexion')
            ->html("
                <h2>Bonjour {$nom},</h2>
                <p>Votre compte a été créé par l'administrateur.</p>
                <ul>
                    <li><strong>Email :</strong> {$to}</li>
                    <li><strong>Mot de passe :</strong> {$plainPassword}</li>
                </ul>
                <p><strong>Changez votre mot de passe dès la première connexion.</strong></p>
            ");

        $this->mailer->send($email);
    }

    public function sendResetPasswordEmail(string $to, string $nom, string $code): void
    {
        $email = (new Email())
            ->from('noreply@tonapp.com')
            ->to($to)
            ->subject('Réinitialisation de mot de passe')
            ->html("
                <h2>Bonjour {$nom},</h2>
                <p>Votre code de réinitialisation : <strong style='font-size:24px'>{$code}</strong></p>
                <p>Ce code expire dans 15 minutes.</p>
            ");

        $this->mailer->send($email);
    }
}