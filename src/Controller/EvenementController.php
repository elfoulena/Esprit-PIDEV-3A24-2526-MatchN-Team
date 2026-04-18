<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\GroqService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository, Request $request): Response
    {
        $q = $request->query->get('q');
        $archived = $request->query->getBoolean('archived', false);
        
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenementRepository->findBySearch($q, $archived),
            'q' => $q,
            'archived' => $archived,
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/ai/improve-description', name: 'app_evenement_ai_improve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function aiImproveDescription(Request $request, GroqService $groqService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';

        if (empty($text)) {
            return new JsonResponse(['error' => 'Texte vide'], 400);
        }

        $improvedText = $groqService->ameliorer($text, 'Événement d\'entreprise MatchNTeam');

        return new JsonResponse(['improved_text' => $improvedText]);
    }

    #[Route('/statistiques-ai', name: 'app_evenement_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function stats(EvenementRepository $evenementRepository, GroqService $groqService): Response
    {
        $donneesBrutes = $evenementRepository->getStatisticsForAI();
        $analyseAI     = $groqService->analyserStatistiquesEvenements($donneesBrutes);

        return $this->render('evenement/stats.html.twig', [
            'stats'     => $donneesBrutes,
            'analyse'   => $analyseAI,
        ]);
    }

    #[Route('/{id_evenement}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id_evenement}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id_evenement}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId_evenement(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

}
