<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\User;

interface UserRepository {
    /**
     * @return User[]
     */
    public function findAll();

    public function findOne(int $id): User;

    public function save(User $user): User;
}
