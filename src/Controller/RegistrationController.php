<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\RegistrationFormType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    UserPasswordHasherInterface $hasher,
    EntityManagerInterface $em,
    MailerService $mailer,
    ValidatorInterface $validator
): Response {

    if ($request->isMethod('POST')) {

        $nom            = trim($request->request->get('nom', ''));
        $prenom         = trim($request->request->get('prenom', ''));
        $email          = trim($request->request->get('email', ''));
        $telephone      = trim($request->request->get('telephone', ''));
        $adresse        = trim($request->request->get('adresse', ''));
        $description    = trim($request->request->get('description', ''));
        $password       = $request->request->get('plainPassword', '');
        $confirmPwd     = $request->request->get('confirmPassword', '');

        if ($nom === '') {
            $this->addFlash('error', 'Le nom est obligatoire.');
            return $this->redirectToRoute('app_register');
        }
        if (strlen($nom) < 2) {
            $this->addFlash('error', 'Le nom doit contenir au moins 2 caractères.');
            return $this->redirectToRoute('app_register');
        }

        if ($prenom === '') {
            $this->addFlash('error', 'Le prénom est obligatoire.');
            return $this->redirectToRoute('app_register');
        }
        if (strlen($prenom) < 2) {
            $this->addFlash('error', 'Le prénom doit contenir au moins 2 caractères.');
            return $this->redirectToRoute('app_register');
        }

        if ($email === '') {
            $this->addFlash('error', "L'email est obligatoire.");
            return $this->redirectToRoute('app_register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Format email invalide.');
            return $this->redirectToRoute('app_register');
        }
        if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('app_register');
        }

        if ($telephone !== '' && !preg_match('/^\+?[0-9\s\-]{7,20}$/', $telephone)) {
            $this->addFlash('error', 'Numéro de téléphone invalide.');
            return $this->redirectToRoute('app_register');
        }

        if ($description === '') {
            $this->addFlash('error', 'La description est obligatoire.');
            return $this->redirectToRoute('app_register');
        }

        if ($password === '') {
            $this->addFlash('error', 'Le mot de passe est obligatoire.');
            return $this->redirectToRoute('app_register');
        }
        if (strlen($password) < 8) {
            $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_register');
        }

        if ($password !== $confirmPwd) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setTelephone($telephone ?: null);
            $user->setAdresse($adresse ?: null);
            $user->setDescription($description);
            $user->setPassword($hasher->hashPassword($user, $password));
            $user->setRole(Role::FREELANCER);
            $user->setVerified(false);

            $code  = (string) random_int(100000, 999999);
            $expiry = new \DateTime('+24 hours');
            $user->setVerificationToken($code);
            $user->setVerificationExpiry($expiry);

            $em->persist($user);
            $em->flush();

            $mailer->sendConfirmationEmail($user->getEmail(), $user->getNom(), $code);

            return $this->redirectToRoute('app_verify_pending', ['email' => $user->getEmail()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.');
            return $this->redirectToRoute('app_register');
        }
    }

    return $this->render('registration/register.html.twig');
}

    // Page "en attente de vérification"
    #[Route('/verify/pending', name: 'app_verify_pending')]
    public function pending(Request $request): Response
    {
        return $this->render('registration/pending.html.twig', [
            'email' => $request->query->get('email')
        ]);
    }

    // Vérifie le code saisi par l'User
    #[Route('/verify/email', name: 'app_verify_email', methods: ['POST'])]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $email = $request->request->get('email');
        $code  = $request->request->get('code');

        $user = $em->getRepository(User::class)->findOneBy([
            'email'             => $email,
            'verificationToken' => $code,
            'verified'          => false,
        ]);

        if (!$user || $user->getVerificationExpiry() < new \DateTime()) {
            $this->addFlash('error', 'Code invalide ou expiré.');
            return $this->redirectToRoute('app_verify_pending', ['email' => $email]);
        }

        $user->setVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationExpiry(null);

        $em->flush();

        return $this->redirectToRoute('app_login');
    }
}