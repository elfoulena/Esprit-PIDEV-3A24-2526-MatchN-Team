<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\ReponseReclamation;
use App\Repository\ReclamationRepository;
use App\Service\PrioriteReclamationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reclamations')]
class AdminReclamationController extends AbstractController
{
    #[Route('/', name: 'admin_reclamation_index')]
    public function index(
        ReclamationRepository $repo,
        Request $request,
        PrioriteReclamationService $prioriteService
    ): Response {
        $statut = $request->query->get('statut');
        $search = $request->query->get('search');
        $tri    = $request->query->get('tri');

        $reclamations = $repo->findWithFilters($statut, $search);

        // Enrichir chaque réclamation avec sa priorité calculée
        $reclamationsAvecPriorite = array_map(function ($r) use ($prioriteService) {
            return [
                'reclamation' => $r,
                'priorite'    => $prioriteService->calculer($r->getMessage()),
            ];
        }, $reclamations);

        // Tri intelligent par urgence si demandé
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

        return $this->render('reclamation/admin/index.html.twig', [
            'reclamations' => $reclamationsAvecPriorite,
            'stats'        => $stats,
            'statut'       => $statut,
            'search'       => $search,
            'tri'          => $tri,
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
}
