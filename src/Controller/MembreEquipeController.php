<?php

namespace App\Controller;

use App\Entity\MembreEquipe;
use App\Form\MembreEquipeType;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use App\Service\WhatsAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/equipe/{id_equipe}/membre')]
class MembreEquipeController extends AbstractController
{
    #[Route('', name: 'app_membre_equipe_index', methods: ['GET'])]
    public function index(int $id_equipe, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membres = $membreRepo->findBy(['equipe' => $equipe]);

        return $this->render('membre_equipe/index.html.twig', [
            'equipe' => $equipe,
            'membres' => $membres,
        ]);
    }

    #[Route('/new', name: 'app_membre_equipe_new', methods: ['GET', 'POST'])]
    public function new(int $id_equipe, Request $request, EquipeRepository $equipeRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        // Vérifier si l'équipe n'a pas atteint son nombre maximum de membres
        if ($equipe->getNbMembresActuel() >= $equipe->getNbMembresMax()) {
            $this->addFlash('error', 'L\'équipe a atteint son nombre maximum de membres.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        $membre = new MembreEquipe();
        $membre->setEquipe($equipe);
        
        $form = $this->createForm(MembreEquipeType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Incrémenter le compteur de membres de l'équipe
            $equipe->setNbMembresActuel($equipe->getNbMembresActuel() + 1);
            
            $em->persist($membre);
            $em->flush();
            
            $this->addFlash('success', 'Le membre a été ajouté à l\'équipe.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('membre_equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
            'button_label' => 'Ajouter le membre',
        ]);
    }

    #[Route('/{id_membre}/edit', name: 'app_membre_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(int $id_equipe, int $id_membre, Request $request, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membre = $membreRepo->find($id_membre);
        if (!$membre instanceof MembreEquipe || $membre->getEquipe()?->getIdEquipe() !== $equipe->getIdEquipe()) {
            throw $this->createNotFoundException('Membre introuvable dans cette équipe.');
        }

        $form = $this->createForm(MembreEquipeType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le membre a été modifié.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('membre_equipe/edit.html.twig', [
            'equipe' => $equipe,
            'membre' => $membre,
            'form' => $form,
            'button_label' => 'Enregistrer',
        ]);
    }

    #[Route('/{id_membre}/delete', name: 'app_membre_equipe_delete', methods: ['POST'])]
    public function delete(int $id_equipe, int $id_membre, Request $request, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membre = $membreRepo->find($id_membre);
        if (!$membre instanceof MembreEquipe || $membre->getEquipe()?->getIdEquipe() !== $equipe->getIdEquipe()) {
            throw $this->createNotFoundException('Membre introuvable dans cette équipe.');
        }

        if ($this->isCsrfTokenValid('delete_membre' . $membre->getIdMembre(), $request->request->getString('_token'))) {
            // Décrémenter le compteur de membres de l'équipe
            $equipe->setNbMembresActuel(max(0, $equipe->getNbMembresActuel() - 1));
            
            $em->remove($membre);
            $em->flush();
            $this->addFlash('success', 'Le membre a été retiré de l\'équipe.');
        }

        return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
    }


    #[Route('/send-whatsapp', name: 'app_membre_equipe_send_whatsapp', methods: ['POST'])]
    public function sendWhatsAppNotification(
        int $id_equipe, 
        Request $request, 
        EquipeRepository $equipeRepo, 
        MembreEquipeRepository $membreRepo,
        WhatsAppService $whatsAppService
    ): JsonResponse {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            return $this->json(['error' => 'Équipe introuvable'], 404);
        }

        $decoded = json_decode($request->getContent(), true);
        $data = is_array($decoded) ? $decoded : [];
        $message = isset($data['message']) && is_string($data['message']) ? trim($data['message']) : '';
        $sendToAll = !isset($data['sendToAll']) || (bool) $data['sendToAll'];
        $memberIds = isset($data['memberIds']) && is_array($data['memberIds']) ? $data['memberIds'] : [];

        if (empty($message)) {
            return $this->json(['error' => 'Le message ne peut pas être vide'], 400);
        }

        // Get members to notify
        if ($sendToAll) {
            $members = $membreRepo->findBy(['equipe' => $equipe, 'statutMembre' => 'Actif']);
        } else {
            $members = $membreRepo->findBy(['idMembre' => $memberIds]);
        }

        /** @var MembreEquipe[] $members */

        if (empty($members)) {
            return $this->json(['error' => 'Aucun membre à notifier'], 400);
        }

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($members as $member) {
            $user = $member->getUser();
            if (!$user || !$user->getTelephone()) {
                $results[] = [
                    'member' => $user ? $user->getNom() . ' ' . $user->getPrenom() : 'Membre #' . $member->getIdMembre(),
                    'status' => 'failed',
                    'error' => 'Numéro de téléphone manquant'
                ];
                $failCount++;
                continue;
            }

            // Format phone number
            $phoneNumber = $whatsAppService->formatPhoneNumber($user->getTelephone());
            
            // Format message with context
            $formattedMessage = sprintf(
                "🏢 Équipe: %s\n👤 Membre: %s %s\n\n%s\n\n📅 %s",
                $equipe->getNomEquipe(),
                $user->getPrenom(),
                $user->getNom(),
                $message,
                (new \DateTime())->format('d/m/Y H:i')
            );
            
            // Send WhatsApp message
            $result = $whatsAppService->sendWhatsAppMessage($phoneNumber, $formattedMessage);
            
            if ($result['success']) {
                $results[] = [
                    'member' => $user->getNom() . ' ' . $user->getPrenom(),
                    'phone' => $user->getTelephone(),
                    'status' => 'success'
                ];
                $successCount++;
            } else {
                $results[] = [
                    'member' => $user->getNom() . ' ' . $user->getPrenom(),
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Erreur inconnue'
                ];
                $failCount++;
            }
        }

        return $this->json([
            'success' => true,
            'total' => count($members),
            'sent' => $successCount,
            'failed' => $failCount,
            'results' => $results
        ]);
    }
}
