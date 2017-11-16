<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class TokenAuthenticator {
    abstract public function canAuthenticate(TokenInterface $token): bool;

    abstract public function authenticate(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface;

    protected function createAuthenticatedToken(UserInterface $user, $providerKey): UsernamePasswordToken {
        return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
    }
}
