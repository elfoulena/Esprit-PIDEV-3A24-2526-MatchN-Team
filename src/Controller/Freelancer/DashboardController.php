<?php

namespace App\Controller\Freelancer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_FREELANCER')]
class DashboardController extends AbstractController
{
    #[Route('/freelancer/dashboard', name: 'freelancer_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('freelancer/dashboard.html.twig');
    }
}
