<?php

namespace App\Controller\Freelancer;

use App\Entity\DemandeParticipation;
use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_FREELANCER')]
#[Route('/freelancer/projets')]
class ProjetController extends AbstractController
{
    #[Route('', name: 'freelancer_projets_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $q      = $request->query->get('q', '');
        $statut = $request->query->get('statut', '');

        $qb = $em->getRepository(Projet::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.repository', 'r')
            ->addSelect('r')
            ->where('p.visibleFreelancer = 1')
            ->orderBy('p.id_projet', 'DESC');

        if ($q) {
            $qb->andWhere('p.titre LIKE :q OR p.description LIKE :q')->setParameter('q', "%$q%");
        }
        if ($statut) {
            $qb->andWhere('p.statut = :s')->setParameter('s', $statut);
        }

        $projets = $qb->getQuery()->getResult();

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $demandes = $em->getRepository(DemandeParticipation::class)->findBy([
            'email_freelancer' => $user->getEmail(),
        ]);
        $demandeMap = [];
        foreach ($demandes as $d) {
            if ($d->getProjet()) {
                $demandeMap[$d->getProjet()->getIdProjet()] = $d->getStatut();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('freelancer/projets/_table.html.twig', [
                'projets'    => $projets,
                'demandeMap' => $demandeMap,
            ]);
        }

        return $this->render('freelancer/projets.html.twig', [
            'projets'    => $projets,
            'demandeMap' => $demandeMap,
            'q'          => $q,
            'statut'     => $statut,
        ]);
    }

    #[Route('/{id}/postuler', name: 'freelancer_projets_postuler', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function postuler(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet || !$projet->isVisibleFreelancer()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('postuler_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $existing = $em->getRepository(DemandeParticipation::class)->findOneBy([
            'email_freelancer' => $user->getEmail(),
            'projet'           => $projet,
        ]);

        if ($existing) {
            $this->addFlash('error', 'Vous avez déjà postulé à ce projet.');
            return $this->redirectToRoute('freelancer_projets_index');
        }

        $message = trim($request->request->get('message', ''));
        $github  = trim($request->request->get('github', ''));

        if ($message === '') {
            $this->addFlash('error', 'Le message de motivation est obligatoire.');
            return $this->redirectToRoute('freelancer_projets_index');
        }
        if ($github === '') {
            $this->addFlash('error', 'Le lien GitHub est obligatoire.');
            return $this->redirectToRoute('freelancer_projets_index');
        }
        if (!filter_var($github, FILTER_VALIDATE_URL)) {
            $this->addFlash('error', 'Le lien GitHub doit être une URL valide.');
            return $this->redirectToRoute('freelancer_projets_index');
        }

        $demande = new DemandeParticipation();
        $demande->setProjet($projet);
        $demande->setEmailFreelancer($user->getEmail());
        $demande->setNomFreelancer($user->getNom() . ' ' . $user->getPrenom());
        $demande->setMessage($message);
        $demande->setGithub($github);
        $demande->setStatut('EN_ATTENTE');
        $demande->setCreatedAt(new \DateTime());

        $em->persist($demande);
        $em->flush();

        $this->addFlash('success', 'Votre candidature a été envoyée pour « ' . $projet->getTitre() . ' ».');
        return $this->redirectToRoute('freelancer_projets_index');
    }

    #[Route('/mes-demandes', name: 'freelancer_mes_demandes', methods: ['GET'])]
    public function mesDemandes(Request $request, EntityManagerInterface $em): Response
    {
        $q      = $request->query->get('q', '');
        $statut = $request->query->get('statut', '');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $qb = $em->getRepository(DemandeParticipation::class)
            ->createQueryBuilder('d')
            ->leftJoin('d.projet', 'p')
            ->addSelect('p')
            ->where('d.email_freelancer = :email')
            ->setParameter('email', $user->getEmail())
            ->orderBy('d.created_at', 'DESC');

        if ($q) {
            $qb->andWhere('p.titre LIKE :q')->setParameter('q', "%$q%");
        }
        if ($statut) {
            $qb->andWhere('d.statut = :s')->setParameter('s', $statut);
        }

        $demandes = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('freelancer/mes_demandes/_table.html.twig', ['demandes' => $demandes]);
        }

        return $this->render('freelancer/mes_demandes.html.twig', [
            'demandes' => $demandes,
            'q'        => $q,
            'statut'   => $statut,
        ]);
    }
}
