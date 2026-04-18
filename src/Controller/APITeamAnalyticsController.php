<?php
// src/Controller/APITeamAnalyticsController.php

namespace App\Controller;

use App\Repository\EquipeRepository;
use App\Service\AITeamAnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class APITeamAnalyticsController extends AbstractController
{
    #[Route('/teams/{id}/analytics', name: 'api_team_analytics', methods: ['GET'])]
    public function getTeamAnalytics(
        int $id,
        EquipeRepository $equipeRepo,
        AITeamAnalyticsService $aiService
    ): JsonResponse {
        $equipe = $equipeRepo->find($id);
        if (!$equipe) {
            return $this->json(['error' => 'Team not found'], 404);
        }
        
        $stats = $aiService->getTeamAdvancedStats($equipe);
        
        return $this->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => (new \DateTime())->format(\DateTime::ISO8601)
        ]);
    }
    
    #[Route('/teams/compare', name: 'api_teams_compare', methods: ['GET'])]
    public function compareTeams(
        EquipeRepository $equipeRepo,
        AITeamAnalyticsService $aiService
    ): JsonResponse {
        $teams = $equipeRepo->findAll();
        $comparison = [];
        
        foreach ($teams as $team) {
            $stats = $aiService->getTeamAdvancedStats($team);
            $comparison[] = [
                'id' => $team->getIdEquipe(),
                'name' => $team->getNomEquipe(),
                'health_score' => $stats['team_metrics']['team_health_score'],
                'productivity' => $stats['team_metrics']['productivity_index'],
                'engagement' => $stats['team_metrics']['engagement_score']
            ];
        }
        
        usort($comparison, fn($a, $b) => $b['health_score'] <=> $a['health_score']);
        
        return $this->json([
            'success' => true,
            'teams' => $comparison,
            'best_team' => $comparison[0] ?? null
        ]);
    }
}