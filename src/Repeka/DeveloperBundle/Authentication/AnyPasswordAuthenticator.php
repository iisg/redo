<?php
namespace Repeka\DeveloperBundle\Authentication;

use Repeka\Application\Authentication\TokenAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AnyPasswordAuthenticator extends TokenAuthenticator {
    private const ENABLED = false;

    public function canAuthenticate(TokenInterface $token): bool {
        return self::ENABLED;
    }

    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider): string {
        return $userProvider->loadUserByUsername($token->getUsername())->getUsername();
    }
}
