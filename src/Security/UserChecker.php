<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// Symfony l'appelle à chaque connexion (déclaré dans security.yaml)
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    // Après la vérification du mot de passe : on refuse les comptes bloqués
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if ($user instanceof User && $user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException('Ton compte a été bloqué par un administrateur.');
        }
    }
}
