<?php

namespace App\Controller;

use App\Entity\MembreEquipe;
use App\Form\MembreEquipeType;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/equipe/{id_equipe}/membre')]
class MembreEquipeController extends AbstractController
{
    #[Route('', name: 'app_membre_equipe_index', methods: ['GET'])]
    public function index(int $id_equipe, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membres = $membreRepo->findBy(['equipe' => $equipe]);

        return $this->render('membre_equipe/index.html.twig', [
            'equipe' => $equipe,
            'membres' => $membres,
        ]);
    }

    #[Route('/new', name: 'app_membre_equipe_new', methods: ['GET', 'POST'])]
    public function new(int $id_equipe, Request $request, EquipeRepository $equipeRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        // Vérifier si l'équipe n'a pas atteint son nombre maximum de membres
        if ($equipe->getNbMembresActuel() >= $equipe->getNbMembresMax()) {
            $this->addFlash('error', 'L\'équipe a atteint son nombre maximum de membres.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        $membre = new MembreEquipe();
        $membre->setEquipe($equipe);
        
        $form = $this->createForm(MembreEquipeType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Incrémenter le compteur de membres de l'équipe
            $equipe->setNbMembresActuel($equipe->getNbMembresActuel() + 1);
            
            $em->persist($membre);
            $em->flush();
            
            $this->addFlash('success', 'Le membre a été ajouté à l\'équipe.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('membre_equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
            'button_label' => 'Ajouter le membre',
        ]);
    }

    #[Route('/{id_membre}/edit', name: 'app_membre_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(int $id_equipe, int $id_membre, Request $request, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membre = $membreRepo->find($id_membre);
        if (!$membre || $membre->getEquipe()->getIdEquipe() !== $equipe->getIdEquipe()) {
            throw $this->createNotFoundException('Membre introuvable dans cette équipe.');
        }

        $form = $this->createForm(MembreEquipeType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le membre a été modifié.');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
        }

        return $this->render('membre_equipe/edit.html.twig', [
            'equipe' => $equipe,
            'membre' => $membre,
            'form' => $form,
            'button_label' => 'Enregistrer',
        ]);
    }

    #[Route('/{id_membre}/delete', name: 'app_membre_equipe_delete', methods: ['POST'])]
    public function delete(int $id_equipe, int $id_membre, Request $request, EquipeRepository $equipeRepo, MembreEquipeRepository $membreRepo, EntityManagerInterface $em): Response
    {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable.');
        }

        $membre = $membreRepo->find($id_membre);
        if (!$membre || $membre->getEquipe()->getIdEquipe() !== $equipe->getIdEquipe()) {
            throw $this->createNotFoundException('Membre introuvable dans cette équipe.');
        }

        if ($this->isCsrfTokenValid('delete_membre' . $membre->getIdMembre(), $request->request->get('_token'))) {
            // Décrémenter le compteur de membres de l'équipe
            $equipe->setNbMembresActuel(max(0, $equipe->getNbMembresActuel() - 1));
            
            $em->remove($membre);
            $em->flush();
            $this->addFlash('success', 'Le membre a été retiré de l\'équipe.');
        }

        return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $equipe->getIdEquipe()]);
    }
}