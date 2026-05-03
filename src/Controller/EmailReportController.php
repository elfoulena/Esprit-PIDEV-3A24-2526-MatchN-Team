<?php

namespace App\Controller;

use App\Repository\EquipeRepository;
use App\Service\MailerService;
use App\Service\PdfExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/email-report')]
class EmailReportController extends AbstractController
{
    #[Route('/equipe/{id_equipe}/send', name: 'app_email_report_equipe', methods: ['GET', 'POST'])]
    public function sendEquipeReport(
        int $id_equipe, 
        Request $request,
        EquipeRepository $equipeRepo,
        PdfExportService $pdfExport,
        MailerService $mailer
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée');
        }

        if ($request->isMethod('POST')) {
            $email = trim($request->request->getString('email'));
            $user = $this->getUser();
            if ($email === '') {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_email_report_equipe', ['id_equipe' => $id_equipe]);
            }
            
            // CORRECTION: Vérifier si l'utilisateur existe et utiliser les bons getters
            $adminName = 'Administrateur';
            if ($user instanceof \App\Entity\User) {
                $prenom = $user->getPrenom();
                $nom = $user->getNom();
                if ($prenom && $nom) {
                    $adminName = $prenom . ' ' . $nom;
                } elseif ($nom) {
                    $adminName = $nom;
                } elseif ($prenom) {
                    $adminName = $prenom;
                }
            }
            
            // Générer le PDF
            $pdfResponse = $pdfExport->generateEquipePdf($equipe, 'export/equipe_pdf.html.twig');
            $pdfContent = $pdfResponse->getContent();
            if (!is_string($pdfContent) || $pdfContent === '') {
                $this->addFlash('error', 'Impossible de générer le PDF.');
                return $this->redirectToRoute('app_equipe_show', ['id_equipe' => $equipe->getIdEquipe()]);
            }
            
            // Envoyer l'email
            $mailer->sendEquipeReportPdf($email, $equipe->getNomEquipe() ?? 'Equipe', $pdfContent, $adminName);
            
            $this->addFlash('success', sprintf('Le rapport de l\'équipe "%s" a été envoyé à %s', $equipe->getNomEquipe(), $email));
            return $this->redirectToRoute('app_equipe_show', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('email_report/send_equipe.html.twig', [
            'equipe' => $equipe,
            'admin_email' => $this->getUser()?->getUserIdentifier()
        ]);
    }

    #[Route('/equipes/send-list', name: 'app_email_report_equipes_list', methods: ['GET', 'POST'])]
    public function sendEquipesList(
        Request $request,
        EquipeRepository $equipeRepo,
        PdfExportService $pdfExport,
        MailerService $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim($request->request->getString('email'));
            if ($email === '') {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_email_report_equipes_list');
            }
            
            // Récupérer les filtres actuels
            $search = trim($request->query->getString('search'));
            $statut = trim($request->query->getString('statut'));
            $departement = trim($request->query->getString('departement'));
            $sortBy = trim($request->query->getString('sortBy')) ?: 'dateCreation';
            $sortDir = trim($request->query->getString('sortDir')) ?: 'DESC';

            $allowedSorts = ['dateCreation', 'nomEquipe', 'nbMembresActuel', 'budget', 'statut'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'dateCreation';
            }
            $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

            $qb = $equipeRepo->createQueryBuilder('e');

            if ($search) {
                $qb->andWhere('e.nomEquipe LIKE :s OR e.description LIKE :s')
                   ->setParameter('s', '%' . $search . '%');
            }
            if ($statut) {
                $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
            }
            if ($departement) {
                $qb->andWhere('e.departement = :dep')->setParameter('dep', $departement);
            }

            $equipes = $qb->orderBy('e.' . $sortBy, $sortDir)->getQuery()->getResult();
            
            $user = $this->getUser();
            
            // CORRECTION: Vérifier si l'utilisateur existe et utiliser les bons getters
            $adminName = 'Administrateur';
            if ($user instanceof \App\Entity\User) {
                $prenom = $user->getPrenom();
                $nom = $user->getNom();
                if ($prenom && $nom) {
                    $adminName = $prenom . ' ' . $nom;
                } elseif ($nom) {
                    $adminName = $nom;
                } elseif ($prenom) {
                    $adminName = $prenom;
                }
            }
            
            // Générer le PDF
            $pdfResponse = $pdfExport->generateEquipeListPdf($equipes, 'export/equipes_list_pdf.html.twig', [
                'filters' => compact('search', 'statut', 'departement', 'sortBy', 'sortDir')
            ]);
            $pdfContent = $pdfResponse->getContent();
            if (!is_string($pdfContent) || $pdfContent === '') {
                $this->addFlash('error', 'Impossible de générer le PDF.');
                return $this->redirectToRoute('app_equipe_index');
            }
            
            // Envoyer l'email
            $mailer->sendEquipesListPdf($email, count($equipes), $pdfContent, $adminName);
            
            $this->addFlash('success', sprintf('La liste des %d équipes a été envoyée à %s', count($equipes), $email));
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('email_report/send_equipes_list.html.twig', [
            'admin_email' => $this->getUser()?->getUserIdentifier(),
            'current_filters' => $request->query->all()
        ]);
    }
}
