<?php

namespace App\Controller\Admin;

use App\Entity\AffectationProjet;
use App\Entity\DemandeParticipation;
use App\Entity\Projet;
<<<<<<< Updated upstream
use App\Form\ProjetType;
use App\Repository\CompetenceRepository;
use App\Repository\ProjetRepository;
=======
use App\Entity\Repository as ProjetRepositoryEntity;
use App\Enum\Role;
use App\Form\ProjetType;
use App\Repository\CompetenceRepository;
use App\Repository\ProjetRepository;
use App\Repository\UserRepository;
use App\Service\GitHubRepositoryService;
>>>>>>> Stashed changes
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/projets')]
class ProjetController extends AbstractController
{
    #[Route('', name: 'admin_projets_index', methods: ['GET'])]
    public function index(Request $request, ProjetRepository $repo, CompetenceRepository $compRepo): Response
    {
        $q      = $request->query->get('q', '');
        $statut = $request->query->get('statut', '');
        $visib  = $request->query->get('visib', '');

        $qb = $repo->createQueryBuilder('p')
            ->leftJoin('p.repository', 'r')
            ->addSelect('r')
            ->orderBy('p.id_projet', 'DESC');

        if ($q) {
            $qb->andWhere('p.titre LIKE :q OR p.description LIKE :q')->setParameter('q', "%$q%");
        }
        if ($statut) {
            $qb->andWhere('p.statut = :s')->setParameter('s', $statut);
        }
        if ($visib === 'employe') {
            $qb->andWhere('p.visibleEmploye = 1');
        } elseif ($visib === 'freelancer') {
            $qb->andWhere('p.visibleFreelancer = 1');
        }

        $projets = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/projets/_table.html.twig', ['projets' => $projets]);
        }

        return $this->render('admin/projets/index.html.twig', [
            'projets'     => $projets,
            'q'           => $q,
            'statut'      => $statut,
            'visib'       => $visib,
            'competences' => $compRepo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_projets_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
        $form   = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès.');
            return $this->redirectToRoute('admin_projets_show', ['id' => $projet->getIdProjet()]);
        }

        return $this->render('admin/projets/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_projets_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Projet mis à jour.');
            return $this->redirectToRoute('admin_projets_show', ['id' => $id]);
        }

        return $this->render('admin/projets/edit.html.twig', [
            'projet' => $projet,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/demandes', name: 'admin_projets_demandes', methods: ['GET'])]
    public function demandes(Request $request, EntityManagerInterface $em): Response
    {
        $q  = $request->query->get('q', '');
        $st = $request->query->get('statut', '');

        $qb = $em->getRepository(DemandeParticipation::class)
            ->createQueryBuilder('d')
            ->leftJoin('d.projet', 'p')
            ->addSelect('p')
            ->orderBy('d.created_at', 'DESC');

        if ($q) {
            $qb->andWhere('d.nom_freelancer LIKE :q OR d.email_freelancer LIKE :q OR p.titre LIKE :q')
               ->setParameter('q', "%$q%");
        }
        if ($st) {
            $qb->andWhere('d.statut = :st')->setParameter('st', $st);
        }

        $demandes = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/projets/_demandes_table.html.twig', ['demandes' => $demandes]);
        }

        return $this->render('admin/projets/demandes.html.twig', [
            'demandes' => $demandes,
            'q'        => $q,
            'statut'   => $st,
        ]);
    }

    #[Route('/demandes/{id}/accept', name: 'admin_projets_demande_accept', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function acceptDemande(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $demande = $em->getRepository(DemandeParticipation::class)->find($id);
        if (!$demande) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('accept_demande_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $demande->setStatut('ACCEPTE');
        $em->flush();

        $this->addFlash('success', 'Demande acceptée.');
        return $this->redirectToRoute('admin_projets_demandes');
    }

    #[Route('/demandes/{id}/refuse', name: 'admin_projets_demande_refuse', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function refuseDemande(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $demande = $em->getRepository(DemandeParticipation::class)->find($id);
        if (!$demande) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('refuse_demande_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $demande->setStatut('REFUSE');
        $em->flush();

        $this->addFlash('success', 'Demande refusée.');
        return $this->redirectToRoute('admin_projets_demandes');
    }

    #[Route('/{id}', name: 'admin_projets_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet) {
            throw $this->createNotFoundException();
        }

        $affectations = $em->getRepository(AffectationProjet::class)->findBy(['projet' => $projet]);
        $demandes     = $em->getRepository(DemandeParticipation::class)->findBy(['projet' => $projet]);

        return $this->render('admin/projets/show.html.twig', [
            'projet'       => $projet,
            'affectations' => $affectations,
            'demandes'     => $demandes,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_projets_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_projet_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($projet);
        $em->flush();

        $this->addFlash('success', 'Projet supprimé.');
        return $this->redirectToRoute('admin_projets_index');
    }

    #[Route('/{id}/repo/configure', name: 'admin_projets_repo_configure', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function configureRepository(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        GitHubRepositoryService $gitHubRepositoryService
    ): RedirectResponse {
        $projet = $em->getRepository(Projet::class)->find($id);
        if (!$projet) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('configure_repo_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($projet->getRepository()) {
            $this->addFlash('info', 'Ce projet a déjà un repo configuré.');
            return $this->redirectToRoute('admin_projets_index');
        }

        try {
            $githubRepoData = $gitHubRepositoryService->createRepositoryForProject($projet);

            $repository = new ProjetRepositoryEntity();
            $repository
                ->setProjet($projet)
                ->setNomRepo($githubRepoData['name'])
                ->setRepoName($githubRepoData['name'])
                ->setUrlRepo($githubRepoData['html_url'])
                ->setOwner($githubRepoData['owner'])
                ->setIsPrivate($githubRepoData['private'])
                ->setCreatedAt(new \DateTime());

            $em->persist($repository);
            $em->flush();

            $this->addFlash('success', sprintf('Repo GitHub créé: %s', $githubRepoData['html_url']));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de configurer le repo GitHub: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_projets_index');
    }
}
