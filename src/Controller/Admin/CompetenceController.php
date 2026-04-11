<?php

namespace App\Controller\Admin;

use App\Entity\Competence;
use App\Form\CompetenceType;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/competences')]
class CompetenceController extends AbstractController
{
    #[Route('', name: 'admin_competences_index', methods: ['GET'])]
    public function index(Request $request, CompetenceRepository $repo): Response
    {
        $q = $request->query->get('q', '');

        $qb = $repo->createQueryBuilder('c')->orderBy('c.nom_competence', 'ASC');
        if ($q) {
            $qb->andWhere('c.nom_competence LIKE :q OR c.type LIKE :q')->setParameter('q', "%$q%");
        }
        $competences = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/competences/_table.html.twig', ['competences' => $competences]);
        }

        return $this->render('admin/competences/index.html.twig', [
            'competences' => $competences,
            'q'           => $q,
        ]);
    }

    #[Route('/new', name: 'admin_competences_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $competence = new Competence();
        $form       = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($competence);
            $em->flush();

            $this->addFlash('success', 'Compétence créée.');
            return $this->redirectToRoute('admin_competences_index');
        }

        return $this->render('admin/competences/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_competences_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $competence = $em->getRepository(Competence::class)->find($id);
        if (!$competence) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Compétence mise à jour.');
            return $this->redirectToRoute('admin_competences_index');
        }

        return $this->render('admin/competences/edit.html.twig', [
            'competence' => $competence,
            'form'       => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_competences_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $competence = $em->getRepository(Competence::class)->find($id);
        if (!$competence) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_competence_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($competence);
        $em->flush();

        $this->addFlash('success', 'Compétence supprimée.');
        return $this->redirectToRoute('admin_competences_index');
    }
}
