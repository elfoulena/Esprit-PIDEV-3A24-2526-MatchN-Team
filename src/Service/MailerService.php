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
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('Réinitialisation de mot de passe')
            ->html("
                <h2>Bonjour {$nom},</h2>
                <p>Votre code de réinitialisation : <strong style='font-size:24px'>{$code}</strong></p>
                <p>Ce code expire dans 15 minutes.</p>
            ");

        $this->mailer->send($email);
    }

    public function sendSecurityAlertEmail(string $to, string $nom, string $ip, string $country): void
{
    $email = (new Email())
        ->from('amenamri80@gmail.com')
        ->to($to)
        ->subject('Alerte sécurité - connexion suspecte')
        ->html("
            <h2>Bonjour {$nom},</h2>
            <p>Une connexion suspecte a été détectée sur votre compte.</p>
            <ul>
                <li>IP : {$ip}</li>
                <li>Pays : {$country}</li>
            </ul>
            <p>Votre compte est temporairement bloqué pendant 1 heure.</p>
        ");

    $this->mailer->send($email);
}
public function sendEquipeReportPdf(string $to, string $nomEquipe, string $pdfContent, string $adminName = ''): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('📊 Rapport PDF - Équipe ' . $nomEquipe)
            ->html($this->getEquipeReportHtml($nomEquipe, $adminName))
            ->attach($pdfContent, sprintf('rapport_equipe_%s.pdf', $nomEquipe), 'application/pdf');

        $this->mailer->send($email);
    }

    /**
     * Envoie la liste complète des équipes en PDF
     */
    public function sendEquipesListPdf(string $to, int $totalEquipes, string $pdfContent, string $adminName = ''): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject('📋 Liste des équipes - MatchNTeam')
            ->html($this->getEquipesListHtml($totalEquipes, $adminName))
            ->attach($pdfContent, sprintf('liste_equipes_%s.pdf', (new \DateTime())->format('Y-m-d')), 'application/pdf');

        $this->mailer->send($email);
    }

    /**
     * Envoie un rapport personnalisé avec plusieurs équipes
     */
    public function sendCustomReport(string $to, string $subject, string $message, string $pdfContent, string $filename): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($message)
            ->attach($pdfContent, $filename, 'application/pdf');

        $this->mailer->send($email);
    }

    /**
     * Envoie un email avec pièce jointe à plusieurs destinataires
     */
    /**
     * @param array<int, string> $recipients
     */
    public function sendBulkEquipeReport(array $recipients, string $nomEquipe, string $pdfContent, string $adminName = ''): void
    {
        $email = (new Email())
            ->from('amenamri80@gmail.com')
            ->to(...$recipients)
            ->subject('📊 Rapport PDF - Équipe ' . $nomEquipe)
            ->html($this->getEquipeReportHtml($nomEquipe, $adminName))
            ->attach($pdfContent, sprintf('rapport_equipe_%s.pdf', $nomEquipe), 'application/pdf');

        $this->mailer->send($email);
    }

    /**
     * Template HTML pour le rapport d'équipe
     */
    private function getEquipeReportHtml(string $nomEquipe, string $adminName = ''): string
    {
        $greeting = $adminName ? "Bonjour {$adminName}," : "Bonjour,";
        
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px; }
                    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; }
                    .btn { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-top: 15px; }
                    .highlight { background: #eff6ff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #3b82f6; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h2>📊 MatchNTeam</h2>
                    <p>Rapport d'équipe</p>
                </div>
                <div class='content'>
                    <h3>{$greeting}</h3>
                    <p>Vous trouverez ci-joint le rapport détaillé de l'équipe <strong>{$nomEquipe}</strong> au format PDF.</p>
                    
                    <div class='highlight'>
                        <strong>📄 Contenu du rapport :</strong>
                        <ul>
                            <li>Informations générales de l'équipe</li>
                            <li>Statistiques des membres</li>
                            <li>Budget et ressources</li>
                            <li>Liste complète des membres</li>
                        </ul>
                    </div>
                    
                    <p>Ce rapport a été généré automatiquement le " . (new \DateTime())->format('d/m/Y à H:i') . ".</p>
                    
                    <p style='margin-top: 20px;'>
                        <small>📎 Le fichier PDF est joint à cet email.</small>
                    </p>
                </div>
                <div class='footer'>
                    <p>MatchNTeam - Gestion d'équipes professionnelle</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Template HTML pour la liste des équipes
     */
    private function getEquipesListHtml(int $totalEquipes, string $adminName = ''): string
    {
        $greeting = $adminName ? "Bonjour {$adminName}," : "Bonjour,";
        
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px; }
                    .stats { background: #eff6ff; padding: 15px; border-radius: 8px; margin: 15px 0; text-align: center; }
                    .stats-number { font-size: 32px; font-weight: bold; color: #3b82f6; }
                    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h2>📋 MatchNTeam</h2>
                    <p>Liste complète des équipes</p>
                </div>
                <div class='content'>
                    <h3>{$greeting}</h3>
                    <p>Vous trouverez ci-joint la liste complète de toutes les équipes au format PDF.</p>
                    
                    <div class='stats'>
                        <div class='stats-number'>{$totalEquipes}</div>
                        <div>équipe(s) au total</div>
                    </div>
                    
                    <div class='highlight'>
                        <strong>📄 Le rapport PDF contient :</strong>
                        <ul>
                            <li>Liste détaillée de toutes les équipes</li>
                            <li>Informations par équipe (nom, département, statut)</li>
                            <li>Effectifs et budgets</li>
                            <li>Dates de création</li>
                        </ul>
                    </div>
                    
                    <p>Ce rapport a été généré le " . (new \DateTime())->format('d/m/Y à H:i') . ".</p>
                    
                    <p style='margin-top: 20px;'>
                        <small>📎 Le fichier PDF est joint à cet email.</small>
                    </p>
                </div>
                <div class='footer'>
                    <p>MatchNTeam - Gestion d'équipes professionnelle</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </body>
            </html>
        ";
    }
}
