<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class TokenAuthenticator {
    abstract public function canAuthenticate(TokenInterface $token): bool;

    abstract public function authenticate(TokenInterface $token, UserProviderInterface $userProvider): string;
}
