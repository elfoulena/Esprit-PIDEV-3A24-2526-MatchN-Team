<?php

namespace App\Controller\Admin;

use App\Entity\MembreEquipe;
use App\Form\MembreEquipeType;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/equipe/{id_equipe}/membres')]
class MembreEquipeController extends AbstractController
{
    #[Route('/', name: 'app_membre_equipe_index', methods: ['GET'])]
    public function index(
        int $id_equipe,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) throw $this->createNotFoundException('Équipe introuvable.');

        $q      = $request->query->get('q', '');
        $role   = $request->query->get('role', '');
        $statut = $request->query->get('statut', '');
        $sortBy = $request->query->get('sortBy', 'dateAffectation');
        $sortDir= $request->query->get('sortDir', 'DESC');

        $membres = $membreRepo->findByEquipeWithFilters($id_equipe, $q, $role, $statut, $sortBy, $sortDir);
        
        // Fetch user data for each member
        $userRepo = $em->getRepository(\App\Entity\User::class);
        $userData = [];
        foreach ($membres as $membre) {
            $user = $userRepo->find($membre->getIdUser());
            if ($user) {
                $userData[$membre->getIdUser()] = $user;
            }
        }

        return $this->render('membre_equipe/index.html.twig', [
            'equipe'   => $equipe,
            'membres'  => $membres,
            'userData' => $userData,
            'q'        => $q,
            'role'     => $role,
            'statut'   => $statut,
            'sortBy'   => $sortBy,
            'sortDir'  => $sortDir,
        ]);
    }

    #[Route('/new', name: 'app_membre_equipe_new', methods: ['GET', 'POST'])]
    public function new(
        int $id_equipe,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) throw $this->createNotFoundException('Équipe introuvable.');

        $membre = new MembreEquipe();
        $membre->setEquipe($equipe);

        // Remove the exclude_users option - don't pass any extra options
        $form = $this->createForm(MembreEquipeType::class, $membre);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if user is already in the team
            if ($membreRepo->isUserInEquipe($membre->getIdUser(), $id_equipe)) {
                $this->addFlash('danger', 'Cet User est déjà membre de cette équipe.');
                return $this->redirectToRoute('app_membre_equipe_new', ['id_equipe' => $id_equipe]);
            }

            // Check team capacity
            if ($equipe->getNbMembresActuel() >= $equipe->getNbMembresMax()) {
                $this->addFlash('danger', 'L\'équipe a atteint sa capacité maximale de membres.');
                return $this->redirectToRoute('app_membre_equipe_new', ['id_equipe' => $id_equipe]);
            }

            $em->persist($membre);
            // Update member count
            $equipe->setNbMembresActuel($equipe->getNbMembresActuel() + 1);
            $em->flush();

            $this->addFlash('success', 'Membre ajouté avec succès !');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $id_equipe]);
        }

        return $this->render('membre_equipe/new.html.twig', [
            'equipe' => $equipe,
            'membre' => $membre,
            'form'   => $form,
        ]);
    }

    #[Route('/{id_membre}/edit', name: 'app_membre_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id_equipe,
        int $id_membre,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        if (!$equipe) throw $this->createNotFoundException('Équipe introuvable.');

        $membre = $membreRepo->find($id_membre);
        if (!$membre) throw $this->createNotFoundException('Membre introuvable.');

        $form = $this->createForm(MembreEquipeType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Membre mis à jour avec succès !');
            return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $id_equipe]);
        }

        return $this->render('membre_equipe/edit.html.twig', [
            'equipe' => $equipe,
            'membre' => $membre,
            'form'   => $form,
        ]);
    }

    #[Route('/{id_membre}/delete', name: 'app_membre_equipe_delete', methods: ['POST'])]
    public function delete(
        int $id_equipe,
        int $id_membre,
        Request $request,
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EntityManagerInterface $em
    ): Response {
        $equipe = $equipeRepo->find($id_equipe);
        $membre = $membreRepo->find($id_membre);

        if ($membre && $this->isCsrfTokenValid('delete' . $membre->getIdMembre(), $request->request->get('_token'))) {
            $em->remove($membre);
            if ($equipe && $equipe->getNbMembresActuel() > 0) {
                $equipe->setNbMembresActuel($equipe->getNbMembresActuel() - 1);
            }
            $em->flush();
            $this->addFlash('success', 'Membre retiré de l\'équipe.');
        }

        return $this->redirectToRoute('app_membre_equipe_index', ['id_equipe' => $id_equipe]);
    }
}