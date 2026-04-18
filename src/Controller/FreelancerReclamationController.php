<?php
namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\MessageDiscussion;
use App\Entity\Reclamation;
use App\Repository\DiscussionRepository;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/freelancer/reclamations')]
class FreelancerReclamationController extends AbstractController
{
    #[Route('/', name: 'freelancer_reclamation_index')]
    public function index(
        ReclamationRepository $repo,
        ReponseReclamationRepository $reponseRepo,
        DiscussionRepository $discussionRepo,
        Request $request
    ): Response {
        $userId = $this->getUser() ? $this->getUser()->getId() : 1;
        $search = $request->query->get('search');
        $filtre = $request->query->get('reponse');

        $reclamations = $repo->findByUserWithFilters($userId, null, $search);

        $reponses = [];
        foreach ($reclamations as $r) {
            $reponse = $reponseRepo->findOneBy(
                ['reclamation' => $r],
                ['id' => 'DESC']
            );
            $reponses[$r->getId()] = $reponse;
        }

        if ($filtre === 'avec') {
            $reclamations = array_filter($reclamations, fn($r) => !empty($reponses[$r->getId()]));
        } elseif ($filtre === 'sans') {
            $reclamations = array_filter($reclamations, fn($r) => empty($reponses[$r->getId()]));
        }

        $stats = [
            'total'    => count($repo->findBy(['utilisateurId' => $userId])),
            'nouveau'  => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'nouveau'])),
            'en_cours' => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'en_cours'])),
            'ferme'    => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'fermé'])),
        ];

        // Chat
        $discussion = $discussionRepo->findOneBy(['id_freelancer' => $userId]);
        $chatMessages = [];
        if ($discussion) {
            $chatMessages = $discussion->getMessageDiscussions()->toArray();
            usort($chatMessages, fn($a, $b) => $a->getDateEnvoi() <=> $b->getDateEnvoi());
        }

        return $this->render('reclamation/freelancer/index.html.twig', [
            'reclamations' => $reclamations,
            'reponses'     => $reponses,
            'stats'        => $stats,
            'search'       => $search,
            'filtre'       => $filtre,
            'chatMessages' => $chatMessages,
        ]);
    }

    #[Route('/nouvelle', name: 'freelancer_reclamation_new', methods: ['POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $userId  = $this->getUser()->getId();
        $message = trim($request->request->get('message', ''));

        if (empty($message)) {
            $this->addFlash('error', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('freelancer_reclamation_index');
        }

        if (str_contains(strtolower($message), 'khayeb')) {
            $this->addFlash('error', '❌ Votre message contient un mot interdit.');
            return $this->redirectToRoute('freelancer_reclamation_index');
        }

        $reclamation = new Reclamation();
        $reclamation->setMessage($message);
        $reclamation->setStatut('nouveau');
        $reclamation->setType('freelancer');
        $reclamation->setUtilisateurId($userId);

        $em->persist($reclamation);
        $em->flush();

        $this->addFlash('success', '✅ Réclamation soumise avec succès !');
        return $this->redirectToRoute('freelancer_reclamation_index');
    }

    #[Route('/chat/envoyer', name: 'freelancer_chat_send', methods: ['POST'])]
    public function chatEnvoyer(
        Request $request,
        EntityManagerInterface $em,
        DiscussionRepository $discussionRepo
    ): Response {
        $userId  = $this->getUser()->getId();
        $contenu = trim($request->request->get('contenu', ''));

        if (empty($contenu)) {
            $this->addFlash('error', 'Message vide.');
            return $this->redirectToRoute('freelancer_reclamation_index');
        }

        $discussion = $discussionRepo->findOneBy(['id_freelancer' => $userId]);
        if (!$discussion) {
            $discussion = new Discussion();
            $discussion->setIdFreelancer($userId);
            $discussion->setTitre('Discussion avec admin');
            $discussion->setDateCreation(new \DateTime());
            $em->persist($discussion);
            $em->flush();
        }

        $message = new MessageDiscussion();
        $message->setDiscussion($discussion);
        $message->setIdExpediteur($userId);
        $message->setRoleExpediteur('freelancer');
        $message->setContenu($contenu);
        $message->setDateEnvoi(new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('freelancer_reclamation_index');
    }

    #[Route('/{id}/modifier', name: 'freelancer_reclamation_edit', methods: ['POST'])]
    public function modifier(Reclamation $reclamation, Request $request, EntityManagerInterface $em): Response
    {
        if ($reclamation->getStatut() !== 'nouveau') {
            $this->addFlash('error', '❌ Seules les réclamations "nouveau" peuvent être modifiées.');
            return $this->redirectToRoute('freelancer_reclamation_index');
        }

        $message = trim($request->request->get('message', ''));
        if (!empty($message)) {
            $reclamation->setMessage($message);
            $em->flush();
            $this->addFlash('success', '✅ Réclamation modifiée.');
        }

        return $this->redirectToRoute('freelancer_reclamation_index');
    }

    #[Route('/{id}/supprimer', name: 'freelancer_reclamation_delete', methods: ['POST'])]
    public function supprimer(Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        if ($reclamation->getStatut() !== 'nouveau') {
            $this->addFlash('error', '❌ Seules les réclamations "nouveau" peuvent être supprimées.');
            return $this->redirectToRoute('freelancer_reclamation_index');
        }

        $em->remove($reclamation);
        $em->flush();
        $this->addFlash('success', '🗑 Réclamation supprimée.');

        return $this->redirectToRoute('freelancer_reclamation_index');
    }
}