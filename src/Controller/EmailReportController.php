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
            $email = $request->request->get('email');
            $user = $this->getUser();
            assert($user instanceof \App\Entity\User);
            
            // CORRECTION: Vérifier si l'utilisateur existe et utiliser les bons getters
            $adminName = 'Administrateur';
            if ($user) {
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
            
            // Envoyer l'email
            $mailer->sendEquipeReportPdf($email, $equipe->getNomEquipe(), $pdfContent, $adminName);
            
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
            $email = $request->request->get('email');
            
            // Récupérer les filtres actuels
            $search = $request->query->get('search', '');
            $statut = $request->query->get('statut', '');
            $departement = $request->query->get('departement', '');
            $sortBy = $request->query->get('sortBy', 'dateCreation');
            $sortDir = $request->query->get('sortDir', 'DESC');

            $allowedSorts = ['dateCreation', 'nomEquipe', 'nbMembresActuel', 'budget', 'statut'];
            if (!in_array($sortBy, $allowedSorts)) {
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
            assert($user instanceof \App\Entity\User);
            
            // CORRECTION: Vérifier si l'utilisateur existe et utiliser les bons getters
            $adminName = 'Administrateur';
            if ($user) {
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