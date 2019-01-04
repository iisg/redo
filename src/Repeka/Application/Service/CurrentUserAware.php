<?php
namespace Repeka\Application\Service;

use Repeka\Application\Entity\UserEntity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait CurrentUserAware {
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @required */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function getCurrentUserToken(): ?TokenInterface {
        return $this->tokenStorage->getToken();
    }

    /** @return UserEntity|null */
    public function getCurrentUser() {
        if (null === $token = $this->getCurrentUserToken()) {
            return null;
        }
        $user = $token->getUser();
        return is_object($user) ? $user : null;
    }
}
