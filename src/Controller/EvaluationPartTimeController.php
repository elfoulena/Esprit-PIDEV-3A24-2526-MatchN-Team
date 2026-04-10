<?php

namespace App\Controller;

use App\Entity\EvaluationPartTime;
use App\Form\EvaluationPartTimeType;
use App\Repository\EvaluationPartTimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/evaluation')]
class EvaluationPartTimeController extends AbstractController
{
    #[Route('', name: 'app_evaluation_index', methods: ['GET'])]
    public function index(EvaluationPartTimeRepository $repo): Response
    {
        $evaluations = $repo->findAllWithDetails();

        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $evaluations,
        ]);
    }

    #[Route('/new', name: 'app_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, EvaluationPartTimeRepository $repo): Response
    {
        $evaluation = new EvaluationPartTime();
        $form = $this->createForm(EvaluationPartTimeType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $affectation = $evaluation->getAffectationProjet();

            if (!$affectation || !$affectation->peutEtreEvaluee()) {
                $this->addFlash('error', 'Cette affectation ne peut pas être évaluée (statut non valide).');
                return $this->render('evaluation/new.html.twig', [
                    'form' => $form,
                    'button_label' => 'Créer l\'évaluation',
                ]);
            }

            if ($repo->existePourAffectation($affectation->getId())) {
                $this->addFlash('error', 'Une évaluation existe déjà pour cette affectation.');
                return $this->render('evaluation/new.html.twig', [
                    'form' => $form,
                    'button_label' => 'Créer l\'évaluation',
                ]);
            }

            $em->persist($evaluation);
            $em->flush();
            $this->addFlash('success', 'Évaluation créée avec succès.');
            return $this->redirectToRoute('app_evaluation_index');
        }

        return $this->render('evaluation/new.html.twig', [
            'form' => $form,
            'button_label' => 'Créer l\'évaluation',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EvaluationPartTimeRepository $repo, EntityManagerInterface $em): Response
    {
        $evaluation = $repo->find($id);
        if (!$evaluation) {
            throw $this->createNotFoundException('Évaluation introuvable.');
        }

        $form = $this->createForm(EvaluationPartTimeType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Évaluation modifiée avec succès.');
            return $this->redirectToRoute('app_evaluation_index');
        }

        return $this->render('evaluation/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
            'button_label' => 'Enregistrer les modifications',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_evaluation_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EvaluationPartTimeRepository $repo, EntityManagerInterface $em): Response
    {
        $evaluation = $repo->find($id);
        if (!$evaluation) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $em->remove($evaluation);
            $em->flush();
            $this->addFlash('success', 'Évaluation supprimée.');
        }

        return $this->redirectToRoute('app_evaluation_index');
    }
}
