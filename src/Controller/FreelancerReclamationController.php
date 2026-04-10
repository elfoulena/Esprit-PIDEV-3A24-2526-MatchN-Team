<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/freelancer/reclamations')]
class FreelancerReclamationController extends AbstractController
{
    #[Route('/', name: 'freelancer_reclamation_index')]
    public function index(ReclamationRepository $repo, Request $request): Response
    {
        $userId = 1; // à remplacer par la session plus tard
        $statut = $request->query->get('statut');
        $search = $request->query->get('search');

        $reclamations = $repo->findByUserWithFilters($userId, $statut, $search);

        $stats = [
            'total'    => count($repo->findBy(['utilisateurId' => $userId])),
            'nouveau'  => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'nouveau'])),
            'en_cours' => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'en_cours'])),
            'ferme'    => count($repo->findBy(['utilisateurId' => $userId, 'statut' => 'fermé'])),
        ];

        return $this->render('reclamation/freelancer/index.html.twig', [
            'reclamations' => $reclamations,
            'stats'        => $stats,
            'statut'       => $statut,
            'search'       => $search,
        ]);
    }

    #[Route('/nouvelle', name: 'freelancer_reclamation_new', methods: ['POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $userId  = 1;
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