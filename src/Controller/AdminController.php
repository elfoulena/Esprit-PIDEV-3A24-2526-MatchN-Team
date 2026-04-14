<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\User;
use App\Enum\Role;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em, EquipeRepository $equipeRepo): Response
    {
        $employes    = $em->getRepository(User::class)->findBy(['role' => Role::EMPLOYE]);
        $freelancers = $em->getRepository(User::class)->findBy(['role' => Role::FREELANCER]);

        $totalEquipes   = $equipeRepo->count([]);
        $equipesActives = $equipeRepo->count(['statut' => 'Active']);

        $totalMembres = 0;
        foreach ($equipeRepo->findAll() as $eq) {
            $totalMembres += $eq->getNbMembresActuel();
        }

        return $this->render('admin/dashboard.html.twig', [
            'employes'       => $employes,
            'freelancers'    => $freelancers,
            'totalEquipes'   => $totalEquipes,
            'equipesActives' => $equipesActives,
            'totalMembres'   => $totalMembres,
        ]);
    }

    #[Route('/employes/{id}/toggle', name: 'admin_toggle_employe', methods: ['POST'])]
    public function toggleStatus(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $user->setActif(!$user->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('admin_dashboard');
    }
}