<?php


namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérification email
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Veuillez vérifier votre email avant de vous connecter.'
            );
        }

        // Vérification compte actif
        if (!$user->isActif()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte est désactivé.'
            );
        }

        if ($user->getBlockedUntil() && $user->getBlockedUntil() > new \DateTime()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte est bloqué pour activité suspecte. Réessayez dans 1 heure.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        
    }
    
}