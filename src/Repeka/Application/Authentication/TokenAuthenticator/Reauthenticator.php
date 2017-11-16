<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Reauthenticator extends TokenAuthenticator {
    public function canAuthenticate(TokenInterface $token): bool {
        return $token->getCredentials() === null;
    }

    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface {
        $currentUserEntity = $userProvider->loadUserByUsername($token->getUsername());
        if ($this->wasAuthenticatedPreviously($token, $currentUserEntity)) {
            return $this->createAuthenticatedToken($currentUserEntity, $providerKey);
        } else {
            throw new CustomUserMessageAuthenticationException('Authentication failed - invalid token');
        }
    }

    private function wasAuthenticatedPreviously(TokenInterface $token, UserInterface $currentUserEntity): bool {
        // Inspired by DaoAuthenticationProvider::checkAuthentication()
        $storedUserEntity = $token->getUser();
        if (!$storedUserEntity instanceof UserInterface) {
            return false;
        }
        return $storedUserEntity->getPassword() === $currentUserEntity->getPassword();
    }
}
