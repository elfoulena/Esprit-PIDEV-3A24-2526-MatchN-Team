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

        // Redirect حسب role
        return match (true) {
            $this->isGranted('ROLE_ADMIN')      => $this->redirectToRoute('admin_dashboard'),
            $this->isGranted('ROLE_EMPLOYE')    => $this->redirectToRoute('employe_dashboard'),
            $this->isGranted('ROLE_FREELANCER') => $this->redirectToRoute('freelancer_dashboard'),
            default                             => $this->redirectToRoute('app_login'),
        };
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