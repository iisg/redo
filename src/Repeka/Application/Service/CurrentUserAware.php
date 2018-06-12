<?php
namespace Repeka\Application\Service;

use Repeka\Application\Entity\UserEntity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

trait CurrentUserAware {
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @required */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /** @return UserEntity|null */
    public function getCurrentUser() {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }
        return $user;
    }
}
