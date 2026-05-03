<?php

namespace App\Controller\Employe;

use App\Entity\AffectationProjet;
use App\Entity\Projet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE')]
#[Route('/employe/projets')]
class ProjetController extends AbstractController
{
    #[Route('', name: 'employe_projets_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $q      = $request->query->get('q', '');
        $statut = $request->query->get('statut', '');

        $qb = $em->getRepository(Projet::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.repository', 'r')
            ->addSelect('r')
            ->where('p.visibleEmploye = 1')
            ->orderBy('p.id_projet', 'DESC');

        if ($q) {
            $qb->andWhere('p.titre LIKE :q OR p.description LIKE :q')->setParameter('q', "%$q%");
        }
        if ($statut) {
            $qb->andWhere('p.statut = :s')->setParameter('s', $statut);
        }

        $projets = $qb->getQuery()->getResult();

        $user = $this->getUser();
        $affectations = $em->getRepository(AffectationProjet::class)->findBy(['User' => $user]);
        $affectedIds = array_map(fn($a) => $a->getProjet()?->getIdProjet(), $affectations);

        if ($request->isXmlHttpRequest()) {
            return $this->render('employe/projets/_table.html.twig', [
                'projets'     => $projets,
                'affectedIds' => $affectedIds,
            ]);
        }

        return $this->render('employe/projets.html.twig', [
            'projets'     => $projets,
            'affectedIds' => $affectedIds,
            'q'           => $q,
            'statut'      => $statut,
        ]);
    }

    #[Route('/{id}/take', name: 'employe_projets_take', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function take(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet || !$projet->isVisibleEmploye()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('take_projet_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $existing = $em->getRepository(AffectationProjet::class)->findOneBy([
            'User'   => $user,
            'projet' => $projet,
        ]);

        if ($existing) {
            $this->addFlash('error', 'Vous êtes déjà assigné à ce projet.');
            return $this->redirectToRoute('employe_projets_index');
        }

        $affectation = new AffectationProjet();
        $affectation->setUser($user);
        $affectation->setProjet($projet);
        $affectation->setDate_debut(new \DateTime());
        $affectation->setStatut('EN_COURS');

        $em->persist($affectation);
        $em->flush();

        $this->addFlash('success', 'Vous êtes maintenant assigné au projet « ' . $projet->getTitre() . ' ».');
        return $this->redirectToRoute('employe_projets_index');
    }

    #[Route('/mes-affectations', name: 'employe_mes_affectations', methods: ['GET'])]
    public function mesAffectations(EntityManagerInterface $em): Response
    {
        $affectations = $em->getRepository(AffectationProjet::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.projet', 'p')
            ->addSelect('p')
            ->leftJoin('p.repository', 'r')
            ->addSelect('r')
            ->where('a.User = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('a.date_debut', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('employe/mes_affectations.html.twig', [
            'affectations' => $affectations,
        ]);
    }
}
