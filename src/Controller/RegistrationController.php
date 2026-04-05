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

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        MailerService $mailer
    ): Response {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $hasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $user->setRole(Role::FREELANCER);
            $user->setVerified(false);

            $code   = (string) random_int(100000, 999999);
            $expiry = new \DateTime('+24 hours');

            $user->setVerificationToken($code);
            $user->setVerificationExpiry($expiry);  

            $em->persist($user);
            $em->flush();

            $mailer->sendConfirmationEmail(
                $user->getEmail(),
                $user->getNom(),
                $code
            );

            $this->addFlash('success', 'Inscription réussie ! Vérifiez votre email.');
            return $this->redirectToRoute('app_verify_pending', [
                'email' => $user->getEmail()
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    // Page "en attente de vérification"
    #[Route('/verify/pending', name: 'app_verify_pending')]
    public function pending(Request $request): Response
    {
        return $this->render('registration/pending.html.twig', [
            'email' => $request->query->get('email')
        ]);
    }

    // Vérifie le code saisi par l'utilisateur
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

        $this->addFlash('success', 'Email vérifié ! Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}