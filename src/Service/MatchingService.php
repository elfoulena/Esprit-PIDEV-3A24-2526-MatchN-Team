<?php

namespace App\Service;

use App\Entity\AffectationProjet;
use App\Entity\Projet;
use App\Entity\User;
use App\Enum\Role;
use App\Repository\AffectationProjetRepository;
use Doctrine\ORM\EntityManagerInterface;

class MatchingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AffectationProjetRepository $affectationRepo,
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Returns ranked freelancer recommendations for a given project.
     * @return array<int, array<string, mixed>>
     */
    public function getRecommendations(Projet $projet): array
    {
        // 1. Get project required competence names (uppercase for comparison)
        $requiredSkills = [];
        foreach ($projet->getCompetences() as $comp) {
            $requiredSkills[] = mb_strtoupper(trim($comp->getNomCompetence() ?? ''));
        }

        // 2. Fetch all active freelancers with their competences
        $freelancers = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.role = :role')
            ->andWhere('u.actif = true')
            ->setParameter('role', Role::FREELANCER)
            ->getQuery()
            ->getResult();

        // 3. Get leaderboard data indexed by user ID
        $leaderboardData = [];
        $maxScore = 1;
        foreach ($this->affectationRepo->getLeaderboardData() as $entry) {
            $leaderboardData[$entry['id']] = $entry;
            if ($entry['score'] > $maxScore) {
                $maxScore = $entry['score'];
            }
        }

        // 4. Score each freelancer
        $recommendations = [];
        foreach ($freelancers as $freelancer) {
            $freelancerSkills = [];
            foreach ($freelancer->getCompetences() as $comp) {
                $freelancerSkills[] = mb_strtoupper(trim($comp->getNom()));
            }

            // Competence match
            $matchedSkills = [];
            if (count($requiredSkills) > 0) {
                $matchedSkills = array_intersect($requiredSkills, $freelancerSkills);
                $matchPercent = (count($matchedSkills) / count($requiredSkills)) * 100;
            } else {
                $matchPercent = $freelancerSkills ? 50 : 0;
            }

            // Availability
            $available = $this->isAvailable($freelancer->getId(), $projet->getDateDebut(), $projet->getDateFin());

            // Leaderboard score (normalized 0-100)
            $lbEntry = $leaderboardData[$freelancer->getId()] ?? null;
            $lbScore = $lbEntry ? ($lbEntry['score'] / $maxScore) * 100 : 0;

            // Budget fit
            $budgetFit = $this->checkBudgetFit($freelancer->getId(), $projet);

            // Composite score: 40% competence + 30% leaderboard + 20% availability + 10% budget
            $totalScore = ($matchPercent * 0.4)
                + ($lbScore * 0.3)
                + ($available ? 20 : 0)
                + ($budgetFit ? 10 : 0);

            $recommendations[] = [
                'userId'             => $freelancer->getId(),
                'nom'                => $freelancer->getNom(),
                'prenom'             => $freelancer->getPrenom(),
                'email'              => $freelancer->getEmail(),
                'matchedCompetences' => array_values($matchedSkills),
                'freelancerSkills'   => $freelancerSkills,
                'matchPercent'       => round($matchPercent, 1),
                'leaderboardScore'   => $lbEntry ? round($lbEntry['score'], 1) : 0,
                'available'          => $available,
                'budgetFit'          => $budgetFit,
                'totalScore'         => round($totalScore, 1),
                'aiExplanation'      => null,
            ];
        }

        // 5. Sort by total score DESC
        usort($recommendations, fn($a, $b) => $b['totalScore'] <=> $a['totalScore']);

        // 6. Keep top 10
        $recommendations = array_slice($recommendations, 0, 10);

        // 7. Generate AI explanation for top 3
        $requiredSkillNames = [];
        foreach ($projet->getCompetences() as $comp) {
            $requiredSkillNames[] = $comp->getNomCompetence();
        }

        for ($i = 0; $i < min(3, count($recommendations)); $i++) {
            $r = $recommendations[$i];
            $recommendations[$i]['aiExplanation'] = $this->geminiService->generateMatchExplanation(
                ($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? ''),
                $r['freelancerSkills'] ?? ['Aucune'],
                $projet->getTitre() ?? '',
                array_values(array_filter($requiredSkillNames, fn($skill) => $skill !== null))
            );
        }

        return $recommendations;
    }

    private function isAvailable(int $userId, ?\DateTimeInterface $projetDebut, ?\DateTimeInterface $projetFin): bool
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(AffectationProjet::class, 'a')
            ->where('a.User = :userId')
            ->andWhere('a.statut IN (:activeStatuts)')
            ->setParameter('userId', $userId)
            ->setParameter('activeStatuts', ['EN_ATTENTE', 'ACCEPTEE']);

        if ($projetDebut && $projetFin) {
            $qb->andWhere('a.date_debut <= :projetFin')
               ->andWhere('(a.date_fin IS NULL OR a.date_fin >= :projetDebut)')
               ->setParameter('projetDebut', $projetDebut)
               ->setParameter('projetFin', $projetFin);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() === 0;
    }

    private function checkBudgetFit(int $userId, Projet $projet): bool
    {
        $budgetFreelance = $projet->getBudgetFreelance();
        if (!$budgetFreelance) {
            return true;
        }

        // Get average hourly rate from past affectations
        $avgRate = $this->em->createQueryBuilder()
            ->select('AVG(a.taux_horaire)')
            ->from(AffectationProjet::class, 'a')
            ->where('a.User = :userId')
            ->andWhere('a.taux_horaire IS NOT NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$avgRate) {
            return true;
        }

        return $avgRate <= (float) $budgetFreelance;
    }
}
