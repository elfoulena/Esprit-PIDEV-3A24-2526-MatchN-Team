<?php

namespace App\Controller;

use App\Repository\AffectationProjetRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LeaderboardController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/leaderboard', name: 'app_admin_leaderboard', methods: ['GET'])]
    public function adminLeaderboard(AffectationProjetRepository $repo): Response
    {
        $repo->updateExpiredAffectations();

        return $this->render('leaderboard/index.html.twig', [
            'leaderboard' => $repo->getLeaderboardData(),
            'layout' => 'admin',
        ]);
    }

    #[IsGranted('ROLE_FREELANCER')]
    #[Route('/freelancer/leaderboard', name: 'app_freelancer_leaderboard', methods: ['GET'])]
    public function freelancerLeaderboard(AffectationProjetRepository $repo): Response
    {
        $repo->updateExpiredAffectations();

        /** @var User|null $user */
        $user = $this->getUser();
        return $this->render('leaderboard/index.html.twig', [
            'leaderboard' => $repo->getLeaderboardData(),
            'layout' => 'freelancer',
            'currentUserId' => $user ? $user->getId() : null,
        ]);
    }
}
