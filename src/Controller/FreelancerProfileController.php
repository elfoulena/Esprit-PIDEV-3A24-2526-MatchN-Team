<?php

namespace App\Controller;

use App\Form\FreelancerProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\SkillExtractor;


#[IsGranted('ROLE_FREELANCER')]
#[Route('/freelancer/profile')]
class FreelancerProfileController extends AbstractController
{
    #[Route('', name: 'freelancer_profile', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('freelancer/profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'freelancer_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SkillExtractor $skillExtractor   

    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(FreelancerProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $description = $user->getDescription();

            $skillsNames = $skillExtractor->extract($description);

            foreach ($user->getCompetences() as $old){
                $user->removeCompetence($old);
            }

            $repo = $em->getRepository(\App\Entity\CompetenceF::class);
            foreach($skillsNames as $name){
                $competence = $repo->findOneBy(['nom'=>ucfirst(strtolower($name))]);
                if ($competence) {
                    $user->addCompetence($competence);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('freelancer_profile');
        }

        return $this->render('freelancer/profile/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}