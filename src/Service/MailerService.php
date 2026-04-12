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

    public function sendEmployeWelcomeEmail(string $to, string $nom, string $plainPassword, string $code): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('Bienvenue — Votre compte Employé MatchNTeam')
            ->html("
                <h2>Bonjour {$nom},</h2>
                <p>L'administrateur a créé votre compte <strong>Employé</strong> sur MatchNTeam.</p>
                <h3>Vos identifiants de connexion :</h3>
                <ul>
                    <li><strong>Email :</strong> {$to}</li>
                    <li><strong>Mot de passe :</strong> {$plainPassword}</li>
                </ul>
                <h3>Code de vérification :</h3>
                <p style='font-size:28px; font-weight:bold; letter-spacing:6px; color:#3b82f6;'>{$code}</p>
                <p>Ce code expire dans <strong>24 heures</strong>. Vous devrez le saisir lors de votre première connexion.</p>
                <p style='color:#64748b;'>Pensez à changer votre mot de passe après votre première connexion.</p>
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