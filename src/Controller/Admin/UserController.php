<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Role;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
class UserController extends AbstractController
{
    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/users/index.html.twig', ['users' => $users]);
    }

    #[Route('/new-employe', name: 'admin_users_new_employe', methods: ['GET', 'POST'])]
    public function newEmploye(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailerService $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $nom       = trim($request->request->get('nom', ''));
            $prenom    = trim($request->request->get('prenom', ''));
            $email     = trim($request->request->get('email', ''));
            $telephone = trim($request->request->get('telephone', ''));
            $password  = $request->request->get('plainPassword', '');

            if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
                return $this->redirectToRoute('admin_users_new_employe');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Format email invalide.');
                return $this->redirectToRoute('admin_users_new_employe');
            }

            if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('admin_users_new_employe');
            }

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
                return $this->redirectToRoute('admin_users_new_employe');
            }

            $code   = (string) random_int(100000, 999999);
            $expiry = new \DateTime('+24 hours');

            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setTelephone($telephone ?: null);
            $user->setRole(Role::EMPLOYE);
            $user->setVerified(false);
            $user->setActif(true);
            $user->setVerificationToken($code);
            $user->setVerificationExpiry($expiry);
            $user->setPassword($hasher->hashPassword($user, $password));

            $em->persist($user);
            $em->flush();

            try {
                $mailer->sendEmployeWelcomeEmail($email, $nom, $password, $code);
            } catch (\Exception) {
            }

            $this->addFlash('success', "Compte employé créé pour {$nom} {$prenom}. Un email avec le code de vérification a été envoyé.");
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/new_employe.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            $nom       = trim($request->request->get('nom', ''));
            $prenom    = trim($request->request->get('prenom', ''));
            $telephone = trim($request->request->get('telephone', ''));
            $adresse   = trim($request->request->get('adresse', ''));
            $actif     = (bool) $request->request->get('actif', false);

            if ($nom === '' || $prenom === '') {
                $this->addFlash('error', 'Nom et prénom sont obligatoires.');
                return $this->redirectToRoute('admin_users_edit', ['id' => $id]);
            }

            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone($telephone ?: null);
            $user->setAdresse($adresse ?: null);
            $user->setActif($actif);

            $em->flush();

            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', ['user' => $user]);
    }

    #[Route('/{id}', name: 'admin_users_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/users/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/toggle', name: 'admin_users_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('toggle_user_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user->setActif(!$user->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_user_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/resend-code', name: 'admin_users_resend_code', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function resendCode(int $id, Request $request, EntityManagerInterface $em, MailerService $mailer): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user || $user->isVerified()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('resend_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $code   = (string) random_int(100000, 999999);
        $expiry = new \DateTime('+24 hours');
        $user->setVerificationToken($code);
        $user->setVerificationExpiry($expiry);
        $em->flush();

        try {
            $mailer->sendConfirmationEmail($user->getEmail(), $user->getNom(), $code);
        } catch (\Exception) {
        }

        $this->addFlash('success', 'Nouveau code de vérification envoyé.');
        return $this->redirectToRoute('admin_users_index');
    }
}
