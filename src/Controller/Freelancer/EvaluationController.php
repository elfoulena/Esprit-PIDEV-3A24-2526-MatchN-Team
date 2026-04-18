<?php

namespace App\Controller\Freelancer;

use App\Entity\AffectationProjet;
use App\Entity\EvaluationPartTime;
use App\Repository\AffectationProjetRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_FREELANCER')]
#[Route('/freelancer')]
class EvaluationController extends AbstractController
{
    #[Route('/mes-evaluations', name: 'freelancer_mes_evaluations', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Get all evaluations for this freelancer's affectations
        $evaluations = $em->createQueryBuilder()
            ->select('e', 'a', 'p')
            ->from(EvaluationPartTime::class, 'e')
            ->join('e.affectationProjet', 'a')
            ->join('a.projet', 'p')
            ->where('a.User = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date_evaluation', 'DESC')
            ->getQuery()
            ->getResult();

        // Calculate stats
        $totalEvaluations = count($evaluations);
        $avgNote = 0;
        $bestNote = 0;
        $totalProjects = 0;

        if ($totalEvaluations > 0) {
            $sum = 0;
            foreach ($evaluations as $eval) {
                $sum += $eval->getNote() ?? 0;
                if (($eval->getNote() ?? 0) > $bestNote) {
                    $bestNote = $eval->getNote();
                }
            }
            $avgNote = round($sum / $totalEvaluations, 1);
        }

        // Count finished projects
        $totalProjects = (int) $em->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(AffectationProjet::class, 'a')
            ->where('a.User = :user')
            ->andWhere('a.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'TERMINEE')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('freelancer/mes_evaluations.html.twig', [
            'evaluations'      => $evaluations,
            'totalEvaluations' => $totalEvaluations,
            'avgNote'          => $avgNote,
            'bestNote'         => $bestNote,
            'totalProjects'    => $totalProjects,
        ]);
    }

    #[Route('/mes-evaluations/pdf', name: 'freelancer_evaluations_pdf', methods: ['GET'])]
    public function exportPdf(EntityManagerInterface $em, AffectationProjetRepository $affectationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $evaluations = $em->createQueryBuilder()
            ->select('e', 'a', 'p')
            ->from(EvaluationPartTime::class, 'e')
            ->join('e.affectationProjet', 'a')
            ->join('a.projet', 'p')
            ->where('a.User = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date_evaluation', 'DESC')
            ->getQuery()
            ->getResult();

        $totalEvaluations = count($evaluations);
        $avgNote = 0;
        $bestNote = 0;
        if ($totalEvaluations > 0) {
            $sum = 0;
            foreach ($evaluations as $eval) {
                $sum += $eval->getNote() ?? 0;
                if (($eval->getNote() ?? 0) > $bestNote) {
                    $bestNote = $eval->getNote();
                }
            }
            $avgNote = round($sum / $totalEvaluations, 1);
        }

        $totalProjects = (int) $em->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(AffectationProjet::class, 'a')
            ->where('a.User = :user')
            ->andWhere('a.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'TERMINEE')
            ->getQuery()
            ->getSingleScalarResult();

        // Get leaderboard rank
        $leaderboard = $affectationRepo->getLeaderboardData();
        $rank = null;
        foreach ($leaderboard as $idx => $entry) {
            if ($entry['id'] === $user->getId()) {
                $rank = $idx + 1;
                break;
            }
        }

        $html = $this->renderView('freelancer/evaluations_pdf.html.twig', [
            'user'             => $user,
            'evaluations'      => $evaluations,
            'totalEvaluations' => $totalEvaluations,
            'avgNote'          => $avgNote,
            'bestNote'         => $bestNote,
            'totalProjects'    => $totalProjects,
            'rank'             => $rank,
            'totalFreelancers' => count($leaderboard),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'report_' . strtolower($user->getNom()) . '_' . date('Y-m-d') . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
