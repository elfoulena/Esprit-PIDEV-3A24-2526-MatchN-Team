<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\ReponseReclamation;
use App\Entity\MessageDiscussion;
use App\Repository\ReclamationRepository;
use App\Repository\DiscussionRepository;
use App\Service\PrioriteReclamationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GroqService;


#[Route('/admin/reclamations')]
class AdminReclamationController extends AbstractController
{
    #[Route('/', name: 'admin_reclamation_index')]
    public function index(
        ReclamationRepository $repo,
        DiscussionRepository $discussionRepo,
        Request $request,
        PrioriteReclamationService $prioriteService
    ): Response {
        $statut = $request->query->get('statut');
        $search = $request->query->get('search');
        $tri    = $request->query->get('tri');

        $reclamations = $repo->findWithFilters($statut, $search);

        $reclamationsAvecPriorite = array_map(function ($r) use ($prioriteService) {
            return [
                'reclamation' => $r,
                'priorite'    => $prioriteService->calculer($r->getMessage()),
            ];
        }, $reclamations);

        if ($tri === 'priorite') {
            usort($reclamationsAvecPriorite, fn($a, $b)
                => $b['priorite']['score'] <=> $a['priorite']['score']
            );
        }

        $stats = [
            'total'    => $repo->count([]),
            'nouveau'  => $repo->count(['statut' => 'nouveau']),
            'en_cours' => $repo->count(['statut' => 'en_cours']),
            'ferme'    => $repo->count(['statut' => 'fermé']),
        ];

        // ── Chat ──
        $chatDiscussions = $discussionRepo->findAll();
        $chatMessagesData = [];
        foreach ($chatDiscussions as $d) {
            $msgs = $d->getMessageDiscussions()->toArray();
            usort($msgs, fn($a, $b) => $a->getDateEnvoi() <=> $b->getDateEnvoi());
            $chatMessagesData[$d->getIdDiscussion()] = array_map(fn($m) => [
                'role'    => $m->getRoleExpediteur(),
                'contenu' => $m->getContenu(),
                'date'    => $m->getDateEnvoi()->format('d/m H:i'),
            ], $msgs);
        }

        return $this->render('reclamation/admin/index.html.twig', [
            'reclamations'     => $reclamationsAvecPriorite,
            'stats'            => $stats,
            'statut'           => $statut,
            'search'           => $search,
            'tri'              => $tri,
            'chatDiscussions'  => $chatDiscussions,
            'chatMessagesData' => $chatMessagesData,
        ]);
    }

    #[Route('/{id}/repondre', name: 'admin_reclamation_repondre', methods: ['POST'])]
    public function repondre(
        Reclamation $reclamation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $contenu       = trim((string) $request->request->get('contenu', ''));
        $nouveauStatut = $request->request->get('statut');

        if ($contenu === '') {
            $this->addFlash('error', 'La réponse ne peut pas être vide.');
            return $this->redirectToRoute('admin_reclamation_index');
        }

        $reponse = new ReponseReclamation();
        $reponse->setContenu($contenu);
        $reponse->setReclamation($reclamation);

        $admin = $this->getUser();
        $reponse->setUtilisateurId(
            ($admin && method_exists($admin, 'getId')) ? $admin->getId() : 1
        );

        $em->persist($reponse);

        if ($nouveauStatut) {
            $reclamation->setStatut($nouveauStatut);
        }

        $em->flush();

        $this->addFlash('success', 'Réponse envoyée avec succès !');
        return $this->redirectToRoute('admin_reclamation_index');
    }

    #[Route('/chat/{id}/repondre', name: 'admin_chat_send', methods: ['POST'])]
    public function chatRepondre(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        DiscussionRepository $discussionRepo
    ): Response {
        $discussion = $discussionRepo->findOneBy(['id_discussion' => $id]);
        if (!$discussion) {
            throw $this->createNotFoundException();
        }

        $contenu = trim($request->request->get('contenu', ''));
        if (!empty($contenu)) {
            $message = new MessageDiscussion();
            $message->setDiscussion($discussion);
            $message->setIdExpediteur($this->getUser()->getId());
            $message->setRoleExpediteur('admin');
            $message->setContenu($contenu);
            $message->setDateEnvoi(new \DateTime());

            $em->persist($message);
            $em->flush();
        }

        return $this->redirectToRoute('admin_reclamation_index');
    }

    #[Route('/{id}/supprimer', name: 'admin_reclamation_supprimer', methods: ['POST'])]
    public function supprimer(
        Reclamation $reclamation,
        EntityManagerInterface $em
    ): Response {
        $em->remove($reclamation);
        $em->flush();

        $this->addFlash('success', 'Réclamation supprimée.');
        return $this->redirectToRoute('admin_reclamation_index');
    }
// Ajouter avant la dernière } du fichier

#[Route('/ai/ameliorer', name: 'admin_reclamation_ai_ameliorer', methods: ['POST'])]
public function ameliorer(Request $request, GroqService $groq): Response
{
    $texte    = trim($request->request->get('texte', ''));
    $contexte = trim($request->request->get('contexte', ''));

    if (empty($texte)) {
        return $this->json(['error' => 'Texte vide'], 400);
    }

    try {
        $resultat = $groq->ameliorer($texte, $contexte);
        return $this->json(['result' => $resultat]);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 500);
    }
}

#[Route('/ai/traduire', name: 'admin_reclamation_ai_traduire', methods: ['POST'])]
public function traduire(Request $request, GroqService $groq): Response
{
    $texte  = trim($request->request->get('texte', ''));
    $langue = $request->request->get('langue', 'en');

    if (empty($texte)) {
        return $this->json(['error' => 'Texte vide'], 400);
    }

    try {
        $resultat = $groq->traduire($texte, $langue);
        return $this->json(['result' => $resultat]);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 500);
    }
}    
}