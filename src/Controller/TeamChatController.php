<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE')]
#[Route('/team-chat')]
class TeamChatController extends AbstractController
{
    #[Route('/{id_equipe}', name: 'team_chat_index', methods: ['GET'])]
    public function index(
        int $id_equipe,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is member of this team
        $membre = $membreRepo->findOneBy(['user' => $user, 'equipe' => $id_equipe, 'statutMembre' => 'Actif']);
        
        if (!$membre) {
            $this->addFlash('error', 'Vous n\'êtes pas membre de cette équipe.');
            return $this->redirectToRoute('employe_my_team');
        }
        
        $equipe = $equipeRepo->find($id_equipe);
        
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }
        
        return $this->render('team_chat/index.html.twig', [
            'equipe' => $equipe,
            'currentUser' => $user,
        ]);
    }

    #[Route('/api/messages/{id_equipe}', name: 'team_chat_get_messages', methods: ['GET'])]
    public function getMessages(
        int $id_equipe,
        Request $request,
        EquipeRepository $equipeRepo,
        MessageRepository $messageRepo,
        MembreEquipeRepository $membreRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is member of this team
        $membre = $membreRepo->findOneBy(['user' => $user, 'equipe' => $id_equipe, 'statutMembre' => 'Actif']);
        
        if (!$membre) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }
        
        $equipe = $equipeRepo->find($id_equipe);
        
        if (!$equipe) {
            return $this->json(['error' => 'Équipe non trouvée'], 404);
        }
        
        $lastId = $request->query->getInt('lastId', 0);
        $messages = $messageRepo->findRecentMessagesByTeam($equipe, $lastId);
        
        $formattedMessages = array_map(function (Message $message): array {
            return [
                'id' => $message->getIdMessage(),
                'sender_id' => $message->getIdExpediteur(),
                'sender_name' => $message->getNomExpediteur(),
                'content' => $message->getContenu(),
                'time' => $message->getDateEnvoi()?->format('H:i'),
                'date' => $message->getDateEnvoi()?->format('d/m/Y'),
                'full_date' => $message->getDateEnvoi()?->format('Y-m-d H:i:s')
            ];
        }, $messages);
        
        return $this->json($formattedMessages);
    }

    #[Route('/api/messages/{id_equipe}', name: 'team_chat_send_message', methods: ['POST'])]
    public function sendMessage(
        int $id_equipe,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is member of this team
        $membre = $membreRepo->findOneBy(['user' => $user, 'equipe' => $id_equipe, 'statutMembre' => 'Actif']);
        
        if (!$membre) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }
        
        $equipe = $equipeRepo->find($id_equipe);
        
        if (!$equipe) {
            return $this->json(['error' => 'Équipe non trouvée'], 404);
        }
        
        $decoded = json_decode($request->getContent(), true);
        $data = is_array($decoded) ? $decoded : [];
        $content = isset($data['content']) && is_string($data['content']) ? trim($data['content']) : '';
        
        if (empty($content)) {
            return $this->json(['error' => 'Le message ne peut pas être vide'], 400);
        }
        
        $message = new Message();
        $message->setIdExpediteur($user->getId() ?? 0);
        $message->setNomExpediteur($user->getPrenom() . ' ' . $user->getNom());
        $message->setContenu($content);
        $message->setEquipe($equipe);
        $message->setDateEnvoi(new \DateTime());
        $message->setEstSupprime(false);
        
        $em->persist($message);
        $em->flush();
        
        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getIdMessage(),
                'sender_id' => $message->getIdExpediteur(),
                'sender_name' => $message->getNomExpediteur(),
                'content' => $message->getContenu(),
                'time' => $message->getDateEnvoi()?->format('H:i'),
                'date' => $message->getDateEnvoi()?->format('d/m/Y')
            ]
        ]);
    }
}
