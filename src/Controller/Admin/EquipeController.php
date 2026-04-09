<?php

namespace App\Controller\Admin;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/equipe')]
class EquipeController extends AbstractController
{
    #[Route('/', name: 'app_equipe_index', methods: ['GET'])]
    public function index(Request $request, EquipeRepository $repo): Response
    {
        $q           = $request->query->get('q', '');
        $statut      = $request->query->get('statut', '');
        $departement = $request->query->get('departement', '');
        $sortBy      = $request->query->get('sortBy', 'dateCreation');
        $sortDir     = $request->query->get('sortDir', 'DESC');

        $equipes      = $repo->findWithFilters($q, $statut, $departement, $sortBy, $sortDir);
        $stats        = $repo->getStats();
        $departements = $repo->findDistinctDepartements();

        return $this->render('equipe/index.html.twig', [
            'equipes'      => $equipes,
            'stats'        => $stats,
            'departements' => $departements,
            'q'            => $q,
            'statut'       => $statut,
            'departement'  => $departement,
            'sortBy'       => $sortBy,
            'sortDir'      => $sortDir,
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $equipe = new Equipe();
        $form   = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($equipe);
            $em->flush();
            $this->addFlash('success', 'Équipe créée avec succès !');
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form'   => $form,
        ]);
    }

    #[Route('/{id_equipe}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(int $id_equipe, EquipeRepository $repo): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        return $this->render('equipe/show.html.twig', ['equipe' => $equipe]);
    }

    #[Route('/{id_equipe}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(int $id_equipe, Request $request, EquipeRepository $repo, EntityManagerInterface $em): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Équipe mise à jour avec succès !');
            return $this->redirectToRoute('app_equipe_show', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form'   => $form,
        ]);
    }

    #[Route('/{id_equipe}/delete', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(int $id_equipe, Request $request, EquipeRepository $repo, EntityManagerInterface $em): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->request->get('_token'))) {
            $em->remove($equipe);
            $em->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès.');
        }

        return $this->redirectToRoute('app_equipe_index');
    }
}
