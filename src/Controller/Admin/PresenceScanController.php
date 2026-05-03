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
        $logger->info('Debut du scan pour le jeton: ' . $jeton);

        $repo = $em->getRepository(ParticipationEvenement::class);
        $participation = $repo->findOneBy(['jeton' => $jeton]);
        $logger->info('Recherche de participation terminee.');

        if (!$participation instanceof ParticipationEvenement) {
            $logger->warning('Participation non trouvee pour le jeton: ' . $jeton);

            return $this->render('admin/evenement/scan_result.html.twig', [
                'status' => 'error',
                'message' => 'Billet invalide ou introuvable.',
            ]);
        }

        $evenement = $participation->getEvenement();
        $employe = $participation->getUtilisateur();
        if (!$employe || !$evenement) {
            $logger->warning('Participation incomplete pour le jeton: ' . $jeton);

            return $this->render('admin/evenement/scan_result.html.twig', [
                'status' => 'error',
                'message' => 'Les informations de participation sont incompletes.',
            ]);
        }

        $logger->info('Employe trouve: ' . $employe->getEmail());

        if ($participation->isPresence()) {
            $logger->info('Billet deja scanne auparavant.');

            return $this->render('admin/evenement/scan_result.html.twig', [
                'status' => 'warning',
                'message' => 'Ce billet a deja ete scanne !',
                'employe' => $employe,
                'evenement' => $evenement,
            ]);
        }

        $logger->info('Mise a jour BDD (flush)...');
        $participation->setPresence(true);
        $em->flush();
        $logger->info('BDD mise a jour avec succes.');

        try {
            $logger->info('Step: Mailer Start');
            $mailerService->sendPresenceConfirmation($participation);
            $logger->info('Step: Mailer End');
        } catch (\Throwable $e) {
            $logger->error('Step: Mailer Failed - ' . $e->getMessage());
        }

        try {
            $logger->info('Step: Sheets Start');
            $success = $sheetsService->appendParticipationInfo(
                $employe->getNom() ?? '',
                $employe->getPrenom() ?? '',
                $employe->getEmail() ?? '',
                $evenement->getTitre() ?? '',
                new \DateTime()
            );

            if ($success) {
                $logger->info('Step: Sheets End - Success: YES');
            } else {
                $logger->warning('Step: Sheets End - Success: NO (See service logs for details)');
            }
        } catch (\Throwable $e) {
            $logger->error('Step: Sheets CRITICAL FAILURE - ' . $e->getMessage());
        }

        return $this->render('admin/evenement/scan_result.html.twig', [
            'status' => 'success',
            'message' => 'Presence validee avec succes !',
            'employe' => $employe,
            'evenement' => $evenement,
        ]);
    }
}
