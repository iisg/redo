<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\User;

interface UserFactory {
    public function createUser(string $username): User;
}
