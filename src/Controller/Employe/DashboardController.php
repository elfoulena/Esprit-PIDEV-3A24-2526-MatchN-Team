<?php

namespace App\Controller\Employe;

use App\Entity\User;
use App\Enum\Role;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE')]
class DashboardController extends AbstractController
{
    #[Route('/employe/dashboard', name: 'employe_dashboard')]
    public function dashboard(
        EntityManagerInterface $em, 
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $employes    = $em->getRepository(User::class)->findBy(['role' => Role::EMPLOYE]);
        $freelancers = $em->getRepository(User::class)->findBy(['role' => Role::FREELANCER]);

        $totalEquipes   = $equipeRepo->count([]);
        $equipesActives = $equipeRepo->count(['statut' => 'Active']);

        $totalMembres = 0;
        foreach ($equipeRepo->findAll() as $eq) {
            $totalMembres += $eq->getNbMembresActuel();
        }

        // Check if current user has a team
        $userTeam = $membreRepo->findOneBy(['user' => $user, 'statutMembre' => 'Actif']);
        $userHasTeam = $userTeam !== null;
        $userTeamId = $userTeam ? $userTeam->getEquipe()->getIdEquipe() : null;

        return $this->render('employe/dashboard.html.twig', [
            'employes'       => $employes,
            'freelancers'    => $freelancers,
            'totalEquipes'   => $totalEquipes,
            'equipesActives' => $equipesActives,
            'totalMembres'   => $totalMembres,
            'userHasTeam'    => $userHasTeam,
            'userTeamId'     => $userTeamId,
        ]);
    }
}