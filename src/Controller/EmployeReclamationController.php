<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employe/reclamations')]
class EmployeReclamationController extends AbstractController
{
    
#[Route('/', name: 'employe_reclamation_index')]
public function index(
    ReclamationRepository $repo,
    ReponseReclamationRepository $reponseRepo,
    Request $request
): Response {
    /** @var \App\Entity\User|null $user */
    $user   = $this->getUser();
    $userId = $user ? $user->getId() : 1; // ✅

    $search = $request->query->get('search');
    $filtre = $request->query->get('reponse'); // 'avec', 'sans', ou ''

    $reclamations = $repo->findByUserWithFilters($userId ?? 1, null, $search);

    // Récupérer les réponses pour chaque réclamation
    $reponses = [];
    foreach ($reclamations as $r) {
        $reponse = $reponseRepo->findOneBy(
            ['reclamation' => $r],
            ['id' => 'DESC']
        );
        $reponses[$r->getId()] = $reponse;
    }

    // Filtrer par réponse APRÈS avoir construit $reponses
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

    return $this->render('reclamation/employe/index.html.twig', [
        'reclamations' => $reclamations,
        'reponses'     => $reponses,
        'stats'        => $stats,
        'search'       => $search,
        'filtre'       => $filtre, // ✅ ajouté
    ]);
}

    #[Route('/nouvelle', name: 'employe_reclamation_new', methods: ['POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
{
    /** @var \App\Entity\User $user */
    $user   = $this->getUser();
    $userId = $user->getId(); // ✅

    $message = trim((string) $request->request->get('message', ''));

    if (empty($message)) {
        $this->addFlash('error', 'Le message ne peut pas être vide.');
        return $this->redirectToRoute('employe_reclamation_index');
    }

    // Filtre mot interdit
    if (str_contains(strtolower($message), 'khayeb')) {
        $this->addFlash('error', '❌ Votre message contient un mot interdit.');
        return $this->redirectToRoute('employe_reclamation_index');
    }

        $reclamation = new Reclamation();
        $reclamation->setMessage($message);
        $reclamation->setStatut('nouveau');
        $reclamation->setType('employé');
        $reclamation->setUtilisateurId($userId);

        $em->persist($reclamation);
        $em->flush();

        $this->addFlash('success', '✅ Réclamation soumise avec succès !');
        return $this->redirectToRoute('employe_reclamation_index');
    }

    #[Route('/{id}/modifier', name: 'employe_reclamation_edit', methods: ['POST'])]
    public function modifier(Reclamation $reclamation, Request $request, EntityManagerInterface $em): Response
    {
        if ($reclamation->getStatut() !== 'nouveau') {
            $this->addFlash('error', '❌ Seules les réclamations "nouveau" peuvent être modifiées.');
            return $this->redirectToRoute('employe_reclamation_index');
        }

        $message = trim((string) $request->request->get('message', ''));
        if (!empty($message)) {
            $reclamation->setMessage($message);
            $em->flush();
            $this->addFlash('success', '✅ Réclamation modifiée.');
        }

        return $this->redirectToRoute('employe_reclamation_index');
    }

    #[Route('/{id}/supprimer', name: 'employe_reclamation_delete', methods: ['POST'])]
    public function supprimer(Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        if ($reclamation->getStatut() !== 'nouveau') {
            $this->addFlash('error', '❌ Seules les réclamations "nouveau" peuvent être supprimées.');
            return $this->redirectToRoute('employe_reclamation_index');
        }

        $em->remove($reclamation);
        $em->flush();
        $this->addFlash('success', '🗑 Réclamation supprimée.');

        return $this->redirectToRoute('employe_reclamation_index');
    }
}