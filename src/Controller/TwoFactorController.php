<?php

namespace App\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class TwoFactorController extends AbstractController
{
    #[Route('/2fa', name: '2fa_login')]
    public function form(): Response
    {
        return $this->render('security/2fa.html.twig');
    }

    #[Route('/2fa_check', name: '2fa_login_check', methods: ['POST'])]
    public function check(): void
    {
        // géré automatiquement par Symfony
    }

    #[Route('/2fa-setup', name: '2fa_setup')]
    public function setup(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user->getTotpSecret()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setTotpSecret($secret);
            $em->flush();
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            if ($googleAuthenticator->checkCode($user, $code)) {
                $user->setTotpEnabled(true);
                $em->flush();

                $this->addFlash('success', '2FA activé !');
                return $this->redirectToRoute('dashboard');
            }

            $this->addFlash('error', 'Code invalide');
        }

        $qrCodeUrl = $googleAuthenticator->getQRContent($user);

        return $this->render('security/2fa_setup.html.twig', [
            'qr_code_url' => $qrCodeUrl,
            'secret' => $user->getTotpSecret(),
        ]);
    }

    #[Route('/2fa-disable', name: '2fa_disable')]
    public function disable(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $user->setTotpEnabled(false);
        $user->setTotpSecret(null);
        $em->flush();

        return $this->redirectToRoute('dashboard');
    }
}