<?php


namespace App\Controller\Admin;

use App\Entity\AffectationProjet;
use App\Entity\CommitHistory;
use App\Entity\DemandeParticipation;
use App\Entity\EvaluationPartTime;
use App\Entity\Notification;
use App\Entity\Projet;
use App\Entity\RepoAccess;
use App\Entity\Repository as ProjetRepositoryEntity;
use App\Enum\Role;
use App\Form\ProjetType;
use App\Repository\CompetenceRepository;
use App\Repository\ProjetRepository;
use App\Repository\UserRepository;
use App\Service\GeminiService;
use App\Service\GitHubRepositoryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/ai/extract-pdf', name: 'admin_projets_ai_extract_pdf', methods: ['POST'])]
    public function aiExtractPdf(Request $request, GeminiService $geminiService): JsonResponse
    {
        $file = $request->files->get('pdf');
        if (!$file) {
            return $this->json(['error' => 'Fichier PDF manquant.'], 400);
        }

        $originalName = (string) $file->getClientOriginalName();
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return $this->json(['error' => 'Veuillez choisir un fichier PDF valide.'], 400);
        }

        $path = $file->getRealPath();
        if (!is_string($path) || $path === '') {
            return $this->json(['error' => 'Impossible de lire le fichier.'], 400);
        }

        $rawData = $geminiService->extractProjectDataFromPdfPath($path);
        if ($rawData === []) {
            $rawData = [
                'titre' => pathinfo($originalName, PATHINFO_FILENAME),
                'description' => 'Extraction automatique limitee pour ce PDF. Merci de completer les champs manuellement.',
            ];
        }

        $payload = [
            'data' => $this->normalizeAiProjectData($rawData),
        ];

        $response = new JsonResponse();
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
        $response->setData($this->sanitizeUtf8Recursive($payload));

        return $response;
    }

    #[Route('/stats', name: 'admin_projets_stats', methods: ['GET'])]
    public function stats(ProjetRepository $repo): Response
    {
        $statusRows = $repo->createQueryBuilder('p')
            ->select('COALESCE(p.statut, :unknown) AS statut, COUNT(p.id_projet) AS total')
            ->setParameter('unknown', 'NON_DEFINI')
            ->groupBy('p.statut')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $priorityRows = $repo->createQueryBuilder('p')
            ->select('COALESCE(p.priorite, :unknown) AS priorite, COUNT(p.id_projet) AS total')
            ->setParameter('unknown', 'NON_DEFINI')
            ->groupBy('p.priorite')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $visibilityRows = $repo->createQueryBuilder('p')
            ->select(
                "SUM(CASE WHEN p.visibleEmploye = 1 THEN 1 ELSE 0 END) AS employe_total,
                 SUM(CASE WHEN p.visibleFreelancer = 1 THEN 1 ELSE 0 END) AS freelancer_total,
                 SUM(CASE WHEN p.visibleEmploye = 1 AND p.visibleFreelancer = 1 THEN 1 ELSE 0 END) AS both_total"
            )
            ->getQuery()
            ->getOneOrNullResult() ?? [
                'employe_total' => 0,
                'freelancer_total' => 0,
                'both_total' => 0,
            ];

        $totalProjects = (int) $repo->createQueryBuilder('p')
            ->select('COUNT(p.id_projet)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/projets/stats.html.twig', [
            'total_projects' => $totalProjects,
            'status_rows' => $statusRows,
            'priority_rows' => $priorityRows,
            'visibility' => [
                'employe' => (int) ($visibilityRows['employe_total'] ?? 0),
                'freelancer' => (int) ($visibilityRows['freelancer_total'] ?? 0),
                'both' => (int) ($visibilityRows['both_total'] ?? 0),
            ],
        ]);
    }

    #[Route('/new', name: 'admin_projets_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $projet = new Projet();
        $form   = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);

            if ($projet->isVisibleEmploye()) {
                $this->createProjectNotifications(
                    $em,
                    $userRepository->findBy(['role' => Role::EMPLOYE, 'actif' => true]),
                    'Nouveau projet disponible',
                    sprintf(
                        'Un nouveau projet "%s" a ete ajoute et il est visible pour les employes.',
                        $projet->getTitre()
                    ),
                    '/employe/projets'
                );
            }

            if ($projet->isVisibleFreelancer()) {
                $this->createProjectNotifications(
                    $em,
                    $userRepository->findBy(['role' => Role::FREELANCER, 'actif' => true]),
                    'Nouveau projet disponible',
                    sprintf(
                        'Un nouveau projet "%s" a ete ajoute et il est visible pour les freelancers.',
                        $projet->getTitre()
                    ),
                    '/freelancer/projets'
                );
            }

            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès.');
            return $this->redirectToRoute('admin_projets_show', ['id' => $projet->getIdProjet()]);
        }

        return $this->render('admin/projets/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createProjectNotifications(
        EntityManagerInterface $em,
        array $users,
        string $title,
        string $message,
        ?string $link = null
    ): void {
        foreach ($users as $user) {
            $notification = new Notification();
            $notification
                ->setUser($user)
                ->setTitre($title)
                ->setMessage($message)
                ->setLien($link)
                ->setIsRead(false);

            $em->persist($notification);
        }
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

        // Delete dependent rows first to respect foreign key constraints.
        $affectations = $em->getRepository(AffectationProjet::class)->findBy(['projet' => $projet]);
        foreach ($affectations as $affectation) {
            $evaluations = $em->getRepository(EvaluationPartTime::class)->findBy(['affectationProjet' => $affectation]);
            foreach ($evaluations as $evaluation) {
                $em->remove($evaluation);
            }
            $em->remove($affectation);
        }

        $demandes = $em->getRepository(DemandeParticipation::class)->findBy(['projet' => $projet]);
        foreach ($demandes as $demande) {
            $em->remove($demande);
        }

        $repository = $projet->getRepository();
        if ($repository !== null) {
            $repoAccesses = $em->getRepository(RepoAccess::class)->findBy(['repository' => $repository]);
            foreach ($repoAccesses as $repoAccess) {
                $em->remove($repoAccess);
            }

            $commitHistory = $em->getRepository(CommitHistory::class)->findOneBy(['repository' => $repository]);
            if ($commitHistory !== null) {
                $em->remove($commitHistory);
            }

            $projet->setRepository(null);
            $em->remove($repository);
        }

        $em->remove($projet);
        try {
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Impossible de supprimer ce projet car il est encore lie a d autres donnees.');
            return $this->redirectToRoute('admin_projets_index');
        }

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

    private function normalizeAiProjectData(array $raw): array
    {
        $allowedStatus = ['PLANIFIE', 'EN_COURS', 'EN_PAUSE', 'TERMINE', 'ANNULE'];
        $allowedPriority = ['HAUTE', 'MOYENNE', 'BASSE'];

        $status = strtoupper((string) ($raw['statut'] ?? ''));
        $priority = strtoupper((string) ($raw['priorite'] ?? ''));

        $dateDebut = $this->normalizeDate($raw['dateDebut'] ?? null);
        $dateFin = $this->normalizeDate($raw['dateFin'] ?? null);
        $dateLivraison = $this->normalizeDate($raw['dateLivraison'] ?? null);

        if ($dateDebut !== null && $dateFin !== null && $dateFin < $dateDebut) {
            $dateFin = $dateDebut;
        }
        if ($dateDebut !== null && $dateLivraison !== null && $dateLivraison < $dateDebut) {
            $dateLivraison = $dateDebut;
        }

        $keywords = [];
        if (isset($raw['competenceKeywords']) && is_array($raw['competenceKeywords'])) {
            foreach ($raw['competenceKeywords'] as $item) {
                $item = trim((string) $item);
                if ($item !== '') {
                    $keywords[] = $item;
                }
            }
            $keywords = array_slice(array_values(array_unique($keywords)), 0, 10);
        }

        return array_filter([
            'titre' => isset($raw['titre']) ? trim((string) $raw['titre']) : null,
            'description' => isset($raw['description']) ? trim((string) $raw['description']) : null,
            'statut' => in_array($status, $allowedStatus, true) ? $status : null,
            'priorite' => in_array($priority, $allowedPriority, true) ? $priority : null,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'dateLivraison' => $dateLivraison,
            'budgetTotal' => $this->normalizeNumber($raw['budgetTotal'] ?? null),
            'budgetInterne' => $this->normalizeNumber($raw['budgetInterne'] ?? null),
            'budgetFreelance' => $this->normalizeNumber($raw['budgetFreelance'] ?? null),
            'visibleEmploye' => filter_var($raw['visibleEmploye'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'visibleFreelancer' => filter_var($raw['visibleFreelancer'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'competenceKeywords' => $keywords,
        ], static fn ($v): bool => $v !== null);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeNumber(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $n = (float) $value;
        if ($n < 0) {
            return null;
        }

        return round($n, 2);
    }

    private function sanitizeUtf8Recursive(mixed $value): mixed
    {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $k => $v) {
                $clean[$k] = $this->sanitizeUtf8Recursive($v);
            }
            return $clean;
        }

        if (!is_string($value)) {
            return $value;
        }

        $text = $value;
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252, ASCII');
        }

        $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if (is_string($fixed) && $fixed !== '') {
            $text = $fixed;
        }

        $text = preg_replace('/[^\P{C}\n\t]/u', ' ', $text) ?? $text;

        return $text;
    }
}


