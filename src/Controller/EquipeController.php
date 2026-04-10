<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/equipe')]
class EquipeController extends AbstractController
{
    #[Route('', name: 'app_equipe_index', methods: ['GET'])]
    public function index(Request $request, EquipeRepository $repo): Response
    {
        $search      = $request->query->get('search', '');
        $statut      = $request->query->get('statut', '');
        $departement = $request->query->get('departement', '');
        $sortBy      = $request->query->get('sortBy', 'dateCreation');
        $sortDir     = $request->query->get('sortDir', 'DESC');

        $allowedSorts = ['dateCreation', 'nomEquipe', 'nbMembresActuel', 'budget', 'statut'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'dateCreation';
        }
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $repo->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.nomEquipe LIKE :s OR e.description LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $statut);
        }
        if ($departement) {
            $qb->andWhere('e.departement = :dep')->setParameter('dep', $departement);
        }

        $equipes = $qb->orderBy('e.' . $sortBy, $sortDir)->getQuery()->getResult();

        $departements = $repo->createQueryBuilder('e')
            ->select('DISTINCT e.departement')
            ->where('e.departement IS NOT NULL')
            ->getQuery()
            ->getResult();
        $departements = array_column($departements, 'departement');

        return $this->render('equipe/index.html.twig', [
            'equipes'      => $equipes,
            'departements' => $departements,
            'search'       => $search,
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
            $this->addFlash('success', "L'équipe « {$equipe->getNomEquipe()} » a été créée.");
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'form'         => $form,
            'button_label' => 'Créer l\'équipe',
        ]);
    }

    #[Route('/{id_equipe}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(int $id_equipe, EquipeRepository $repo): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException("Équipe introuvable.");
        }

        return $this->render('equipe/show.html.twig', ['equipe' => $equipe]);
    }

    #[Route('/{id_equipe}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(int $id_equipe, Request $request, EquipeRepository $repo, EntityManagerInterface $em): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException("Équipe introuvable.");
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', "L'équipe « {$equipe->getNomEquipe()} » a été modifiée.");
            return $this->redirectToRoute('app_equipe_show', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe'       => $equipe,
            'form'         => $form,
            'button_label' => 'Enregistrer les modifications',
        ]);
    }

    #[Route('/{id_equipe}/delete', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(int $id_equipe, Request $request, EquipeRepository $repo, EntityManagerInterface $em): Response
    {
        $equipe = $repo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete' . $id_equipe, $request->request->get('_token'))) {
            $em->remove($equipe);
            $em->flush();
            $this->addFlash('success', "L'équipe a été supprimée.");
        }

        return $this->redirectToRoute('app_equipe_index');
    }
}
