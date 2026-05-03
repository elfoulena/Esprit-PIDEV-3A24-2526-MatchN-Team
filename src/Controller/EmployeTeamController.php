<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\MembreEquipe;
use App\Entity\TeamRequest;
use App\Entity\User;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use App\Repository\TeamRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE')]
#[Route('/employe/team')]
class EmployeTeamController extends AbstractController
{
    #[Route('/my-team', name: 'employe_my_team', methods: ['GET'])]
    public function myTeam(MembreEquipeRepository $membreRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $membre = $membreRepo->findOneBy(['user' => $user, 'statutMembre' => 'Actif']);
        
        if (!$membre instanceof MembreEquipe) {
            $this->addFlash('info', 'Vous n\'êtes actuellement membre d\'aucune équipe.');
            return $this->redirectToRoute('employe_available_teams');
        }
        
        $equipe = $membre->getEquipe();
        
        return $this->render('employe/my_team.html.twig', [
            'equipe' => $equipe,
            'membre' => $membre,
        ]);
    }

    #[Route('/available-teams', name: 'employe_available_teams', methods: ['GET'])]
    public function availableTeams(
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        TeamRequestRepository $requestRepo
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is already in a team
        $currentMembre = $membreRepo->findOneBy(['user' => $user, 'statutMembre' => 'Actif']);
        if ($currentMembre) {
            $this->addFlash('warning', 'Vous êtes déjà membre d\'une équipe. Vous ne pouvez pas rejoindre une autre équipe.');
            return $this->redirectToRoute('employe_my_team');
        }
        
        // Get pending requests
        $pendingRequests = $requestRepo->findPendingByEmployee($user);
        $pendingTeamIds = array_map(
            static fn(TeamRequest $teamRequest): ?int => $teamRequest->getTeam()?->getIdEquipe(),
            $pendingRequests
        );
        $pendingTeamIds = array_values(array_filter($pendingTeamIds, static fn(?int $id): bool => $id !== null));
        
        // Get all active teams
        $search = trim($request->query->getString('search'));
        $departement = trim($request->query->getString('departement'));
        
        $qb = $equipeRepo->createQueryBuilder('e');
        $qb->where('e.statut = :statut')
           ->setParameter('statut', 'Active');
        
        if ($search) {
            $qb->andWhere('e.nomEquipe LIKE :search OR e.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        if ($departement) {
            $qb->andWhere('e.departement = :departement')
               ->setParameter('departement', $departement);
        }
        
        $teams = $qb->getQuery()->getResult();
        
        // Get all departments for filter
        $departements = $equipeRepo->createQueryBuilder('e')
            ->select('DISTINCT e.departement')
            ->where('e.departement IS NOT NULL')
            ->getQuery()
            ->getResult();
        $departements = array_column($departements, 'departement');
        
        return $this->render('employe/available_teams.html.twig', [
            'teams' => $teams,
            'pendingTeamIds' => $pendingTeamIds,
            'search' => $search,
            'departement' => $departement,
            'departements' => $departements,
        ]);
    }

    #[Route('/request/{id_equipe}', name: 'employe_request_team', methods: ['GET', 'POST'])]
    public function requestTeam(
        int $id_equipe,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        TeamRequestRepository $requestRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is already in a team
        $currentMembre = $membreRepo->findOneBy(['user' => $user, 'statutMembre' => 'Actif']);
        if ($currentMembre) {
            $this->addFlash('error', 'Vous êtes déjà membre d\'une équipe.');
            return $this->redirectToRoute('employe_my_team');
        }
        
        $team = $equipeRepo->find($id_equipe);
        if (!$team) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }
        
        // Check if team is full
        if ($team->getNbMembresActuel() >= $team->getNbMembresMax()) {
            $this->addFlash('error', 'Cette équipe a atteint son nombre maximum de membres.');
            return $this->redirectToRoute('employe_available_teams');
        }
        
        // Check if user already has a pending request for this team
        $existingRequest = $requestRepo->findOneBy([
            'employee' => $user,
            'team' => $team,
            'status' => 'pending'
        ]);
        
        if ($existingRequest) {
            $this->addFlash('warning', 'Vous avez déjà une demande en attente pour cette équipe.');
            return $this->redirectToRoute('employe_available_teams');
        }
        
        if ($request->isMethod('POST')) {
            $message = trim($request->request->getString('message'));
            $requestedRole = trim($request->request->getString('requestedRole')) ?: 'Membre';
            
            $teamRequest = new TeamRequest();
            $teamRequest->setEmployee($user);
            $teamRequest->setTeam($team);
            $teamRequest->setMessage($message);
            $teamRequest->setRequestedRole($requestedRole);
            $teamRequest->setStatus('pending');
            
            $em->persist($teamRequest);
            $em->flush();
            
            $this->addFlash('success', 'Votre demande a été envoyée à l\'administrateur.');
            return $this->redirectToRoute('employe_my_requests');
        }
        
        return $this->render('employe/request_team.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/my-requests', name: 'employe_my_requests', methods: ['GET'])]
    public function myRequests(TeamRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $requests = $requestRepo->findRequestsByEmployee($user);
        
        return $this->render('employe/my_requests.html.twig', [
            'requests' => $requests,
        ]);
    }

    #[Route('/cancel-request/{id}', name: 'employe_cancel_request', methods: ['POST'])]
    public function cancelRequest(
        int $id,
        Request $request,
        TeamRequestRepository $requestRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $teamRequest = $requestRepo->find($id);
        
        if (
            !$teamRequest instanceof TeamRequest
            || !$teamRequest->getEmployee()
            || $teamRequest->getEmployee()->getId() !== $user->getId()
        ) {
            throw $this->createNotFoundException('Demande non trouvée.');
        }
        
        if ($teamRequest->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette demande ne peut plus être annulée.');
            return $this->redirectToRoute('employe_my_requests');
        }
        
        if ($this->isCsrfTokenValid('cancel_request_' . $id, $request->request->getString('_token'))) {
            $em->remove($teamRequest);
            $em->flush();
            $this->addFlash('success', 'Votre demande a été annulée.');
        }
        
        return $this->redirectToRoute('employe_my_requests');
    }
}
