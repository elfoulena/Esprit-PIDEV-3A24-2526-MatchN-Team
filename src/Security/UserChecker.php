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
    }

    public function checkPostAuth(UserInterface $user): void
    {
        
    }
    
}