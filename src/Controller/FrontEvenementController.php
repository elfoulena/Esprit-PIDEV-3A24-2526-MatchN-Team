<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\ParticipationEvenement;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\TicketPdfService;

#[Route('/front/evenement')]
class FrontEvenementController extends AbstractController
{
    #[Route('/', name: 'app_front_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour accéder aux événements.');
            return $this->redirectToRoute('app_login');
        }

        $q = $request->query->get('q', '');
        $type = $request->query->get('type', 'ALL');
        $sort = $request->query->get('sort', 'date_asc');

        $evenements = $evenementRepository->findByFilters($q, $type, $sort);

        $participatingIds = [];
        $qb = $entityManager->createQueryBuilder();
        $participatingIdsRaw = $qb->select('e.id_evenement, p.jeton')
            ->from(ParticipationEvenement::class, 'p')
            ->join('p.evenement', 'e')
            ->where('p.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        $participatingIds = array_column($participatingIdsRaw, 'id_evenement');
        $userJetons = array_column($participatingIdsRaw, 'jeton', 'id_evenement');

        return $this->render('front/evenement/index.html.twig', [
            'evenements' => $evenements,
            'q' => $q,
            'type' => $type,
            'sort' => $sort,
            'participating_ids' => $participatingIds,
            'user_jetons' => $userJetons,
        ]);
    }

    #[Route('/search', name: 'app_front_evenement_search', methods: ['GET'])]
    public function search(EvenementRepository $evenementRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $q = $request->query->get('q', '');
        $type = $request->query->get('type', 'ALL');
        $sort = $request->query->get('sort', 'date_asc');

        $evenements = $evenementRepository->findByFilters($q, $type, $sort);

        $qb = $entityManager->createQueryBuilder();
        $participatingIdsRaw = $qb->select('e.id_evenement, p.jeton')
            ->from(ParticipationEvenement::class, 'p')
            ->join('p.evenement', 'e')
            ->where('p.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        $participatingIds = array_column($participatingIdsRaw, 'id_evenement');
        $userJetons = array_column($participatingIdsRaw, 'jeton', 'id_evenement');

        return $this->render('front/evenement/_list.html.twig', [
            'evenements' => $evenements,
            'participating_ids' => $participatingIds,
            'user_jetons' => $userJetons,
        ]);
    }

    #[Route('/{id_evenement}/participer', name: 'app_front_evenement_participer', methods: ['POST'])]
    public function participer(
        int $id_evenement,
        EvenementRepository $evenementRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_login');
        }

        $evenement = $evenementRepository->find($id_evenement);

        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_front_evenement_index');
        }

        // Vérifier si déjà inscrit
        $repoParticipation = $entityManager->getRepository(ParticipationEvenement::class);
        $existing = $repoParticipation->findOneBy([
            'evenement' => $evenement,
            'utilisateur' => $user
        ]);

        if ($existing) {
            $this->addFlash('info', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('app_front_evenement_index');
        }

        // Vérifier capacité
        $currentParticipants = $evenement->getNombre_actuel() ?? 0;
        $maxCapacity = $evenement->getCapacite_max() ?? 9999;

        if ($currentParticipants >= $maxCapacity) {
            $this->addFlash('error', 'L\'événement est complet.');
            return $this->redirectToRoute('app_front_evenement_index');
        }

        // Créer participation
        $participation = new ParticipationEvenement();
        $participation->setEvenement($evenement);
        $participation->setUtilisateur($user);
        $participation->setDateInscription(new \DateTime());
        $participation->setJeton(bin2hex(random_bytes(16)));

        $evenement->setNombre_actuel($currentParticipants + 1);

        try {
            $entityManager->persist($participation);
            $entityManager->flush();
            $this->addFlash('success', 'Votre participation a été enregistrée !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_front_evenement_index');
    }

    #[Route('/{id_evenement}/desister', name: 'employe_evenement_desister', methods: ['POST'])]
    public function desister(
        int $id_evenement,
        EvenementRepository $evenementRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $evenement = $evenementRepository->find($id_evenement);

        if (!$evenement) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('app_front_evenement_index');
        }

        $repoParticipation = $entityManager->getRepository(ParticipationEvenement::class);
        $participation = $repoParticipation->findOneBy([
            'evenement' => $evenement,
            'utilisateur' => $user
        ]);

        if ($participation) {
            $entityManager->remove($participation);

            // Décrémenter le compteur
            if ($evenement->getNombre_actuel() > 0) {
                $evenement->setNombre_actuel($evenement->getNombre_actuel() - 1);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre participation a été annulée.');
        }

        return $this->redirectToRoute('app_front_evenement_index');
    }

    #[Route('/ticket/{jeton}', name: 'employe_evenement_ticket', methods: ['GET'])]
    public function ticket(
        string $jeton,
        EntityManagerInterface $entityManager,
        TicketPdfService $pdfService
    ): Response {
        $user = $this->getUser();

        $repoParticipation = $entityManager->getRepository(ParticipationEvenement::class);
        $participation = $repoParticipation->findOneBy([
            'jeton' => $jeton,
            'utilisateur' => $user
        ]);

        if (!$participation) {
            $this->addFlash('error', 'Billet introuvable ou vous n\'avez pas accès.');
            return $this->redirectToRoute('employe_evenement_index');
        }

        $pdfContent = $pdfService->generateTicketPdf($participation);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ticket_' . $jeton . '.pdf"'
        ]);
    }
}
