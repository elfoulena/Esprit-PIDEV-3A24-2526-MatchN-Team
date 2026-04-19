<?php

namespace App\Controller;

use App\Repository\EquipeRepository;
use App\Service\PdfExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/export')]
class ExportController extends AbstractController
{
    #[Route('/equipe/{id_equipe}/pdf', name: 'app_export_equipe_pdf', methods: ['GET'])]
    public function exportEquipePdf(int $id_equipe, EquipeRepository $repo, PdfExportService $pdfExport): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée');
        }

        return $pdfExport->generateEquipePdf($equipe, 'export/equipe_pdf.html.twig');
    }

    #[Route('/equipes/pdf', name: 'app_export_equipes_pdf', methods: ['GET'])]
    public function exportEquipesPdf(Request $request, EquipeRepository $repo, PdfExportService $pdfExport): Response
    {
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

        $qb = $repo->createQueryBuilder('e');

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

        return $pdfExport->generateEquipeListPdf($equipes, 'export/equipes_list_pdf.html.twig', [
            'filters' => [
                'search' => $search,
                'statut' => $statut,
                'departement' => $departement,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ]
        ]);
    }
}