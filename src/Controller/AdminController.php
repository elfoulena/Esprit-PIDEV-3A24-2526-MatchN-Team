<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\CreateEmployeeFormType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $employes    = $em->getRepository(User::class)->findBy(['role' => Role::EMPLOYE]);
        $freelancers = $em->getRepository(User::class)->findBy(['role' => Role::FREELANCER]);

        return $this->render('admin/dashboard.html.twig', [
            'employes'    => $employes,
            'freelancers' => $freelancers,
        ]);
    }

    #[Route('/employes/nouveau', name: 'admin_create_employee')]
    public function createEmployee(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        MailerService $mailer
    ): Response {

        $employe = new User();
        $form    = $this->createForm(CreateEmployeeFormType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $this->generateSecurePassword();

            $employe->setPassword($hasher->hashPassword($employe, $plainPassword));
            $employe->setRole(Role::EMPLOYE);
            $employe->setVerified(true); 
            $employe->setActif(true);

            $em->persist($employe);
            $em->flush();

            $mailer->sendEmployeeCredentials(
                $employe->getEmail(),
                $employe->getNom(),
                $plainPassword
            );

            $this->addFlash('success', "Compte créé pour {$employe->getNom()}.");
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/create_employee.html.twig', ['form' => $form]);
    }

    #[Route('/employes/{id}/toggle', name: 'admin_toggle_employe')]
    public function toggleStatus(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $user->setActif(!$user->isActif());
        $em->flush();

        return $this->redirectToRoute('admin_dashboard');
    }

    private function generateSecurePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $pass  = '';
        for ($i = 0; $i < $length; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }
}