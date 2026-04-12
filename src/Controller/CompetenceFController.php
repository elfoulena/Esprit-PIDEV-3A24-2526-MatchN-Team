<?php

namespace App\Controller;

use App\Entity\CompetenceF;
use App\Form\CompetenceFType;
use App\Repository\CompetenceFRepository;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/competences')]
#[IsGranted('ROLE_ADMIN')]
class CompetenceFController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly CompetenceFRepository    $repo,
        private readonly GeminiService            $gemini,
    ) {}

    #[Route('', name: 'competence_index', methods: ['GET', 'POST'])]
public function index(Request $request): Response
{
    // Formulaire d'ajout rapide depuis la page index
    $competence = new CompetenceF();
    $form = $this->createForm(CompetenceFType::class, $competence);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if (empty(trim((string) $competence->getDescription()))) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom())
            );
        }
        $this->em->persist($competence);
        $this->em->flush();

        $this->addFlash('success', 'Compétence ajoutée avec succès !');
        return $this->redirectToRoute('competence_index');
    }

    return $this->render('competence_f/index.html.twig', [
        'competences' => $this->repo->findAll(),
        'form'        => $form,   
    ]);
}

    #[Route('/new', name: 'competence_new')]
    public function new(Request $request): Response
    {
        $competence = new CompetenceF();
        $form = $this->createForm(CompetenceFType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty(trim((string) $competence->getDescription()))) {
                $competence->setDescription(
                    $this->gemini->genererDescriptionCompetence($competence->getNom())
                );
            }

            $this->em->persist($competence);
            $this->em->flush();

            $this->addFlash('success', 'Compétence ajoutée avec succès !');
            return $this->redirectToRoute('competence_index');
        }

        return $this->render('competence_f/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'competence_show')]
    public function show(CompetenceF $competence): Response
    {
        return $this->render('competence_f/show.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/{id}/edit', name: 'competence_edit')]
public function edit(Request $request, CompetenceF $competence): Response
{
    $ancienNom = $competence->getNom();

    $form = $this->createForm(CompetenceFType::class, $competence);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $nomAChange = $ancienNom !== $competence->getNom();

        if ($nomAChange) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom())
            );
        } elseif (empty(trim((string) $competence->getDescription()))) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom())
            );
        }

        $this->em->flush();
        $this->addFlash('success', 'Compétence modifiée avec succès !');
        return $this->redirectToRoute('competence_index');
    }

    return $this->render('competence_f/edit.html.twig', [
        'competence' => $competence,
        'form'       => $form,
    ]);
}

    #[Route('/{id}/delete', name: 'competence_delete')]
    public function delete(Request $request, CompetenceF $competence): Response
    {
        if ($this->isCsrfTokenValid('delete' . $competence->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($competence);
            $this->em->flush();
            $this->addFlash('success', 'Compétence supprimée.');
        }

        return $this->redirectToRoute('competence_index');
    }

    public function createIfNotExists(string $skillName): CompetenceF
    {
        $existing = $this->repo->findByName($skillName);
        if ($existing) {
            return $existing;
        }

        $competence = (new CompetenceF())
            ->setNom($skillName)
            ->setDescription($this->gemini->genererDescriptionCompetence($skillName));

        $this->em->persist($competence);
        $this->em->flush();

        return $competence;
    }
}