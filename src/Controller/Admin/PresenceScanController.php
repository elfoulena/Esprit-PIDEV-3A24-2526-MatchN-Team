<?php

namespace App\Controller\Admin;

use App\Entity\ParticipationEvenement;
use App\Service\GoogleSheetsService;
use App\Service\PresenceMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/p/scan')]
class PresenceScanController extends AbstractController
{
    #[Route('/{jeton}', name: 'admin_scan_presence', methods: ['GET'])]
    public function scan(
        string $jeton,
        EntityManagerInterface $em,
        PresenceMailerService $mailerService,
        GoogleSheetsService $sheetsService,
        LoggerInterface $logger
    ): Response {
        $logger->info('Début du scan pour le jeton: ' . $jeton);
        
        $repo = $em->getRepository(ParticipationEvenement::class);
        $participation = $repo->findOneBy(['jeton' => $jeton]);
        $logger->info('Recherche de participation terminée.');

        if (!$participation) {
            $logger->warning('Participation non trouvée pour le jeton: ' . $jeton);
            return $this->render('admin/evenement/scan_result.html.twig', [
                'status' => 'error',
                'message' => 'Billet invalide ou introuvable.'
            ]);
        }

        $evenement = $participation->getEvenement();
        $employe = $participation->getUtilisateur();
        $logger->info('Employé trouvé: ' . $employe->getEmail());

        if ($participation->isPresence()) {
            $logger->info('Billet déjà scanné auparavant.');
            return $this->render('admin/evenement/scan_result.html.twig', [
                'status' => 'warning',
                'message' => 'Ce billet a déjà été scanné !',
                'employe' => $employe,
                'evenement' => $evenement
            ]);
        }

        // Marquer la présence
        $logger->info('Marquage de la présence en BDD...');
        $participation->setPresence(true);
        $em->flush();
        $logger->info('BDD mise à jour (flush réussi).');

        // Envoyer l'email
        try {
            $logger->info('Appel MailerService...');
            $mailerService->sendPresenceConfirmation($participation);
            $logger->info('MailerService a terminé.');
        } catch (\Throwable $e) {
            $logger->error('Erreur MailerService: ' . $e->getMessage());
        }

        // Ajouter dans Google Sheets
        try {
            $logger->info('Appel SheetsService...');
            $success = $sheetsService->appendParticipationInfo(
                $employe->getNom(),
                $employe->getPrenom(),
                $employe->getEmail(),
                $evenement->getTitre(),
                new \DateTime()
            );
            $logger->info('SheetsService a terminé. Succès: ' . ($success ? 'OUI' : 'NON'));
        } catch (\Throwable $e) {
            $logger->error('Erreur SheetsService: ' . $e->getMessage());
        }

        return $this->render('admin/evenement/scan_result.html.twig', [
            'status' => 'success',
            'message' => 'Présence validée avec succès !',
            'employe' => $employe,
            'evenement' => $evenement
        ]);
    }
}
