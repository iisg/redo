<?php
namespace Repeka\Application\Service;

use Elasticsearch\Common\Exceptions\Unauthorized401Exception;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\User;
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

    public function getCurrentUserOrThrow(): User {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new Unauthorized401Exception();
        }
        return $user;
    }
}
