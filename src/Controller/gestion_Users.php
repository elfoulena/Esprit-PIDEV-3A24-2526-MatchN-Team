<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\CreateEmployeType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class gestion_Users extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserRepository              $repo,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly MailerService               $mailer,
    ) {}

    // ── 1. Routes STATIQUES en premier ───────────────────────────────────

    #[Route('', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $role   = $request->query->get('role');
        $search = $request->query->get('q', '');

        $qb = $this->repo->createQueryBuilder('u');

        if ($role) {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', Role::from($role));
        }

        if ($search) {
            $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
               ->setParameter('q', '%' . $search . '%');
        }

        $qb->orderBy('u.createdAt', 'DESC');
        $utilisateurs = $qb->getQuery()->getResult();

        $stats = [
            'total'       => $this->repo->count([]),
            'employes'    => $this->repo->count(['role' => Role::EMPLOYE]),
            'freelancers' => $this->repo->count(['role' => Role::FREELANCER]),
            'admins'      => $this->repo->count(['role' => Role::ADMIN_RH]),
            'actifs'      => count(array_filter($utilisateurs, fn($u) => $u->isActif())),
        ];

        return $this->render('gestion_Users/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'stats'        => $stats,
            'currentRole'  => $role,
            'search'       => $search,
        ]);
    }

    #[Route('/creer-employe', name: 'admin_user_create_employe', methods: ['GET', 'POST'])]
    public function createEmploye(Request $request): Response
    {
        $form = $this->createForm(CreateEmployeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $plainPassword = substr(
                str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#!'),
                0, 10
            );

            $employe = new User();
            $employe->setNom($data['nom']);
            $employe->setPrenom($data['prenom']);
            $employe->setEmail($data['email']);
            $employe->setPoste($data['poste'] ?? null);
            $employe->setSalaire($data['salaire'] ?? null);
            $employe->setTypeContrat($data['typeContrat'] ?? null);
            $employe->setDepartement($data['departement'] ?? null);
            $employe->setTelephone($data['telephone'] ?? null);
            $employe->setAdresse($data['adresse'] ?? null);
            $employe->setRole(Role::EMPLOYE);
            $employe->setActif(true);
            $employe->setVerified(true);
            $employe->setPassword($this->hasher->hashPassword($employe, $plainPassword));

            $this->em->persist($employe);
            $this->em->flush();

            try {
                $this->mailer->sendEmployeeCredentials(
                    $employe->getEmail(),
                    $employe->getNom() . ' ' . $employe->getPrenom(),
                    $plainPassword
                );
                $this->addFlash('success', "Compte créé et identifiants envoyés à {$employe->getEmail()}.");
            } catch (\Throwable $e) {
                $this->addFlash('success', "Compte créé. Échec de l'envoi email : " . $e->getMessage());
            }

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('gestion_Users/create_employe.html.twig', [
            'form' => $form,
        ]);
    }

    // ── 2. Routes DYNAMIQUES avec {id} en dernier ─────────────────────────

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        return $this->render('gestion_Users/show.html.twig', [
            'utilisateur' => $user,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggle(Request $request, int $id): Response
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('toggle_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setActif(!$user->isActif());
            $this->em->flush();

            $etat = $user->isActif() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Utilisateur {$user->getNom()} {$user->getPrenom()} $etat.");
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/role', name: 'admin_user_role', methods: ['POST'])]
    public function changeRole(Request $request, int $id): Response
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('role_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $newRole = $request->getPayload()->getString('role');

            try {
                $user->setRole(Role::from($newRole));
                $this->em->flush();
                $this->addFlash('success', "Rôle mis à jour pour {$user->getNom()} {$user->getPrenom()}.");
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Rôle invalide.');
            }
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            /** @var User|null $currentUser */
            $currentUser = $this->getUser();
            if ($user->getId() === ($currentUser ? $currentUser->getId() : null)) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                return $this->redirectToRoute('admin_user_index');
            }

            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('admin_user_index');
    }
}