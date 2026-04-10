<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        // If NOT logged in → go to login
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Admin → back-office dashboard
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // Freelancer
        if ($this->isGranted('ROLE_FREELANCER')) {
            return $this->redirectToRoute('freelancer_dashboard');
        }

        // Employé (ou tout autre rôle) → page front
        return $this->render('front/index.html.twig');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If already logged in → go home
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony handles logout automatically
        throw new \LogicException('This method can be blank.');
    }
}