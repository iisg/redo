<?php
namespace Repeka\Application\Factory;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;

class UserEntityFactory implements UserFactory {
    /** @return UserEntity */
    public function createUser(string $username): User {
        $user = new UserEntity();
        $user->setUsername($username);
        return $user;
    }
}
