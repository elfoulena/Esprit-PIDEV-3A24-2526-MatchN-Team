<?php

namespace App\Controller;

use App\Entity\AffectationProjet;
use App\Form\AffectationProjetType;
use App\Repository\AffectationProjetRepository;
use App\Service\CurrencyExchangeService;
use App\Service\MatchingService;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/affectation')]
class AffectationProjetController extends AbstractController
{
    #[Route('', name: 'app_affectation_index', methods: ['GET'])]
    public function index(Request $request, AffectationProjetRepository $repo, CurrencyExchangeService $currencyService): Response
    {
        $repo->updateExpiredAffectations();

        $search  = $request->query->get('search', '');
        $statut  = $request->query->get('statut', '');
        $sortBy  = $request->query->get('sortBy', 'date_debut');
        $sortDir = strtoupper($request->query->get('sortDir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $allowedSorts = ['date_debut', 'date_fin', 'statut', 'taux_horaire'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'date_debut';
        }

        $qb = $repo->createQueryBuilder('a')
            ->leftJoin('a.User', 'u')->addSelect('u')
            ->leftJoin('a.projet', 'p')->addSelect('p');

        if ($search) {
            $qb->andWhere('u.nom LIKE :s OR u.prenom LIKE :s OR p.titre LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('a.statut = :statut')->setParameter('statut', $statut);
        }

        $affectations = $qb->orderBy('a.' . $sortBy, $sortDir)->getQuery()->getResult();

        return $this->render('affectation/index.html.twig', [
            'affectations' => $affectations,
            'search'       => $search,
            'statut'       => $statut,
            'sortBy'       => $sortBy,
            'sortDir'      => $sortDir,
            'rates'        => $currencyService->getRates(),
        ]);
    }

    #[Route('/api/matching/{projetId}', name: 'app_affectation_matching', methods: ['GET'])]
    public function getMatchingRecommendations(
        int $projetId,
        ProjetRepository $projetRepo,
        MatchingService $matchingService
    ): JsonResponse {
        $projet = $projetRepo->find($projetId);
        if (!$projet) {
            return $this->json(['error' => 'Projet introuvable'], 404);
        }

        $recommendations = $matchingService->getRecommendations($projet);

        return $this->json([
            'projet' => $projet->getTitre(),
            'recommendations' => $recommendations,
        ]);
    }

    #[Route('/new', name: 'app_affectation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $affectation = new AffectationProjet();
        $form = $this->createForm(AffectationProjetType::class, $affectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->validateBusinessRules($affectation)) {
                return $this->render('affectation/new.html.twig', [
                    'form' => $form,
                    'button_label' => 'Créer l\'affectation',
                ]);
            }

            $em->persist($affectation);
            $em->flush();
            $this->addFlash('success', 'Affectation créée avec succès.');
            return $this->redirectToRoute('app_affectation_index');
        }

        return $this->render('affectation/new.html.twig', [
            'form' => $form,
            'button_label' => 'Créer l\'affectation',
        ]);
    }

    #[Route('/{id}', name: 'app_affectation_show', methods: ['GET'])]
    public function show(int $id, AffectationProjetRepository $repo, CurrencyExchangeService $currencyService): Response
    {
        $affectation = $repo->find($id);
        if (!$affectation) {
            throw $this->createNotFoundException('Affectation introuvable.');
        }

        return $this->render('affectation/show.html.twig', [
            'affectation' => $affectation,
            'rates'       => $currencyService->getRates(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affectation_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, AffectationProjetRepository $repo, EntityManagerInterface $em): Response
    {
        $affectation = $repo->find($id);
        if (!$affectation) {
            throw $this->createNotFoundException('Affectation introuvable.');
        }

        $form = $this->createForm(AffectationProjetType::class, $affectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->validateBusinessRules($affectation)) {
                return $this->render('affectation/edit.html.twig', [
                    'affectation' => $affectation,
                    'form' => $form,
                    'button_label' => 'Enregistrer les modifications',
                ]);
            }

            $em->flush();
            $this->addFlash('success', 'Affectation modifiée avec succès.');
            return $this->redirectToRoute('app_affectation_show', ['id' => $affectation->getId()]);
        }

        return $this->render('affectation/edit.html.twig', [
            'affectation' => $affectation,
            'form' => $form,
            'button_label' => 'Enregistrer les modifications',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_affectation_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, AffectationProjetRepository $repo, EntityManagerInterface $em): Response
    {
        $affectation = $repo->find($id);
        if (!$affectation) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            if ($affectation->getEvaluationPartTimes()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer cette affectation car elle possède des évaluations.');
                return $this->redirectToRoute('app_affectation_show', ['id' => $id]);
            }

            $em->remove($affectation);
            $em->flush();
            $this->addFlash('success', 'Affectation supprimée.');
        }

        return $this->redirectToRoute('app_affectation_index');
    }

    #[Route('/{id}/statut/{statut}', name: 'app_affectation_change_statut', methods: ['POST'])]
    public function changeStatut(int $id, string $statut, Request $request, AffectationProjetRepository $repo, EntityManagerInterface $em): Response
    {
        $affectation = $repo->find($id);
        if (!$affectation) {
            throw $this->createNotFoundException();
        }

        $allowed = ['EN_ATTENTE', 'ACCEPTEE', 'REFUSEE', 'TERMINEE'];
        if (!in_array($statut, $allowed)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_affectation_show', ['id' => $id]);
        }

        if ($this->isCsrfTokenValid('statut' . $id, $request->request->get('_token'))) {
            $affectation->setStatut($statut);
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour.');
        }

        return $this->redirectToRoute('app_affectation_show', ['id' => $id]);
    }

    private function validateBusinessRules(AffectationProjet $affectation): bool
    {
        $valid = true;

        if ($affectation->getDateFin() && $affectation->getDateDebut()) {
            if ($affectation->getDateFin() <= $affectation->getDateDebut()) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                $valid = false;
            }
        }

        $taux = $affectation->getTauxHoraire();
        if ($taux !== null && $taux > 0 && $taux < 5) {
            $this->addFlash('error', 'Le taux horaire doit être d\'au moins 5 DT/h.');
            $valid = false;
        }

        return $valid;
    }
}
