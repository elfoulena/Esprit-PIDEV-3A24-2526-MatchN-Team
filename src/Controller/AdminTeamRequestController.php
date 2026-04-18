<?php

namespace App\Controller;

use App\Entity\MembreEquipe;
use App\Entity\TeamRequest;
use App\Entity\User;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use App\Repository\TeamRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/team-requests')]
class AdminTeamRequestController extends AbstractController
{
    #[Route('', name: 'admin_team_requests_index', methods: ['GET'])]
    public function index(TeamRequestRepository $requestRepo, EquipeRepository $equipeRepo): Response
    {
        $pendingRequests = $requestRepo->findBy(['status' => 'pending'], ['createdAt' => 'ASC']);
        $approvedRequests = $requestRepo->findBy(['status' => 'approved'], ['processedAt' => 'DESC']);
        $rejectedRequests = $requestRepo->findBy(['status' => 'rejected'], ['processedAt' => 'DESC']);
        
        $teams = $equipeRepo->findAll();
        
        return $this->render('admin/team_requests/index.html.twig', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'teams' => $teams,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_team_request_approve', methods: ['POST'])]
    public function approve(
        int $id,
        Request $request,
        TeamRequestRepository $requestRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): Response {
        // Find the team request
        $teamRequest = $requestRepo->find($id);
        
        if (!$teamRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        if ($teamRequest->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('approve_request_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        $team = $teamRequest->getTeam();
        $employee = $teamRequest->getEmployee();
        
        // Check if team is full
        if ($team->getNbMembresActuel() >= $team->getNbMembresMax()) {
            $this->addFlash('error', 'L\'équipe a atteint son nombre maximum de membres.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        // Check if employee is already in a team
        $existingMembre = $membreRepo->findOneBy(['user' => $employee, 'statutMembre' => 'Actif']);
        if ($existingMembre) {
            $this->addFlash('error', 'Cet employé est déjà membre d\'une équipe.');
            $teamRequest->setStatus('rejected');
            $teamRequest->setProcessedAt(new \DateTime());
            $adminUser = $this->getUser();
            if ($adminUser && $adminUser instanceof User) {
                $teamRequest->setProcessedByUser($adminUser);
            }
            $teamRequest->setAdminNotes('L\'employé est déjà membre d\'une autre équipe.');
            $em->flush();
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        try {
            // Create membre record
            $membre = new MembreEquipe();
            $membre->setEquipe($team);
            $membre->setUser($employee);
            $membre->setRoleEquipe($teamRequest->getRequestedRole() ?? 'Membre');
            $membre->setDateAffectation(new \DateTime());
            $membre->setStatutMembre('Actif');
            $membre->setTauxParticipation('100.00');
            
            // Update team member count
            $currentCount = $team->getNbMembresActuel();
            $team->setNbMembresActuel($currentCount + 1);
            
            // Update request status
            $teamRequest->setStatus('approved');
            $teamRequest->setProcessedAt(new \DateTime());
            $adminUser = $this->getUser();
            if ($adminUser && $adminUser instanceof User) {
                $teamRequest->setProcessedByUser($adminUser);
            }
            
            $adminNotes = $request->request->get('admin_notes');
            if ($adminNotes) {
                $teamRequest->setAdminNotes($adminNotes);
            }
            
            // Save everything
            $em->persist($membre);
            $em->flush();
            
            $this->addFlash('success', sprintf(
                'La demande de %s %s a été approuvée et ajouté à l\'équipe %s.', 
                $employee->getPrenom(), 
                $employee->getNom(),
                $team->getNomEquipe()
            ));
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_team_requests_index');
    }

    #[Route('/{id}/reject', name: 'admin_team_request_reject', methods: ['POST'])]
    public function reject(
        int $id,
        Request $request,
        TeamRequestRepository $requestRepo,
        EntityManagerInterface $em
    ): Response {
        // Find the team request
        $teamRequest = $requestRepo->find($id);
        
        if (!$teamRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        if ($teamRequest->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('reject_request_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_team_requests_index');
        }
        
        try {
            // Get rejection reason
            $reason = $request->request->get('rejection_reason');
            
            // Update request status
            $teamRequest->setStatus('rejected');
            $teamRequest->setProcessedAt(new \DateTime());
            $adminUser = $this->getUser();
            if ($adminUser && $adminUser instanceof User) {
                $teamRequest->setProcessedByUser($adminUser);
            }
            $teamRequest->setAdminNotes($reason ?? 'Demande rejetée par l\'administrateur');
            
            $em->flush();
            
            $this->addFlash('success', 'La demande a été rejetée.');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_team_requests_index');
    }

    #[Route('/team/{id_equipe}', name: 'admin_team_requests_by_team', methods: ['GET'])]
    public function requestsByTeam(
        int $id_equipe,
        EquipeRepository $equipeRepo,
        TeamRequestRepository $requestRepo
    ): Response {
        $team = $equipeRepo->find($id_equipe);
        
        if (!$team) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }
        
        $pendingRequests = $requestRepo->findPendingByTeam($team);
        
        return $this->render('admin/team_requests/by_team.html.twig', [
            'team' => $team,
            'pendingRequests' => $pendingRequests,
        ]);
    }
}