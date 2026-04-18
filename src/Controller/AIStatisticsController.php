<?php
// src/Controller/AIStatisticsController.php

namespace App\Controller;

use App\Entity\Equipe;
use App\Repository\EquipeRepository;
use App\Service\AITeamAnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/ai-statistics')]
class AIStatisticsController extends AbstractController
{
    #[Route('/equipe/{id_equipe}', name: 'app_ai_team_stats', methods: ['GET'])]
    public function teamStatistics(
        int $id_equipe,
        EquipeRepository $equipeRepo,
        AITeamAnalyticsService $aiService
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }
        
        $stats = $aiService->getTeamAdvancedStats($equipe);
        
        return $this->render('ai_statistics/team_stats.html.twig', [
            'equipe' => $equipe,
            'stats' => $stats,
        ]);
    }
    
    #[Route('/api/equipe/{id_equipe}/stats', name: 'api_ai_team_stats', methods: ['GET'])]
    public function apiTeamStatistics(
        int $id_equipe,
        EquipeRepository $equipeRepo,
        AITeamAnalyticsService $aiService
    ): JsonResponse {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            return $this->json(['error' => 'Équipe introuvable'], 404);
        }
        
        $stats = $aiService->getTeamAdvancedStats($equipe);
        
        return $this->json($stats);
    }
    
    #[Route('/dashboard', name: 'app_ai_dashboard', methods: ['GET'])]
    public function dashboard(EquipeRepository $equipeRepo): Response
    {
        $equipes = $equipeRepo->findAll();
        
        return $this->render('ai_statistics/dashboard.html.twig', [
            'equipes' => $equipes,
        ]);
    }
}